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

    @include('partials.sidebar')

    <main class="adm-content">
        <div class="adm-topbar">
            <button type="button" class="menu-toggle" onclick="toggleSidebar()" aria-label="Menu">&#9776;</button>
            <strong>@yield('title', 'Admin')</strong>
        </div>
        @yield('content')
    </main>

</div>

@include('partials.echo')
@include('partials.order-listener')

</body>
</html>
