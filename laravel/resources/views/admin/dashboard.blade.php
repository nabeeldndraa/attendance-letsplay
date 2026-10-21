<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Let's Play Indonesia</title>

    <style>
        :root {
            --red: #e51d35;
            --red-dark: #a90f25;
            --red-deep: #780b1b;
            --red-light: #ff5265;
            --bg: #fff1f3;
            --text: #302027;
            --muted: #8b6870;
            --border: #f4cbd1;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background: var(--bg);
        }

        .layout {
            min-height: 100vh;
        }

        /* SIDEBAR MERAH */
        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 245px;
            padding: 28px 18px;
            background: linear-gradient(180deg, #e51d35 0%, #a90f25 100%);
            color: white;
            z-index: 10;
            box-shadow: 5px 0 24px #a90f251c;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px 30px;
            border-bottom: 1px solid #ffffff35;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: white;
            color: var(--red);
            font-size: 20px;
            font-weight: 800;
        }

        .brand-name {
            font-size: 15px;
            font-weight: 800;
            color: white;
        }

        .brand-caption {
            margin-top: 4px;
            color: #ffe0e5;
            font-size: 11px;
        }

        .nav-label {
            margin: 28px 10px 12px;
            color: #ffc4cc;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 5px 0;
            padding: 13px 12px;
            border-radius: 9px;
            color: #ffe5e9;
            font-size: 13px;
            text-decoration: none;
            transition: .2s ease;
        }

        .nav-link:hover {
            background: #ffffff1c;
            color: white;
        }

        .nav-link.active {
            background: white;
            color: var(--red-dark);
            font-weight: 800;
            box-shadow: 0 5px 15px #710b1b30;
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            font-size: 17px;
        }

        .sidebar-bottom {
            position: absolute;
            right: 18px;
            bottom: 24px;
            left: 18px;
            padding: 15px;
            border: 1px solid #ffffff30;
            border-radius: 12px;
            background: #ffffff12;
            color: #ffe9ec;
            font-size: 12px;
            line-height: 1.6;
        }

        .sidebar-bottom strong {
            display: block;
            margin-bottom: 3px;
            color: white;
        }

        /* HEADER */
        .main {
            margin-left: 245px;
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 19px 32px;
            background: linear-gradient(100deg, #c8152e, #ed3047);
            color: white;
            box-shadow: 0 4px 18px #a90f251c;
        }

        .breadcrumb {
            color: #ffe1e6;
            font-size: 12px;
        }

        .breadcrumb strong {
            color: white;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 12px;
            font-weight: 700;
        }

        .avatar {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border: 2px solid #ffffff70;
            border-radius: 50%;
            background: white;
            color: var(--red);
            font-weight: 800;
        }

        /* KONTEN */
        .content {
            max-width: 1600px;
            margin: auto;
            padding: 30px 32px;
        }

        .page-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0;
            color: var(--red-deep);
            font-size: 28px;
            letter-spacing: -.6px;
        }

        .subtitle {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .date-label {
            padding: 11px 14px;
            border: 1px solid #f2bbc4;
            border-radius: 9px;
            background: #fff;
            color: var(--red-dark);
            font-size: 12px;
            white-space: nowrap;
        }

        .section-title {
            margin: 28px 0 15px;
            color: var(--red-deep);
            font-size: 15px;
            font-weight: 800;
        }

        /* KARTU STATISTIK */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .stat-card {
            position: relative;
            min-width: 0;
            overflow: hidden;
            padding: 20px;
            border: 1px solid #ffffff25;
            border-radius: 13px;
            background: linear-gradient(135deg, #ed3047, #bc102b);
            color: white;
            box-shadow: 0 7px 18px #b20f2820;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:nth-child(2),
        .stat-card:nth-child(5),
        .stat-card:nth-child(7) {
            background: linear-gradient(135deg, #b70f29, #850b20);
        }

        .stat-card:nth-child(3),
        .stat-card:nth-child(6) {
            background: linear-gradient(135deg, #fa5366, #d91b35);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px #b20f2830;
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .stat-label {
            color: #ffe6e9;
            font-size: 12px;
            line-height: 1.5;
        }

        .stat-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 37px;
            height: 37px;
            border-radius: 10px;
            background: #ffffff24;
            color: white;
            font-size: 18px;
        }

        .stat-value {
            margin-top: 16px;
            color: white;
            font-size: 32px;
            font-weight: 800;
        }

        .stat-note {
            margin-top: 7px;
            color: #ffe1e6;
            font-size: 11px;
        }

        /* PANEL TABEL */
        .columns {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(260px, 1fr);
            gap: 18px;
            margin-top: 24px;
        }

        .panel {
            min-width: 0;
            overflow: hidden;
            border: 1px solid #f1c2ca;
            border-radius: 13px;
            background: white;
            box-shadow: 0 5px 20px #9d102016;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 20px;
            border-bottom: 1px solid #f5dce0;
            background: #fff5f6;
        }

        .panel-title {
            margin: 0;
            color: var(--red-deep);
            font-size: 14px;
        }

        .panel-caption {
            margin-top: 6px;
            color: var(--muted);
            font-size: 11px;
        }

        .count {
            padding: 6px 9px;
            border-radius: 7px;
            background: #ffe1e5;
            color: var(--red-dark);
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 13px 16px;
            background: #fff0f2;
            color: #9e3041;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        td {
            padding: 15px 16px;
            border-top: 1px solid #f9e8eb;
            font-size: 11px;
            white-space: nowrap;
        }

        tbody tr:hover {
            background: #fff8f9;
        }

        .person {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .person-avatar {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border-radius: 9px;
            background: #ffe3e7;
            color: var(--red-dark);
            font-size: 11px;
            font-weight: 800;
        }

        .person-name {
            font-size: 11px;
            font-weight: 700;
        }

        .person-role {
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
        }

        /* LABEL STATUS */
        .badge {
            display: inline-block;
            padding: 6px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-red {
            background: #ffe1e5;
            color: #b10e27;
        }

        .badge-green {
            background: #e9f8ef;
            color: #21834b;
        }

        .badge-gray {
            background: #f0f1f4;
            color: #6c7280;
        }

        /* DAFTAR PESERTA */
        .participant-list {
            padding: 4px 20px;
        }

        .participant {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 0;
            border-bottom: 1px solid #f5e4e7;
        }

        .participant:last-child {
            border-bottom: none;
        }

        .participant-details {
            flex: 1;
            min-width: 0;
        }

        .participant-name {
            overflow-wrap: anywhere;
            font-size: 12px;
            font-weight: 700;
        }

        .participant-school {
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.5;
        }

        .participant-status {
            margin-top: 7px;
        }

        .footer {
            padding: 25px 0 5px;
            color: #a16e78;
            font-size: 10px;
            text-align: center;
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .columns {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            .sidebar {
                position: static;
                width: 100%;
                padding: 15px 18px;
                border-right: 0;
                border-bottom: 1px solid var(--border);
            }

            .brand {
                padding-bottom: 14px;
            }

            .sidebar .nav-label,
            .sidebar-bottom {
                display: none;
            }

            .sidebar nav {
                display: flex;
                gap: 6px;
                margin-top: 12px;
                overflow-x: auto;
            }

            .nav-link {
                flex-shrink: 0;
                margin: 0;
                padding: 10px;
                font-size: 11px;
            }

            .nav-icon {
                width: auto;
            }

            .main {
                margin-left: 0;
            }

            .topbar {
                padding: 15px 18px;
            }

            .content {
                padding: 23px 16px;
            }

            .page-heading {
                flex-direction: column;
            }

            h1 {
                font-size: 23px;
            }

            .date-label {
                white-space: normal;
            }

            .stats {
                gap: 10px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-label {
                font-size: 11px;
            }

            .stat-value {
                font-size: 25px;
            }

            .stat-icon {
                width: 31px;
                height: 31px;
                font-size: 15px;
            }

            .panel-header {
                padding: 16px;
            }

            .participant-list {
                padding: 4px 16px;
            }
        }

        @media (max-width: 370px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .admin-label {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">LP</div>
                <div>
                    <div class="brand-name">LET'S PLAY</div>
                    <div class="brand-caption">Internship Management</div>
                </div>
            </div>

            <div class="nav-label">MENU UTAMA</div>

            <nav>
                <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                    <span class="nav-icon">▦</span> Dashboard
                </a>
                <a href="{{ route('admin.data-presensi') }}" class="nav-link">
                    <span class="nav-icon">▤</span>
                    <span class="nav-text">Data Presensi</span>
                </a>
                <a class="nav-link" href="#peserta">
                    <span class="nav-icon">♙</span> Data Peserta
                </a>
            </nav>

            <div class="sidebar-bottom">
                <strong>Let's Play Indonesia</strong>
                Sistem monitoring presensi peserta magang.
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div class="breadcrumb">
                    Admin Panel / <strong>Dashboard</strong>
                </div>

                <div class="admin-info">
                    <div class="avatar">A</div>
                    <span class="admin-label">Administrator</span>
                </div>
            </header>

            <section class="content">
                <div class="page-heading">
                    <div>
                        <h1>Dashboard Admin</h1>
                        <p class="subtitle">
                            Pantau aktivitas dan presensi peserta magang
                            Let's Play Indonesia.
                        </p>
                    </div>

                    <div class="date-label">
                        ◷
                        {{ \Carbon\Carbon::parse($hariIni)->locale('id')->translatedFormat('l, d F Y') }}
                    </div>
                </div>

                <h2 class="section-title">Ringkasan Hari Ini</h2>

                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Peserta Aktif</div>
                            <div class="stat-icon">♙</div>
                        </div>
                        <div class="stat-value">
                            {{ $statistik['total_peserta_aktif'] }}
                        </div>
                        <div class="stat-note">Peserta dengan akun aktif</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Presensi Hari Ini</div>
                            <div class="stat-icon">◷</div>
                        </div>
                        <div class="stat-value">
                            {{ $statistik['total_presensi_hari_ini'] }}
                        </div>
                        <div class="stat-note">Total presensi tercatat</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Tepat Waktu</div>
                            <div class="stat-icon">✓</div>
                        </div>
                        <div class="stat-value">
                            {{ $statistik['tepat_waktu'] }}
                        </div>
                        <div class="stat-note">Presensi tepat waktu</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Terlambat</div>
                            <div class="stat-icon">!</div>
                        </div>
                        <div class="stat-value">
                            {{ $statistik['terlambat'] }}
                        </div>
                        <div class="stat-note">Presensi berstatus terlambat</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Menunggu Konfirmasi</div>
                            <div class="stat-icon">⌛</div>
                        </div>
                        <div class="stat-value">
                            {{ $statistik['menunggu_konfirmasi'] }}
                        </div>
                        <div class="stat-note">Pengajuan konfirmasi admin</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Presensi WFO</div>
                            <div class="stat-icon">⌂</div>
                        </div>
                        <div class="stat-value">
                            {{ $statistik['wfo'] }}
                        </div>
                        <div class="stat-note">Work From Office</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <div class="stat-label">Presensi WFH</div>
                            <div class="stat-icon">⌘</div>
                        </div>
                        <div class="stat-value">
                            {{ $statistik['wfh'] }}
                        </div>
                        <div class="stat-note">Work From Home</div>
                    </div>
                </div>

                <div class="columns">
                    <section class="panel" id="presensi">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Presensi Terbaru</h2>
                                <div class="panel-caption">
                                    Riwayat presensi seluruh peserta
                                </div>
                            </div>
                            <span class="count">
                                {{ $presensiTerbaru->count() }} data
                            </span>
                        </div>

                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>PESERTA</th>
                                        <th>TANGGAL</th>
                                        <th>JAM</th>
                                        <th>TIPE</th>
                                        <th>STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($presensiTerbaru as $presensi)
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <div class="person-avatar">
                                                    {{ strtoupper(substr($presensi->user?->nama ?? 'P', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="person-name">
                                                        {{ $presensi->user?->nama ?? 'Peserta' }}
                                                    </div>
                                                    <div class="person-role">
                                                        {{ $presensi->user?->bidang_magang ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($presensi->tanggal)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            {{ $presensi->jam_presensi ?? '-' }}
                                        </td>
                                        <td>
                                            {{ $presensi->tipe_kerja }}
                                        </td>
                                        <td>
                                            @if ($presensi->status_presensi === 'Tepat Waktu')
                                            <span class="badge badge-green">
                                                Tepat Waktu
                                            </span>
                                            @else
                                            <span class="badge badge-red">
                                                {{ $presensi->status_presensi }}
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center;padding:28px">
                                            Belum ada data presensi.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="panel" id="peserta">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Peserta Magang</h2>
                                <div class="panel-caption">
                                    Daftar peserta terdaftar
                                </div>
                            </div>
                            <span class="count">
                                {{ $daftarPeserta->count() }} peserta
                            </span>
                        </div>

                        <div class="participant-list">
                            @forelse ($daftarPeserta as $peserta)
                            <div class="participant">
                                <div class="person-avatar">
                                    {{ strtoupper(substr($peserta->nama, 0, 1)) }}
                                </div>

                                <div class="participant-details">
                                    <div class="participant-name">
                                        {{ $peserta->nama }}
                                    </div>
                                    <div class="participant-school">
                                        {{ $peserta->asal_instansi ?? '-' }}
                                        <br>
                                        {{ $peserta->bidang_magang ?? '-' }}
                                    </div>
                                    <div class="participant-status">
                                        @if ($peserta->status_akun === 'aktif')
                                        <span class="badge badge-green">
                                            Aktif
                                        </span>
                                        @else
                                        <span class="badge badge-gray">
                                            {{ ucfirst($peserta->status_akun) }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p class="subtitle">
                                Belum ada peserta magang terdaftar.
                            </p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="footer">
                    Let's Play Indonesia · Internship Management System
                </div>
            </section>
        </main>
    </div>
</body>

</html>