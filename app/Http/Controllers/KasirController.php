<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function index(Request $request): View
    {
        return view('kasir.index', [
            'transactionNumber' => $this->nextNumber(),
            'customers' => Customer::orderBy('id')->get(),
            'kasir' => $request->user()->name,
            'resumeId' => $request->integer('resume') ?: null,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $products = Product::cari(trim((string) $request->query('q')))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'code', 'barcode', 'name', 'price']);

        return response()->json($products);
    }

    private function nextNumber(): string
    {
        $date = now()->format('Ymd');
        $seq = Transaction::whereNotNull('transaction_number')->whereDate('created_at', today())->count() + 1;

        do {
            $number = 'TRX-'.$date.'-'.str_pad((string) $seq++, 3, '0', STR_PAD_LEFT);
        } while (Transaction::where('transaction_number', $number)->exists());

        return $number;
    }
}
