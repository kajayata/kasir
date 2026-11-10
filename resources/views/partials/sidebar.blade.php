<aside class="adm-side">
    <div class="adm-brand">
        <strong>TOKO RETAIL MAKMUR</strong>
        <span>Panel Admin</span>
    </div>

    <nav class="adm-menu">
        <a href="{{ route('kasir') }}" class="{{ request()->routeIs('kasir') ? 'adm-active' : '' }}">Transaksi Baru</a>
        <a href="{{ route('kasir.produk.index') }}" class="{{ request()->routeIs('kasir.produk.*') ? 'adm-active' : '' }}">Kelola Produk</a>
        <a href="{{ route('kasir.ditahan') }}" class="{{ request()->routeIs('kasir.ditahan') ? 'adm-active' : '' }}">Transaksi Ditahan</a>
        <a href="{{ route('kasir.riwayat') }}" class="{{ request()->routeIs('kasir.riwayat') ? 'adm-active' : '' }}">Riwayat Transaksi</a>
    </nav>

    <div class="adm-side-foot">
        Kasir: {{ auth()->user()->name }}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
</aside>

<div class="adm-overlay" onclick="closeSidebar()"></div>

<script>
    function toggleSidebar() {
        var shell = document.querySelector('.admin-shell');
        if (!shell) return;
        if (window.matchMedia('(max-width: 800px)').matches) {
            shell.classList.toggle('sidebar-open');
        } else {
            shell.classList.toggle('sidebar-collapsed');
        }
    }

    function closeSidebar() {
        var shell = document.querySelector('.admin-shell');
        if (shell) shell.classList.remove('sidebar-open');
    }
</script>
