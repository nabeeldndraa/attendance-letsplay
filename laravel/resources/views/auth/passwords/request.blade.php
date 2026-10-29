<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Let's Play Indonesia</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <img src="{{ asset('images/login/logo_letsplay.png') }}" alt="Let's Play Indonesia" class="logo">

    <main class="page">
        <header class="hero">
            <h1>Lupa Password?</h1>
            <p class="hint">Kami akan mengirim kode verifikasi ke email akunmu.</p>
        </header>

        <section class="login-card">
            @if (session('success'))
                <p role="status">{{ session('success') }}</p>
            @endif

            @if (session('error'))
                <p role="alert">{{ session('error') }}</p>
            @endif

            @if ($errors->any())
                <div role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Email akun :</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               autocomplete="email" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">KIRIM KODE VERIFIKASI</button>
            </form>

            <p class="register">
                <a href="{{ route('login') }}" class="register-link">Kembali ke login</a>
            </p>
        </section>
    </main>
</body>
</html>
