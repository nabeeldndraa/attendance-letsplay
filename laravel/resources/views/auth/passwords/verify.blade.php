<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Let's Play Indonesia</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <style>
        /* ===== Kode verifikasi 6 kotak ===== */
        .otp-group {
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        .otp-box {
            flex: 1 1 0;
            min-width: 0;
            height: 56px;
            padding: 0;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            background: #fff;
            border: 1px solid #8b2a2a;
            border-radius: 10px;
            outline: none;
            caret-color: #c62828;
            transition: border-color .15s, box-shadow .15s;
        }

        .otp-box:focus {
            border-color: #c62828;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, .2);
        }

        /* ===== Countdown kirim ulang (tampilan saja) ===== */
        .resend-timer {
            color: #666;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        /* ===== Tombol lihat/sembunyikan password ===== */
        .input-wrap.has-toggle {
            position: relative;
        }

        .input-wrap.has-toggle input {
            padding-right: 52px;
        }

        .toggle-pass {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            color: #8b2a2a;
            background: transparent;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
        }

        .toggle-pass:hover { color: #c62828; }

        .toggle-pass:focus-visible {
            outline: 2px solid #c62828;
            outline-offset: 2px;
        }

        /* ikon di icons.blade.php berupa garis, jadi perlu stroke */
        .toggle-pass svg {
            width: 22px;
            height: 22px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        @media (max-width: 420px) {
            .otp-group { gap: 6px; }
            .otp-box { height: 48px; font-size: 20px; }
        }
    </style>
</head>
<body>
    @include('partials.icons')

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
                    <label for="code-1">Kode verifikasi :</label>

                    {{-- Nilai 6 kotak digabung ke input hidden ini, nama "code" sama seperti sebelumnya --}}
                    <input type="hidden" id="code" name="code">

                    <div class="otp-group" role="group" aria-label="Kode verifikasi 6 digit">
                        @for ($i = 1; $i <= 6; $i++)
                            <input type="text" id="code-{{ $i }}" class="otp-box"
                                   inputmode="numeric" pattern="[0-9]" autocomplete="{{ $i === 1 ? 'one-time-code' : 'off' }}"
                                   aria-label="Digit {{ $i }}" required {{ $i === 1 ? 'autofocus' : '' }}>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password baru :</label>
                    <div class="input-wrap has-toggle">
                        <input type="password" id="password" name="password" minlength="8"
                               autocomplete="new-password" required>
                        <button type="button" class="toggle-pass" data-target="password"
                                aria-label="Tampilkan password" aria-pressed="false">
                            <svg aria-hidden="true"><use href="#i-eye"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Ulangi password baru :</label>
                    <div class="input-wrap has-toggle">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               minlength="8" autocomplete="new-password" required>
                        <button type="button" class="toggle-pass" data-target="password_confirmation"
                                aria-label="Tampilkan password" aria-pressed="false">
                            <svg aria-hidden="true"><use href="#i-eye"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">SIMPAN PASSWORD BARU</button>
            </form>

            <p class="register">
                <a href="{{ route('password.request') }}" class="register-link" id="resend-link">Kirim kode baru</a>
                <span class="resend-timer" id="resend-timer">(01:00)</span>
                · <a href="{{ route('login') }}" class="register-link">Kembali ke login</a>
            </p>
        </section>
    </main>

    <script>
        (function () {
            // ===== 6 kotak kode verifikasi: hanya angka =====
            var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
            var hidden = document.getElementById('code');

            function sync() {
                hidden.value = boxes.map(function (b) { return b.value; }).join('');
            }

            // Isi kotak mulai dari index tertentu (dipakai untuk ketik, paste, dan autofill)
            function fill(start, digits) {
                var i = start;
                for (var k = 0; k < digits.length && i < boxes.length; k++, i++) {
                    boxes[i].value = digits[k];
                }
                boxes[Math.min(i, boxes.length - 1)].focus();
                sync();
            }

            boxes.forEach(function (box, idx) {
                box.addEventListener('focus', function () { box.select(); });

                box.addEventListener('input', function () {
                    var digits = box.value.replace(/\D/g, '');
                    box.value = '';
                    if (!digits) { sync(); return; }
                    fill(idx, digits);
                });

                box.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace') {
                        if (box.value === '' && idx > 0) {
                            e.preventDefault();
                            boxes[idx - 1].value = '';
                            boxes[idx - 1].focus();
                            sync();
                        }
                    } else if (e.key === 'ArrowLeft' && idx > 0) {
                        e.preventDefault();
                        boxes[idx - 1].focus();
                    } else if (e.key === 'ArrowRight' && idx < boxes.length - 1) {
                        e.preventDefault();
                        boxes[idx + 1].focus();
                    }
                });

                box.addEventListener('paste', function (e) {
                    e.preventDefault();
                    var text = (e.clipboardData || window.clipboardData).getData('text');
                    var digits = text.replace(/\D/g, '');
                    if (digits) { fill(idx, digits); }
                });
            });

            // ===== Lihat / sembunyikan password =====
            document.querySelectorAll('.toggle-pass').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = document.getElementById(btn.dataset.target);
                    var show = input.type === 'password';

                    input.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                    btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
                    btn.querySelector('use').setAttribute('href', show ? '#i-eye-off' : '#i-eye');
                });
            });

            // ===== Countdown kirim ulang =====
            // Saat ini HANYA TAMPILAN (statis 01:00), belum dijalankan.
            // Nanti kalau backend cooldown sudah siap, aktifkan dengan membuka komentar di bawah.
            /*
            var timerEl = document.getElementById('resend-timer');
            var linkEl = document.getElementById('resend-link');
            var remaining = 60;

            function render() {
                var m = String(Math.floor(remaining / 60)).padStart(2, '0');
                var s = String(remaining % 60).padStart(2, '0');
                timerEl.textContent = '(' + m + ':' + s + ')';
            }

            linkEl.style.pointerEvents = 'none';
            linkEl.style.opacity = '.5';

            var interval = setInterval(function () {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(interval);
                    timerEl.style.display = 'none';
                    linkEl.style.pointerEvents = '';
                    linkEl.style.opacity = '';
                    return;
                }
                render();
            }, 1000);
            */
        })();
    </script>
</body>
</html>