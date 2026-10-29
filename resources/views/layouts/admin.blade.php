<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Kasir Retail POS</title>
    <link rel="stylesheet" href="{{ asset('css/chasier.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

<div class="admin-shell">

    <aside class="adm-side">

        <div class="adm-brand">
            <strong>TOKO RETAIL MAKMUR</strong>
            <span>Panel Admin</span>
        </div>

        <nav class="adm-menu">
            <a href="{{ route('kasir') }}" class="{{ request()->routeIs('kasir') ? 'adm-active' : '' }}">
                Transaksi Baru
            </a>
            <a href="{{ route('kasir.produk.index') }}" class="{{ request()->routeIs('kasir.produk.*') ? 'adm-active' : '' }}">
                Kelola Produk
            </a>
            <a href="{{ route('kasir.ditahan') }}" class="{{ request()->routeIs('kasir.ditahan') ? 'adm-active' : '' }}">
                Transaksi Ditahan
            </a>
            <a href="{{ route('kasir.riwayat') }}" class="{{ request()->routeIs('kasir.riwayat') ? 'adm-active' : '' }}">
                Riwayat Transaksi
            </a>
        </nav>

        <div class="adm-side-foot">
            Kasir: {{ auth()->user()->name }}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>

    </aside>

    <main class="adm-content">
        @yield('content')
    </main>

</div>

@include('partials.echo')
@include('partials.order-listener')

</body>
</html>
