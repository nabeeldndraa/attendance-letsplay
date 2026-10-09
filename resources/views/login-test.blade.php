<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Let's Play Indonesia</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f7f7f7;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            background: #e30613;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .brand h1 {
            font-size: 22px;
            color: #222;
            margin-bottom: 6px;
        }

        .brand p {
            font-size: 14px;
            color: #777;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: #e30613;
            box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.08);
        }

        .login-button,
        .register-button {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .login-button {
            border: none;
            background: #e30613;
            color: white;
            cursor: pointer;
        }

        .login-button:hover {
            background: #c90510;
        }

        .login-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Pemisah login dan register */
        .register-section {
            margin-top: 22px;
            text-align: center;
        }

        .register-section p {
            margin-bottom: 12px;
            font-size: 13px;
            color: #777;
        }

        .register-button {
            border: 1px solid #e30613;
            background: #fff;
            color: #e30613;
        }

        .register-button:hover {
            background: #e30613;
            color: #fff;
        }

        .footer {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: #999;
        }

        /* Popup notifikasi */
        .success-popup {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 1000;
            width: min(360px, calc(100% - 32px));
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 17px 18px;
            background: #fff;
            border: 1px solid #eee;
            border-left: 4px solid #e30613;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            animation: slideIn 0.35s ease;
        }

        .success-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e30613;
            color: #fff;
            font-size: 19px;
            font-weight: bold;
        }

        .success-text {
            flex: 1;
        }

        .success-text strong {
            display: block;
            color: #222;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .success-text span {
            color: #777;
            font-size: 12px;
            line-height: 1.5;
        }

        .close-popup {
            align-self: flex-start;
            border: none;
            background: transparent;
            color: #999;
            font-size: 20px;
            cursor: pointer;
            padding: 0 2px;
        }

        .close-popup:hover {
            color: #333;
        }

        .error-popup {
            border-left-color: #e30613;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(25px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateY(-8px);
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 24px;
            }

            .brand h1 {
                font-size: 20px;
            }

            .success-popup {
                top: 16px;
                right: 16px;
            }
        }
    </style>
</head>

<body>

    {{-- Popup sukses --}}
    @if (session('success'))
    <div class="success-popup" id="successNotification" role="status">
        <div class="success-icon">✓</div>

        <div class="success-text">
            <strong>Berhasil</strong>
            <span>{{ session('success') }}</span>
        </div>

        <button type="button" class="close-popup" aria-label="Tutup notifikasi" onclick="closeNotification('successNotification')">&times;</button>
    </div>
    @endif

    {{-- Popup error --}}
    @if ($errors->any() || session('error'))
    <div class="success-popup error-popup" id="errorNotification" role="alert">
        <div class="success-icon">!</div>

        <div class="success-text">
            <strong>Login Gagal</strong>
            <span>{{ session('error') ?? $errors->first() }}</span>
        </div>

        <button type="button" class="close-popup" aria-label="Tutup notifikasi" onclick="closeNotification('errorNotification')">&times;</button>
    </div>
    @endif

    <main class="login-container">
        <section class="login-card">

            <div class="brand">
                <div class="brand-logo">LP</div>

                <h1>Let's Play Indonesia</h1>
                <p>Sistem Presensi Intern</p>
            </div>

            <form action="{{ url('/login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>

                    <input type="email" id="email" name="email" placeholder="Masukkan email" value="{{ old('email') }}" autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="login-button">
                    Login
                </button>
            </form>

            <div class="register-section">
                <p>Belum punya akun?</p>

                <a href="{{ route('register') }}" class="register-button">
                    Daftar Sekarang
                </a>
            </div>

            <div class="footer">
                Let's Play Indonesia &copy; 2026
            </div>

        </section>
    </main>

    <script>
        function closeNotification(id) {
            const notification = document.getElementById(id);

            if (notification) {
                notification.style.animation =
                    'fadeOut 0.2s ease forwards';

                setTimeout(() => {
                    notification.remove();
                }, 200);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            ['successNotification', 'errorNotification'].forEach(id => {
                const notification = document.getElementById(id);

                if (notification) {
                    setTimeout(() => closeNotification(id), 4000);
                }
            });
        });
    </script>

</body>

</html>