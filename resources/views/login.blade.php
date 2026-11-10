<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Kasir Retail POS</title>
    <link rel="stylesheet" href="{{ asset('css/chasier.css') }}">
</head>
<body class="login-page">
<div class="login-box">
    <div class="card">
        <div class="card-header">Kasir Retail POS — Login</div>
        <div class="card-body">

            @if ($errors->any())
                <div class="login-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="form-group" style="display:flex; align-items:center; gap:6px;">
                    <input type="checkbox" id="remember" name="remember" value="1" style="width:auto; height:auto;">
                    <label for="remember" style="margin:0">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-primary">Masuk</button>
            </form>

            <p style="font-size:12px; color:#6c757d; margin-top:14px">
                Akun demo: <code>kasir@example.com</code> / <code>password</code>
            </p>
        </div>
    </div>
</div>
</body>
</html>
