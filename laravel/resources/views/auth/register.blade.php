<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Let's Play Indonesia</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>

    {{-- Kumpulan ikon (dipakai ulang lewat <use>) --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <symbol id="i-user" viewBox="0 0 24 24">
            <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-8 2.2-8 5v2a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2c0-2.8-3.6-5-8-5Z"/>
        </symbol>
        <symbol id="i-mail" viewBox="0 0 24 24">
            <path d="M3 5h18a1 1 0 0 1 1 1v.4l-10 6.2L2 6.4V6a1 1 0 0 1 1-1Zm19 3.75V18a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8.75l9.47 5.87a1 1 0 0 0 1.06 0L22 8.75Z"/>
        </symbol>
        <symbol id="i-lock" viewBox="0 0 24 24">
            <path d="M12 2a5 5 0 0 0-5 5v2H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5Zm-3 7V7a3 3 0 0 1 6 0v2H9Zm3 4a1.8 1.8 0 0 1 1 3.3V18a1 1 0 0 1-2 0v-1.7A1.8 1.8 0 0 1 12 13Z"/>
        </symbol>
        <symbol id="i-school" viewBox="0 0 24 24">
            <path d="M12 2 1 8l11 6 9-4.9V16h2V8L12 2ZM5 13.2V17c0 1.7 3.1 3 7 3s7-1.3 7-3v-3.8l-7 3.8-7-3.8Z"/>
        </symbol>
        <symbol id="i-bag" viewBox="0 0 24 24">
            <path d="M9 3a2 2 0 0 0-2 2v1H4a2 2 0 0 0-2 2v3h20V8a2 2 0 0 0-2-2h-3V5a2 2 0 0 0-2-2H9Zm0 2h6v1H9V5ZM2 13v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6h-8v1.5a1 1 0 0 1-2 0V13H2Z"/>
        </symbol>
        <symbol id="i-calendar" viewBox="0 0 24 24">
            <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1Zm15 9v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9h20Z"/>
        </symbol>
        <symbol id="i-phone" viewBox="0 0 24 24">
            <path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1L6.6 10.8Z"/>
        </symbol>
    </svg>

    <img src="{{ asset('images/login/logo_letsplay.png') }}" alt="Let's Play Indonesia" class="logo">

    <main class="page">

        <header class="hero">
            <h1>Create Your Player</h1>
            <p>Yuk, buat akun dulu untuk mulai perjalanmu<br>di Let's Play Indonesia</p>
        </header>

        <section class="register-card">

            @if ($errors->any())
                <div class="alert" role="alert">
                    <strong>Pendaftaran gagal.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.store') }}" method="POST">
                @csrf

                {{-- ===== STEP 1 ===== --}}
                <h2 class="step-title">STEP 1 <span>·</span> Info Diri</h2>

                <div class="grid">
                    <div class="field">
                        <label for="nama">Nama Lengkap :</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true"><use href="#i-user"/></svg>
                            <input type="text" id="nama" name="nama" value="{{ old('nama') }}"
                                   placeholder="Masukkan nama lengkap" autocomplete="name" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="email">Email :</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true"><use href="#i-mail"/></svg>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   placeholder="contoh@gmail.com" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password :</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true"><use href="#i-lock"/></svg>
                            <input type="password" id="password" name="password" minlength="8"
                                   placeholder="Buat password (min. 8 karakter)" autocomplete="new-password" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Konfirmasi Password :</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true"><use href="#i-lock"/></svg>
                            <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                                   placeholder="Ulangi password" autocomplete="new-password" required>
                        </div>
                    </div>
                </div>

                <hr class="step-divider">

                {{-- ===== STEP 2 ===== --}}
                <h2 class="step-title">STEP 2 <span>·</span> Info Magang</h2>

                <div class="grid">
                    <div class="field">
                        <label for="asal_instansi">Asal Sekolah/Universitas :</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true"><use href="#i-school"/></svg>
                            <input type="text" id="asal_instansi" name="asal_instansi" value="{{ old('asal_instansi') }}"
                                   placeholder="Contoh : Universitas Brawijaya" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="bidang_magang">Bidang Magang :</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true"><use href="#i-bag"/></svg>
                            <input type="text" id="bidang_magang" name="bidang_magang" value="{{ old('bidang_magang') }}"
                                   placeholder="Contoh : Backend Developer" required>
                        </div>
                    </div>

                    {{-- Tanggal: input tampilan (teks) + input tersembunyi yang dikirim ke backend (format Y-m-d) --}}
                    <div class="field">
                        <label for="tanggal_mulai_view">Tanggal Mulai Magang :</label>
                        <div class="input-wrap datepicker">
                            <svg aria-hidden="true"><use href="#i-calendar"/></svg>
                            <input type="text" id="tanggal_mulai_view" class="dp-input"
                                   placeholder="Pilih tanggal mulai" autocomplete="off" inputmode="none" required>
                            <input type="hidden" id="tanggal_mulai" name="tanggal_mulai" class="dp-value"
                                   value="{{ old('tanggal_mulai') }}">
                        </div>
                    </div>

                    <div class="field">
                        <label for="tanggal_selesai_view">Tanggal Selesai Magang :</label>
                        <div class="input-wrap datepicker align-right" data-min="tanggal_mulai">
                            <svg aria-hidden="true"><use href="#i-calendar"/></svg>
                            <input type="text" id="tanggal_selesai_view" class="dp-input"
                                   placeholder="Pilih tanggal selesai" autocomplete="off" inputmode="none" required>
                            <input type="hidden" id="tanggal_selesai" name="tanggal_selesai" class="dp-value"
                                   value="{{ old('tanggal_selesai') }}">
                        </div>
                    </div>

                    <div class="field full">
                        <label for="no_wa">Nomor WhatsApp :</label>
                        <div class="input-wrap">
                            <svg aria-hidden="true"><use href="#i-phone"/></svg>
                            <input type="tel" id="no_wa" name="no_wa" value="{{ old('no_wa') }}"
                                   placeholder="08xxxxxxxxxx" autocomplete="tel" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary">CREATE MY PLAYER</button>
            </form>

            <p class="login-link">
                Sudah punya akun?
                <a href="/login-test">Login di sini</a>
            </p>

        </section>
    </main>

    <img src="{{ asset('images/login/kucing_register.png') }}" alt="" class="mascot">

    <script src="{{ asset('js/register.js') }}"></script>

</body>
</html>