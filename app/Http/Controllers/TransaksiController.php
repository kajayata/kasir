<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('kasir');
    }

    public function store(StoreTransaksiRequest $request): JsonResponse
    {
        $transaction = DB::transaction(function () use ($request) {
            $lines = $this->buildLines($request);
            $this->guardStock($lines);

            $totals = $this->buildTotals($request, $lines);

            if ($totals['paid_amount'] < $totals['grand_total']) {
                throw ValidationException::withMessages(['paid_amount' => 'Uang pembayaran masih kurang.']);
            }

            $transaction = $this->persist($request, $lines, $totals, Transaction::STATUS_PAID);
            $transaction->transaction_number = $this->generateNumber();
            $transaction->save();

            foreach ($lines as $line) {
                $line['product']->decrement('stock', $line['qty']);
            }

            return $transaction;
        });

        return response()->json(['struk_url' => route('kasir.struk', $transaction)]);
    }

    public function hold(StoreTransaksiRequest $request): JsonResponse
    {
        $transaction = DB::transaction(function () use ($request) {
            $lines = $this->buildLines($request);
            $totals = $this->buildTotals($request, $lines);

            // Transaksi ditahan: tanpa nomor, tanpa potong stok, tanpa pembayaran.
            return $this->persist($request, $lines, $totals, Transaction::STATUS_HELD);
        });

        return response()->json(['id' => $transaction->id]);
    }

    public function resume(StoreTransaksiRequest $request, Transaction $transaksi): JsonResponse|RedirectResponse
    {
        abort_unless($transaksi->status === Transaction::STATUS_HELD, 409);

        $transaction = DB::transaction(function () use ($request, $transaksi) {
            $lines = $this->buildLines($request);
            $this->guardStock($lines);

            $totals = $this->buildTotals($request, $lines);

            if ($totals['paid_amount'] < $totals['grand_total']) {
                throw ValidationException::withMessages(['paid_amount' => 'Uang pembayaran masih kurang.']);
            }

            $transaksi->details()->delete();
            $this->persist($request, $lines, $totals, Transaction::STATUS_PAID, $transaksi);

            $transaksi->transaction_number = $this->generateNumber();
            $transaksi->save();

            foreach ($lines as $line) {
                $line['product']->decrement('stock', $line['qty']);
            }

            return $transaksi;
        });

        return response()->json(['struk_url' => route('kasir.struk', $transaction)]);
    }

    public function cancel(Transaction $transaksi): JsonResponse
    {
        abort_if($transaksi->status === Transaction::STATUS_PAID, 409, 'Transaksi yang sudah dibayar tidak bisa dibatalkan.');

        $transaksi->update(['status' => Transaction::STATUS_CANCELLED]);

        return response()->json(['ok' => true]);
    }

    public function struk(Transaction $transaksi): View
    {
        return view('kasir.struk', [
            'transaksi' => $transaksi->load('details', 'user', 'customer'),
        ]);
    }

    public function invoice(Transaction $transaksi)
    {
        $transaksi->loadMissing('details', 'user', 'customer');

        $pdf = Pdf::loadView('invoices.invoice', ['invoice' => $transaksi]);

        $filename = $transaksi->transaction_number
            ? 'invoice_'.$transaksi->transaction_number.'.pdf'
            : 'invoice_'.$transaksi->id.'.pdf';

        return $pdf->download($filename);
    }

    public function ditahan(): View
    {
        return view('kasir.ditahan', [
            'transaksi' => Transaction::with('details')
                ->where('status', Transaction::STATUS_HELD)
                ->latest('id')
                ->get(),
        ]);
    }

    public function riwayat(Request $request): View
    {
        return view('kasir.riwayat', [
            'transaksi' => Transaction::with('details', 'user', 'customer')
                ->where('status', '!=', Transaction::STATUS_HELD)
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function detail(Transaction $transaksi): JsonResponse
    {
        abort_unless($transaksi->status === Transaction::STATUS_HELD, 404);

        return response()->json([
            'id' => $transaksi->id,
            'items' => $transaksi->details->map(fn (TransactionDetail $d) => [
                'id' => $d->product_id,
                'code' => $d->product_code,
                'name' => $d->product_name,
                'price' => $d->price,
                'qty' => $d->qty,
            ])->values(),
        ]);
    }

    /**
     * Harga SELALU diambil dari database, tidak pernah dari kiriman client.
     *
     * @return array<int, array{product: Product, qty: int, price: int, code: string, name: string, subtotal: int, discount: int}>
     */
    private function buildLines(StoreTransaksiRequest $request): array
    {
        $items = $request->array('items');
        $products = Product::whereIn('id', array_column($items, 'id'))->get()->keyBy('id');
        $discountPercent = (int) $request->input('discount_percent', 0);
        $manualDiscount = (int) $request->input('discount_amount', 0);

        $lines = [];
        foreach ($items as $item) {
            $product = $products[$item['id']];
            $qty = (int) $item['qty'];
            $lineSubtotal = $product->price * $qty;

            $lines[] = [
                'product' => $product,
                'qty' => $qty,
                'price' => $product->price,
                'code' => $product->code,
                'name' => $product->name,
                'subtotal' => $lineSubtotal,
                'discount' => (int) round($lineSubtotal * $discountPercent / 100),
            ];
        }

        // Diskon rupiah dipotong dari detail terakhir bila sisa, agar total detail == diskon.
        if ($manualDiscount > 0 && $lines !== []) {
            $remaining = $manualDiscount;
            for ($i = count($lines) - 1; $i >= 0 && $remaining > 0; $i--) {
                $take = min($remaining, $lines[$i]['subtotal'] - $lines[$i]['discount']);
                $lines[$i]['discount'] += $take;
                $remaining -= $take;
            }
        }

        return $lines;
    }

    /**
     * @return array{subtotal: int, discount: int, tax: int, other_fee: int, grand_total: int, paid_amount: int, change_amount: int}
     */
    private function buildTotals(StoreTransaksiRequest $request, array $lines): array
    {
        $subtotal = array_sum(array_column($lines, 'subtotal'));
        $discount = array_sum(array_column($lines, 'discount'));
        $tax = (int) $request->input('tax', 0);
        $otherFee = (int) $request->input('other_fee', 0);
        $paid = (int) $request->input('paid_amount', 0);
        $grandTotal = max(0, $subtotal - $discount + $tax + $otherFee);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'other_fee' => $otherFee,
            'grand_total' => $grandTotal,
            'paid_amount' => $paid,
            'change_amount' => max(0, $paid - $grandTotal),
        ];
    }

    /**
     * @return array<int, array{product: Product, qty: int, price: int, code: string, name: string, subtotal: int, discount: int}>
     */
    private function guardStock(array $lines): void
    {
        foreach ($lines as $line) {
            if ($line['product']->stock < $line['qty']) {
                throw ValidationException::withMessages([
                    'items' => "Stok {$line['name']} tidak cukup (sisa {$line['product']->stock}).",
                ]);
            }
        }
    }

    private function persist(StoreTransaksiRequest $request, array $lines, array $totals, string $status, ?Transaction $transaction = null): Transaction
    {
        $transaction ??= new Transaction;
        $transaction->fill([
            'user_id' => $request->user()->id,
            'customer_id' => $request->input('customer_id'),
            'payment_method' => $request->input('payment_method'),
            'status' => $status,
        ] + $totals);
        $transaction->save();

        foreach ($lines as $line) {
            $transaction->details()->create([
                'product_id' => $line['product']->id,
                'product_code' => $line['code'],
                'product_name' => $line['name'],
                'price' => $line['price'],
                'qty' => $line['qty'],
                'discount' => $line['discount'],
                'subtotal' => $line['subtotal'],
            ]);
        }

        return $transaction;
    }

    private function generateNumber(): string
    {
        $date = now()->format('Ymd');
        $seq = Transaction::whereNotNull('transaction_number')->whereDate('created_at', today())->count() + 1;

        do {
            $number = 'TRX-'.$date.'-'.str_pad((string) $seq++, 3, '0', STR_PAD_LEFT);
        } while (Transaction::where('transaction_number', $number)->exists());

        return $number;
    }
}
