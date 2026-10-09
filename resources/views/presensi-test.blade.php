<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Presensi - Let's Play Indonesia</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --red: #d71920;
            --red-dark: #b9151b;
            --black: #171717;
            --gray: #6b6b6b;
            --light-gray: #f5f5f5;
            --border: #e5e5e5;
            --white: #ffffff;
            --green: #20843d;
        }

        body {
            margin: 0;
            background: #f6f6f6;
            color: var(--black);
            font-family: Arial, Helvetica, sans-serif;
        }

        button,
        select,
        textarea {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        /* =========================
           DESKTOP HEADER
        ========================= */

        .top-header {
            display: none;
        }

        /* =========================
           MAIN
        ========================= */

        .page {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 24px 18px 100px;
        }

        .back-button {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: var(--white);
            font-size: 20px;
            margin-bottom: 18px;
        }

        .page-title {
            margin: 0;
            font-size: 25px;
            font-weight: 700;
        }

        .page-subtitle {
            margin: 7px 0 25px;
            color: var(--gray);
            font-size: 14px;
        }

        /* =========================
           PRESENCE CARD
        ========================= */

        .attendance-card {
            background: var(--white);
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 18px;
            border: 1px solid var(--border);
        }

        .card-label {
            color: var(--gray);
            font-size: 13px;
            margin-bottom: 8px;
        }

        .attendance-time {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .attendance-status {
            font-size: 14px;
            color: var(--gray);
        }

        /* =========================
           SECTION
        ========================= */

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        /* =========================
           WORK TYPE
        ========================= */

        .work-type {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .work-option {
            border: 1px solid var(--border);
            background: var(--white);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            font-weight: 600;
            color: var(--black);
        }

        .work-option.active {
            border-color: var(--red);
            background: #fff1f1;
            color: var(--red);
        }

        /* =========================
           LOCATION
        ========================= */

        .location-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 17px;
        }

        .location-main {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .location-icon {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #fff1f1;
            font-size: 20px;
        }

        .location-name {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.4;
        }

        .location-status {
            margin-top: 3px;
            color: var(--gray);
            font-size: 13px;
        }

        .location-distance {
            margin-top: 4px;
            color: var(--gray);
            font-size: 12px;
        }

        .location-button {
            width: 100%;
            margin-top: 15px;
            padding: 12px;
            border: 1px solid var(--red);
            border-radius: 10px;
            background: var(--white);
            color: var(--red);
            font-weight: 700;
        }

        .location-button:hover {
            background: #fff1f1;
        }

        .location-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .coordinates {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
            color: #999;
            font-size: 10px;
        }

        /* =========================
           WFH INFO
        ========================= */

        .wfh-info {
            padding: 15px;
            border-radius: 12px;
            background: #f7f7f7;
            color: var(--gray);
            font-size: 13px;
            line-height: 1.5;
        }

        /* =========================
           PROGRESS
        ========================= */

        textarea {
            width: 100%;
            min-height: 110px;
            resize: vertical;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            outline: none;
            background: var(--white);
        }

        textarea:focus {
            border-color: var(--red);
        }

        /* =========================
           SUBMIT
        ========================= */

        .submit-button {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 15px;
            background: var(--red);
            color: var(--white);
            font-weight: 700;
            margin-top: 4px;
        }

        .submit-button:hover {
            background: var(--red-dark);
        }

        .submit-button:disabled {
            background: #bdbdbd;
            cursor: not-allowed;
        }

        /* =========================
           MESSAGE
        ========================= */

        .message {
            display: none;
            margin-top: 15px;
            padding: 13px;
            border-radius: 10px;
            font-size: 13px;
            line-height: 1.4;
        }

        .message.success {
            display: block;
            background: #edf8ef;
            color: var(--green);
        }

        .message.error {
            display: block;
            background: #fff0f0;
            color: #b00000;
        }

        /* =========================
           BOTTOM NAV
        ========================= */

        .bottom-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            height: 68px;
            background: var(--white);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 100;
        }

        .nav-item {
            border: none;
            background: transparent;
            color: #888;
            font-size: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .nav-icon {
            font-size: 18px;
        }

        .nav-item.active {
            color: var(--red);
            font-weight: 700;
        }

        /* =========================
           DESKTOP
        ========================= */

        @media (min-width: 768px) {

            body {
                background: #f5f5f5;
            }

            .top-header {
                height: 70px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 40px;
                background: var(--white);
                border-bottom: 1px solid var(--border);
            }

            .brand {
                font-size: 18px;
                font-weight: 800;
            }

            .desktop-nav {
                display: flex;
                gap: 28px;
            }

            .desktop-nav button {
                border: none;
                background: transparent;
                color: #777;
                font-size: 14px;
                font-weight: 600;
            }

            .desktop-nav button.active {
                color: var(--red);
            }

            .page {
                max-width: 850px;
                padding: 35px 25px 50px;
            }

            .back-button {
                display: none;
            }

            .page-title {
                font-size: 30px;
            }

            .page-subtitle {
                margin-bottom: 30px;
            }

            .attendance-card {
                padding: 25px;
            }

            .bottom-nav {
                display: none;
            }

            .form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 18px;
            }

            .location-section {
                grid-column: 1 / -1;
            }

            .submit-button {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- DESKTOP HEADER -->

    <header class="top-header">

        <div class="brand">
            Let's Play Indonesia
        </div>

        <nav class="desktop-nav">

            <button>
                Home
            </button>

            <button class="active">
                Presensi
            </button>

            <button>
                Riwayat
            </button>

            <button>
                Leaderboard
            </button>

            <button>
                Profil
            </button>

        </nav>

    </header>


    <!-- MAIN -->

    <main class="page">

        <button class="back-button" type="button">
            ←
        </button>


        <h1 class="page-title">
            Presensi
        </h1>

        <p class="page-subtitle">
            Catat kehadiran dan aktivitas kerja hari ini.
        </p>


        <!-- STATUS PRESENSI -->

        <div class="attendance-card">

            <div class="card-label">
                Presensi Hari Ini
            </div>

            <div class="attendance-time" id="currentTime">
                --:--
            </div>

            <div class="attendance-status" id="attendanceStatus">
                Belum melakukan presensi
            </div>

        </div>


        <div class="form-grid">

            <!-- TIPE KERJA -->

            <section class="section">

                <div class="section-title">
                    Tipe Kerja
                </div>

                <div class="work-type">

                    <button type="button" class="work-option active" data-value="WFO">
                        WFO
                    </button>

                    <button type="button" class="work-option" data-value="WFH">
                        WFH
                    </button>

                </div>

                <input type="hidden" id="tipe_kerja" value="WFO">

            </section>


            <!-- LOKASI -->

            <section class="section location-section" id="lokasiSection">

                <div class="section-title">
                    Lokasi
                </div>

                <div class="location-card">

                    <div class="location-main">

                        <div class="location-icon">
                            📍
                        </div>

                        <div>

                            <div class="location-name" id="namaLokasi">
                                Lokasi belum diambil
                            </div>

                            <div class="location-status" id="statusLokasi">
                                Ambil lokasi untuk mendeteksi kantor.
                            </div>

                            <div class="location-distance" id="jarakLokasi">
                            </div>

                        </div>

                    </div>


                    <button type="button" class="location-button" id="btnLokasi">
                        Ambil Lokasi Saya
                    </button>


                    <div class="coordinates" id="koordinat">
                    </div>

                </div>

            </section>


            <!-- PROGRESS -->

            <section class="section">

                <div class="section-title">
                    Progress Hari Ini
                </div>

                <textarea id="progress_hari_ini" placeholder="Tuliskan progress yang dikerjakan hari ini..."></textarea>

            </section>

        </div>


        <!-- WFH INFO -->

        <div class="wfh-info" id="wfhInfo" style="display: none;">
            WFH tidak membutuhkan validasi lokasi kantor.
            Anda dapat melakukan presensi dari lokasi mana pun.
        </div>


        <!-- SUBMIT -->

        <button type="button" class="submit-button" id="btnPresensi" disabled>
            Kirim Presensi
        </button>


        <!-- MESSAGE -->

        <div id="message" class="message"></div>

    </main>


    <!-- MOBILE BOTTOM NAV -->

    <nav class="bottom-nav">

        <button class="nav-item">
            <span class="nav-icon">⌂</span>
            <span>Home</span>
        </button>

        <button class="nav-item active">
            <span class="nav-icon">✓</span>
            <span>Presensi</span>
        </button>

        <button class="nav-item">
            <span class="nav-icon">◷</span>
            <span>Riwayat</span>
        </button>

        <button class="nav-item">
            <span class="nav-icon">★</span>
            <span>Leaderboard</span>
        </button>

        <button class="nav-item">
            <span class="nav-icon">○</span>
            <span>Profil</span>
        </button>

    </nav>


    <script>
    const tipeKerjaInput =
        document.getElementById('tipe_kerja');

    const lokasiSection =
        document.getElementById('lokasiSection');

    const wfhInfo =
        document.getElementById('wfhInfo');

    const btnLokasi =
        document.getElementById('btnLokasi');

    const btnPresensi =
        document.getElementById('btnPresensi');

    const namaLokasi =
        document.getElementById('namaLokasi');

    const statusLokasi =
        document.getElementById('statusLokasi');

    const jarakLokasi =
        document.getElementById('jarakLokasi');

    const koordinat =
        document.getElementById('koordinat');

    const message =
        document.getElementById('message');

    const currentTime =
        document.getElementById('currentTime');

    const attendanceStatus =
        document.getElementById('attendanceStatus');


    let latitude = null;
    let longitude = null;

    let lokasiValid = false;
    let sudahPresensiHariIni = false;


    /* =========================
       JAM REAL-TIME
    ========================= */

    function updateTime() {

        // Jangan ubah jam kalau sudah presensi
        if (sudahPresensiHariIni) {
            return;
        }

        const now = new Date();

        const hours =
            String(now.getHours()).padStart(2, '0');

        const minutes =
            String(now.getMinutes()).padStart(2, '0');

        currentTime.textContent =
            hours + ':' + minutes;
    }

    updateTime();

    setInterval(updateTime, 1000);


    /* =========================
       MESSAGE
    ========================= */

    function tampilkanPesan(text, type) {

        message.textContent = text;

        message.className =
            'message ' + type;

        message.style.display = 'block';
    }


    /* =========================
       RESET LOCATION
    ========================= */

    function resetLokasi() {

        latitude = null;

        longitude = null;

        lokasiValid = false;

        namaLokasi.textContent =
            'Lokasi belum diambil';

        statusLokasi.textContent =
            'Ambil lokasi untuk mendeteksi kantor.';

        jarakLokasi.textContent = '';

        koordinat.textContent = '';

        if (!sudahPresensiHariIni) {
            btnPresensi.disabled = true;
        }
    }


    /* =========================
       KUNCI FORM SETELAH PRESENSI
    ========================= */

    function kunciFormPresensi() {

        btnPresensi.disabled = true;

        btnPresensi.textContent =
            'Sudah Presensi';

        btnLokasi.disabled = true;

        document
            .querySelectorAll('.work-option')
            .forEach(button => {
                button.disabled = true;
            });
    }


    /* =========================
       CEK STATUS PRESENSI HARI INI
    ========================= */

    async function cekStatusPresensiHariIni() {

        try {

            const response =
                await fetch(
                    '/presensi/status-hari-ini',
                    {
                        method: 'GET',

                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );


            const result =
                await response.json();


            if (!response.ok || !result.success) {
                return;
            }


            if (result.sudah_presensi) {

                sudahPresensiHariIni = true;


                /* =========================
                   STATUS CARD
                ========================= */

                attendanceStatus.textContent =
                    'Sudah melakukan presensi';


                /* =========================
                   JAM PRESENSI
                ========================= */

                if (result.data.jam_presensi) {

                    currentTime.textContent =
                        result.data.jam_presensi.substring(0, 5);

                }


                /* =========================
                   KUNCI FORM
                ========================= */

                kunciFormPresensi();


                /* =========================
                   TAMPILKAN INFORMASI PRESENSI
                ========================= */

                if (result.data.tipe_kerja) {

                    tipeKerjaInput.value =
                        result.data.tipe_kerja;

                }

            } else {

                sudahPresensiHariIni = false;

                attendanceStatus.textContent =
                    'Belum melakukan presensi';

                updateTime();
            }

        } catch (error) {

            console.error(
                'Gagal mengecek status presensi:',
                error
            );

        }
    }


    /* =========================
       WFO / WFH
    ========================= */

    document
        .querySelectorAll('.work-option')
        .forEach(button => {

            button.addEventListener(
                'click',
                function() {

                    // Jangan izinkan perubahan
                    // kalau sudah presensi
                    if (sudahPresensiHariIni) {
                        return;
                    }


                    document
                        .querySelectorAll('.work-option')
                        .forEach(item => {

                            item.classList.remove(
                                'active'
                            );

                        });


                    this.classList.add('active');


                    tipeKerjaInput.value =
                        this.dataset.value;


                    message.style.display =
                        'none';


                    resetLokasi();


                    if (
                        this.dataset.value === 'WFO'
                    ) {

                        lokasiSection.style.display =
                            'block';

                        wfhInfo.style.display =
                            'none';

                    } else {

                        lokasiSection.style.display =
                            'none';

                        wfhInfo.style.display =
                            'block';

                        btnPresensi.disabled =
                            false;
                    }

                }
            );

        });


    /* =========================
       AMBIL GPS
    ========================= */

    btnLokasi.addEventListener(
        'click',
        function() {

            if (sudahPresensiHariIni) {
                return;
            }


            if (!navigator.geolocation) {

                tampilkanPesan(
                    'Browser tidak mendukung GPS.',
                    'error'
                );

                return;
            }


            btnLokasi.disabled = true;

            btnLokasi.textContent =
                'Mendeteksi lokasi...';

            btnPresensi.disabled = true;


            namaLokasi.textContent =
                'Mencari lokasi...';

            statusLokasi.textContent =
                'Sedang mengambil GPS...';

            jarakLokasi.textContent = '';


            navigator.geolocation.getCurrentPosition(

                async function(position) {

                    latitude =
                        position.coords.latitude;

                    longitude =
                        position.coords.longitude;


                    koordinat.textContent =
                        'Koordinat: ' +
                        latitude.toFixed(6) +
                        ', ' +
                        longitude.toFixed(6);


                    namaLokasi.textContent =
                        'Mengecek lokasi kantor...';

                    statusLokasi.textContent =
                        'Sedang memvalidasi lokasi...';


                    try {

                        const response =
                            await fetch(
                                '/presensi/cek-lokasi',
                                {
                                    method: 'POST',

                                    headers: {

                                        'Content-Type':
                                            'application/json',

                                        'Accept':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            '{{ csrf_token() }}'

                                    },

                                    body: JSON.stringify({

                                        latitude_user:
                                            latitude,

                                        longitude_user:
                                            longitude

                                    })

                                }
                            );


                        const result =
                            await response.json();


                        if (!response.ok) {

                            throw new Error(
                                result.message ||
                                'Lokasi tidak valid.'
                            );

                        }


                        lokasiValid = true;


                        namaLokasi.textContent =
                            result.lokasi.nama_lokasi;


                        statusLokasi.textContent =
                            '✓ Lokasi terdeteksi';


                        jarakLokasi.textContent =
                            result.lokasi.jarak_meter +
                            ' meter dari titik kantor';


                        btnPresensi.disabled =
                            false;


                        btnLokasi.disabled =
                            false;

                        btnLokasi.textContent =
                            'Perbarui Lokasi';


                        message.style.display =
                            'none';


                    } catch (error) {

                        lokasiValid = false;


                        namaLokasi.textContent =
                            'Lokasi tidak valid';


                        statusLokasi.textContent =
                            error.message;


                        jarakLokasi.textContent =
                            '';


                        btnPresensi.disabled =
                            true;


                        btnLokasi.disabled =
                            false;

                        btnLokasi.textContent =
                            'Coba Lagi';


                        tampilkanPesan(
                            error.message,
                            'error'
                        );

                    }

                },


                function(error) {

                    btnLokasi.disabled =
                        false;

                    btnLokasi.textContent =
                        'Ambil Lokasi Saya';

                    btnPresensi.disabled =
                        true;


                    let pesan =
                        'Gagal mendapatkan lokasi.';


                    if (error.code === 1) {

                        pesan =
                            'Izin lokasi ditolak oleh browser.';

                    }


                    if (error.code === 2) {

                        pesan =
                            'Lokasi tidak tersedia.';

                    }


                    if (error.code === 3) {

                        pesan =
                            'Pengambilan lokasi terlalu lama.';

                    }


                    tampilkanPesan(
                        pesan,
                        'error'
                    );

                },


                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }

            );

        }
    );


    /* =========================
       KIRIM PRESENSI
    ========================= */

    btnPresensi.addEventListener(
        'click',
        async function() {

            if (sudahPresensiHariIni) {

                tampilkanPesan(
                    'Anda sudah melakukan presensi hari ini.',
                    'error'
                );

                return;
            }


            if (
                tipeKerjaInput.value === 'WFO' &&
                !lokasiValid
            ) {

                tampilkanPesan(
                    'Silakan ambil lokasi terlebih dahulu.',
                    'error'
                );

                return;
            }


            message.style.display =
                'none';


            btnPresensi.disabled =
                true;

            btnPresensi.textContent =
                'Memproses...';


            const data = {

                tipe_kerja:
                    tipeKerjaInput.value,

                latitude_user:
                    latitude,

                longitude_user:
                    longitude,

                progress_hari_ini:
                    document.getElementById(
                        'progress_hari_ini'
                    ).value

            };


            try {

                const response =
                    await fetch(
                        '/presensi',
                        {
                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    '{{ csrf_token() }}'

                            },

                            body: JSON.stringify(data)

                        }
                    );


                const result =
                    await response.json();


                if (!response.ok) {

                    throw new Error(
                        result.message ||
                        'Presensi gagal.'
                    );

                }


                /* =========================
                   PRESENSI BERHASIL
                ========================= */

                sudahPresensiHariIni = true;


                attendanceStatus.textContent =
                    'Sudah melakukan presensi';


                if (
                    result.data &&
                    result.data.jam_presensi
                ) {

                    currentTime.textContent =
                        result.data.jam_presensi.substring(0, 5);

                }


                tampilkanPesan(
                    result.message,
                    'success'
                );


                kunciFormPresensi();


            } catch (error) {

                tampilkanPesan(
                    error.message,
                    'error'
                );


                btnPresensi.disabled =
                    false;

                btnPresensi.textContent =
                    'Kirim Presensi';

            }

        }
    );


    /* =========================
       CEK STATUS SAAT HALAMAN DIBUKA
    ========================= */

    cekStatusPresensiHariIni();

</script>

</body>

</html>