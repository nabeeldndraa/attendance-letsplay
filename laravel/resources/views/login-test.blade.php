<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Let's Play Indonesia</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tilt+Warp&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    {{-- Popup sukses --}}
    @if (session('success'))
        <div class="notification" id="notification" role="status">
            <div class="notification-icon">✓</div>

            <div class="notification-text">
                <strong>Login Berhasil</strong>
                <span>{{ session('success') }}</span>
            </div>

            <button type="button" class="close-popup" aria-label="Tutup notifikasi" onclick="closeNotification()">&times;</button>
        </div>
    @endif

    {{-- Popup error --}}
    @if ($errors->any() || session('error'))
        <div class="notification" id="notification" role="alert">
            <div class="notification-icon">!</div>

            <div class="notification-text">
                <strong>Login Gagal</strong>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>

            <button type="button" class="close-popup" aria-label="Tutup notifikasi" onclick="closeNotification()">&times;</button>
        </div>
    @endif

    <img src="{{ asset('images/login/logo_letsplay.png') }}" alt="Let's Play Indonesia" class="logo">

    <main class="page">

        <header class="hero">
            <h1>Ready To Play?</h1>
            <p class="welcome">Haii. Welcome Back, BOCIL!</p>
            <p class="hint">Alangkah baiknya login dulu ya buat lanjut!</p>
        </header>

        <section class="login-card">

            <form action="{{ url('/login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email">Email :</label>

                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 5h18a1 1 0 0 1 1 1v.4l-10 6.2L2 6.4V6a1 1 0 0 1 1-1Zm19 3.75V18a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8.75l9.47 5.87a1 1 0 0 0 1.06 0L22 8.75Z"/>
                        </svg>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Masukkan Email kamu"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password :</label>

                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2a5 5 0 0 0-5 5v2H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5Zm-3 7V7a3 3 0 0 1 6 0v2H9Zm3 4a1.8 1.8 0 0 1 1 3.3V18a1 1 0 0 1-2 0v-1.7A1.8 1.8 0 0 1 12 13Z"/>
                        </svg>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan Password"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <div class="options">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1">
                        Ingat Saya
                    </label>

                    {{-- TODO: sesuaikan URL dengan route lupa password dari backend --}}
                    <a href="{{ url('/forgot-password') }}" class="forgot">Lupa Password?</a>
                </div>

                <button type="submit" class="btn-primary">LET'S GO!</button>
            </form>

            <div class="divider">atau</div>

            {{-- TODO: ganti href dengan route login Google dari backend (mis. url('/auth/google')) --}}
            <a href="#" class="btn-google">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65Z"/>
                    <path fill="#FBBC05" d="M10.53 28.59A14.5 14.5 0 0 1 9.5 24c0-1.59.28-3.14.76-4.59l-7.98-6.19A23.99 23.99 0 0 0 0 24c0 3.88.92 7.55 2.56 10.78l7.97-6.19Z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
                </svg>
                Masuk dengan Google
            </a>

            <p class="register">
                New intern?
                {{-- TODO: sesuaikan URL dengan route register dari backend --}}
                <a href="{{ url('/register') }}" class="register-link">Create Account</a>
            </p>

        </section>
    </main>

    <img src="{{ asset('images/login/kucing_login.png') }}" alt="" class="mascot">

    <script>
        function closeNotification() {
            const notification = document.getElementById('notification');

            if (notification) {
                notification.style.animation = 'fadeOut 0.2s ease forwards';

                setTimeout(() => {
                    notification.remove();
                }, 200);
            }
        }

        // Popup menghilang otomatis setelah 4 detik.
        document.addEventListener('DOMContentLoaded', () => {
            const notification = document.getElementById('notification');

            if (notification) {
                setTimeout(closeNotification, 4000);
            }
        });
    </script>

</body>
</html>