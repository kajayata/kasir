@extends('layouts.admin')

@section('title', 'Kelola Produk')

@section('content')

@if (session('success'))
    <div class="adm-flash adm-flash-ok">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <div class="page-head">
            <span>Daftar Produk ({{ $produk->total() }})</span>
            <a href="{{ route('kasir.produk.create') }}" class="btn btn-success" style="text-decoration:none; line-height:36px">+ Tambah Produk</a>
        </div>
    </div>

    <div class="card-body" style="padding-bottom:0">
        <form method="GET" action="{{ route('kasir.produk.index') }}" class="search-area filter-bar">
            <div class="search-input">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / kode / barcode..." autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            @if ($q !== '')
                <a href="{{ route('kasir.produk.index') }}" class="btn btn-secondary" style="text-decoration:none; line-height:38px">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper" style="padding:16px 16px 0">
        <table>
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="70">Gambar</th>
                    <th width="110">Kode</th>
                    <th width="150">Barcode</th>
                    <th>Nama Barang</th>
                    <th width="130" class="text-right">Harga</th>
                    <th width="90" class="text-center">Stok</th>
                    <th width="170">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($produk as $p)
                    <tr>
                        <td>{{ $produk->firstItem() + $loop->index }}</td>
                        <td>
                            @if ($p->image)
                                <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #e9ecef">
                            @else
                                <span style="color:#adb5bd">—</span>
                            @endif
                        </td>
                        <td><code>{{ $p->code }}</code></td>
                        <td>{{ $p->barcode ?? '—' }}</td>
                        <td><strong>{{ $p->name }}</strong></td>
                        <td class="text-right"><strong>{{ 'Rp '.number_format($p->price, 0, ',', '.') }}</strong></td>
                        <td class="text-center">
                            @if ($p->stock > 0)
                                {{ $p->stock }}
                            @else
                                <span style="color:#dc3545">habis</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('kasir.produk.edit', $p) }}" class="btn btn-primary" style="text-decoration:none; padding:0 14px; line-height:32px">Edit</a>
                            <form method="POST" action="{{ route('kasir.produk.destroy', $p) }}" style="display:inline"
                                  onsubmit="return confirm('Hapus {{ $p->name }}? Riwayat transaksi lama tidak terpengaruh.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding:0 14px; line-height:32px">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-cart">
                            @if ($q !== '')
                                Produk "{{ $q }}" tidak ditemukan.
                            @else
                                Belum ada produk. Klik "Tambah Produk" untuk menambahkan barang.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($produk->hasPages())
        <div style="padding:16px">
            {{ $produk->links() }}
        </div>
    @endif
</div>

@endsection
