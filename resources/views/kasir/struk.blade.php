<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaksi->transaction_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/struk.css') }}">
</head>
<body>

<div class="receipt-container">
    <div class="receipt">

        <div class="store-header">
            <img src="{{ asset('logo-toko.png') }}" class="store-logo" alt="Logo Toko">
            <div class="store-address">Jl. Contoh No. 123</div>
            <div class="store-address">Jember, Jawa Timur</div>
            <div class="store-phone">Telp. 0812-3456-7890</div>
        </div>

        <div class="separator"></div>

        <div class="transaction-info">
            <div class="transaction-row">
                <div class="transaction-label">No</div>
                <div class="transaction-value">{{ $transaksi->transaction_number }}</div>
            </div>
            <div class="transaction-row">
                <div class="transaction-label">Tanggal</div>
                <div class="transaction-value">{{ $transaksi->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="transaction-row">
                <div class="transaction-label">Kasir</div>
                <div class="transaction-value">{{ $transaksi->user->name }}</div>
            </div>
            <div class="transaction-row">
                <div class="transaction-label">Pelanggan</div>
                <div class="transaction-value">{{ $transaksi->customer->name ?? 'Umum' }}</div>
            </div>
            <div class="transaction-row">
                <div class="transaction-label">Bayar</div>
                <div class="transaction-value">{{ $transaksi->payment_method }}</div>
            </div>
        </div>

        <div class="separator"></div>

        @forelse ($transaksi->details as $detail)
            <div class="item">
                <div class="item-name">{{ $detail->product_name }}</div>
                <div class="item-detail">
                    <div class="item-price">{{ $detail->qty }} x {{ number_format($detail->price, 0, ',', '.') }}</div>
                    <div class="item-total">{{ number_format($detail->subtotal - $detail->discount, 0, ',', '.') }}</div>
                </div>
            </div>
        @empty
            <div class="item"><div class="item-name">(keranjang kosong)</div></div>
        @endforelse

        <div class="separator"></div>

        <div class="summary-row">
            <div class="summary-label">Subtotal</div>
            <div class="summary-value">{{ number_format($transaksi->subtotal, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Diskon</div>
            <div class="summary-value">{{ number_format($transaksi->discount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Pajak</div>
            <div class="summary-value">{{ number_format($transaksi->tax, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Biaya Lain</div>
            <div class="summary-value">{{ number_format($transaksi->other_fee, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row grand-total">
            <div class="summary-label">TOTAL</div>
            <div class="summary-value">{{ number_format($transaksi->grand_total, 0, ',', '.') }}</div>
        </div>

        <div class="separator"></div>

        <div class="summary-row">
            <div class="summary-label">Bayar</div>
            <div class="summary-value">{{ number_format($transaksi->paid_amount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Kembali</div>
            <div class="summary-value">{{ number_format($transaksi->change_amount, 0, ',', '.') }}</div>
        </div>

        <div class="receipt-footer">
            <div class="thank-you">TERIMA KASIH</div>
            <div>Selamat berbelanja kembali</div>
            <div style="margin-top: 3mm;">Barang yang sudah dibeli</div>
            <div>tidak dapat dikembalikan.</div>
        </div>

    </div>

    <div class="receipt-actions">
        <button type="button" class="btn-print" onclick="window.print()">Cetak</button>
        <a href="{{ route('kasir') }}" class="btn-close" style="text-decoration:none">Transaksi Baru</a>
    </div>

</div>

</body>
</html>
