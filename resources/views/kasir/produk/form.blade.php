@extends('layouts.admin')

@section('title', $produk->exists ? 'Ubah Produk' : 'Tambah Produk')

@section('content')
<div class="card">
    <div class="card-header">{{ $produk->exists ? 'Ubah Produk' : 'Tambah Produk' }}</div>
    <div class="card-body">

        @if ($errors->any())
            <div class="adm-flash adm-flash-error">Periksa kembali data yang diisi.</div>
        @endif

        <form method="POST" action="{{ $produk->exists ? route('kasir.produk.update', $produk) : route('kasir.produk.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($produk->exists)
                @method('PUT')
            @endif

            <div class="form-grid">

                <div class="form-group full">
                    <label for="name">Nama Barang</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $produk->name) }}" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="code">Kode Barang</label>
                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $produk->code) }}" required>
                    @error('code') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="barcode">Barcode <span style="color:#adb5bd">(opsional)</span></label>
                    <input type="text" class="form-control" id="barcode" name="barcode" value="{{ old('barcode', $produk->barcode) }}">
                    @error('barcode') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="price">Harga (Rp)</label>
                    <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $produk->price ?? 0) }}" min="0" step="100" required>
                    @error('price') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock', $produk->stock ?? 0) }}" min="0" required>
                    @error('stock') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group full">
                    <label for="image">Gambar Produk <span style="color:#adb5bd">(opsional, jpg/jpeg/png/webp, max 2MB)</span></label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    @error('image') <div class="form-error">{{ $message }}</div> @enderror
                    @if ($produk->image)
                        <div style="margin-top:10px">
                            <img src="{{ asset('storage/'.$produk->image) }}" alt="{{ $produk->name }}" style="max-width:160px; max-height:160px; border-radius:8px; border:1px solid #e9ecef">
                        </div>
                    @endif
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('kasir.produk.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>

    </div>
</div>
@endsection
