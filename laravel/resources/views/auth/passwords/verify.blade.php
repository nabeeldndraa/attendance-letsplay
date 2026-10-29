<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Let's Play Indonesia</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <img src="{{ asset('images/login/logo_letsplay.png') }}" alt="Let's Play Indonesia" class="logo">

    <main class="page">
        <header class="hero">
            <h1>Reset Password</h1>
            <p class="hint">Masukkan kode dari email dan password baru.</p>
        </header>

        <section class="login-card">
            @if ($errors->any())
                <div role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ old('email', $email) }}">

                <div class="form-group">
                    <label for="code">Kode verifikasi :</label>
                    <div class="input-wrap">
                        <input type="text" id="code" name="code" inputmode="numeric"
                               pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"
                               placeholder="Masukkan 6 digit kode" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password baru :</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" minlength="8"
                               autocomplete="new-password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Ulangi password baru :</label>
                    <div class="input-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               minlength="8" autocomplete="new-password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">SIMPAN PASSWORD BARU</button>
            </form>

            <p class="register">
                <a href="{{ route('password.request') }}" class="register-link">Kirim kode baru</a>
                · <a href="{{ route('login') }}" class="register-link">Kembali ke login</a>
            </p>
        </section>
    </main>
</body>
</html>
