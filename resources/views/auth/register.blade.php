    
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Let's Play Indonesia</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #222;
            padding: 30px 16px;
        }

        .container {
            max-width: 620px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .06);
        }

        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .header h1 {
            color: #d71920;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .header p {
            color: #777;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        input {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #d71920;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .error {
            color: #d71920;
            font-size: 12px;
            margin-top: 5px;
        }

        .alert {
            padding: 12px;
            margin-bottom: 18px;
            background: #fff0f0;
            color: #b91c1c;
            border-radius: 7px;
            font-size: 13px;
        }

        .submit-button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #d71920;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 5px;
        }

        .submit-button:hover {
            background: #b9151b;
        }

        .login-link {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
        }

        .login-link a {
            color: #d71920;
            font-weight: bold;
            text-decoration: none;
        }

        @media (max-width: 480px) {
            .container {
                padding: 22px 18px;
            }

            .row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .header h1 {
                font-size: 23px;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>Let's Play Indonesia</h1>
        <p>Daftar akun presensi magang</p>
    </div>

    @if ($errors->any())
        <div class="alert">
            <strong>Pendaftaran gagal.</strong>
            <ul style="margin: 8px 0 0 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="nama">Nama lengkap</label>
            <input
                type="text"
                id="nama"
                name="nama"
                value="{{ old('nama') }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="asal_instansi">Asal instansi / kampus</label>
            <input
                type="text"
                id="asal_instansi"
                name="asal_instansi"
                value="{{ old('asal_instansi') }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="bidang_magang">Bidang magang</label>
            <input
                type="text"
                id="bidang_magang"
                name="bidang_magang"
                value="{{ old('bidang_magang') }}"
                placeholder="Contoh: Backend Developer"
                required
            >
        </div>

        <div class="row">
            <div class="form-group">
                <label for="tanggal_mulai">Tanggal mulai magang</label>
                <input
                    type="date"
                    id="tanggal_mulai"
                    name="tanggal_mulai"
                    value="{{ old('tanggal_mulai') }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="tanggal_selesai">Tanggal selesai magang</label>
                <input
                    type="date"
                    id="tanggal_selesai"
                    name="tanggal_selesai"
                    value="{{ old('tanggal_selesai') }}"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="no_wa">Nomor WhatsApp</label>
            <input
                type="tel"
                id="no_wa"
                name="no_wa"
                value="{{ old('no_wa') }}"
                placeholder="Contoh: 08xxxxxxxxxx"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                required
            >
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                minlength="8"
                required
            >
        </div>

        <button type="submit" class="submit-button">
            Daftar Sekarang
        </button>
    </form>

    <div class="login-link">
        Sudah punya akun?
        <a href="/login-test">Login di sini</a>
    </div>

</div>

</body>
</html>