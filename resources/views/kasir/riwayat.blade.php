@extends('layouts.admin')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="page-head">
            <span>Riwayat Transaksi</span>
            <a href="{{ route('kasir') }}" class="btn btn-primary">Transaksi Baru</a>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Waktu</th>
                    <th>Kasir</th>
                    <th>Pelanggan</th>
                    <th>Metode</th>
                    <th class="text-center">Item</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Status</th>
                    <th width="80">Struk</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi as $t)
                    <tr>
                        <td><strong>{{ $t->transaction_number }}</strong></td>
                        <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $t->user->name }}</td>
                        <td>{{ $t->customer->name ?? 'Umum' }}</td>
                        <td>{{ $t->payment_method }}</td>
                        <td class="text-center">{{ $t->details->sum('qty') }}</td>
                        <td class="text-right"><strong>{{ 'Rp '.number_format($t->grand_total, 0, ',', '.') }}</strong></td>
                        <td class="text-center">
                            <span class="adm-badge adm-badge-{{ $t->status }}">{{ strtoupper($t->status) }}</span>
                        </td>
                        <td class="text-center">
                            @if ($t->status === 'paid')
                                <a href="{{ route('kasir.struk', $t) }}">Lihat</a> |
                                <a href="{{ route('kasir.invoice', $t) }}">PDF</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-cart">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($transaksi->hasPages())
        {{ $transaksi->links() }}
    @endif
</div>
@endsection
