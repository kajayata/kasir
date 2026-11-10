@extends('layouts.admin')

@section('title', 'Transaksi Ditahan')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="page-head">
            <span>Transaksi Ditahan</span>
            <a href="{{ route('kasir') }}" class="btn btn-primary">Transaksi Baru</a>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th>Waktu Ditahan</th>
                    <th class="text-center">Item</th>
                    <th class="text-right">Total</th>
                    <th width="240">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi as $t)
                    <tr>
                        <td>{{ $t->id }}</td>
                        <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">{{ $t->details->sum('qty') }}</td>
                        <td class="text-right"><strong>{{ 'Rp '.number_format($t->grand_total, 0, ',', '.') }}</strong></td>
                        <td>
                            <a href="{{ route('kasir', ['resume' => $t->id]) }}" class="btn btn-primary" style="padding:0 14px">Lanjutkan</a>
                            <form method="POST" action="{{ route('kasir.cancel', $t) }}" style="display:inline"
                                  onsubmit="return confirm('Batalkan transaksi #{{ $t->id }}?')">
                                @csrf
                                <button type="submit" class="btn btn-danger" style="padding:0 14px">Batal</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-cart">Tidak ada transaksi yang ditahan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
