<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->transaction_number ?? $invoice->id }}</title>
    <style>
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
        }

        body {
            font-size: 12px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
        }

        .meta {
            margin-bottom: 16px;
        }

        .meta-table {
            width: 100%;
        }

        .meta-table td {
            padding: 2px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
        }

        .table th {
            background: #f8f9fa;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total {
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>INVOICE</h2>
        <div>No: {{ $invoice->transaction_number ?? $invoice->id }}</div>
        <div>Tanggal: {{ optional($invoice->created_at)->format('d/m/Y H:i') }}</div>
    </div>

    <div class="meta">
        <table class="meta-table">
            <tr>
                <td width="50%">Kasir: {{ $invoice->user->name ?? '-' }}</td>
                <td width="50%">Pelanggan: {{ $invoice->customer->name ?? 'Umum' }}</td>
            </tr>
            <tr>
                <td>Metode Pembayaran: {{ $invoice->payment_method ?? '-' }}</td>
                <td>Status: {{ $invoice->status ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-center">Jumlah</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->details as $item)
                @php
                    $qty = $item->qty ?? 1;
                    $price = $item->price ?? 0;
                    $discount = $item->discount ?? 0;
                    $subtotal = $item->subtotal ?? ($price * $qty - $discount);
                @endphp
                <tr>
                    <td>{{ $item->product_name ?? ($item->product->name ?? '-') }}</td>
                    <td class="text-center">{{ $qty }}</td>
                    <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($discount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total">Subtotal</td>
                <td class="total">Rp {{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="total">Diskon</td>
                <td class="total">Rp {{ number_format($invoice->discount ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="total">Pajak</td>
                <td class="total">Rp {{ number_format($invoice->tax ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="total">Biaya Lain</td>
                <td class="total">Rp {{ number_format($invoice->other_fee ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="total">Grand Total</td>
                <td class="total">Rp {{ number_format($invoice->grand_total ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="total">Dibayar</td>
                <td class="total">Rp {{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="total">Kembalian</td>
                <td class="total">Rp {{ number_format($invoice->change_amount ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
