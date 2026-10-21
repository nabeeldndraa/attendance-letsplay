<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Presensi | Let's Play Indonesia</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --red: #c8102e;
            --red-dark: #97001d;
            --red-deep: #760018;
            --red-light: #fff0f2;
            --red-border: #f3c5ce;
            --border: #ececef;
            --muted: #85858f;
            --green: #21834b;
            --white: #ffffff;
            --background: #f8f8fa;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: var(--background);
            color: #252525;
            font-size: 13px;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 245px;
            background: linear-gradient(180deg, #c8102e 0%, #97001d 65%, #760018 100%);
            color: white;
            padding: 25px 16px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 0 10px 30px;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            color: var(--red);
            background: white;
            border-radius: 10px;
            font-weight: 800;
            font-size: 17px;
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 13px;
            font-weight: 800;
            color: white;
        }

        .brand-subtitle {
            color: #ffdce3;
            font-size: 10px;
            margin-top: 4px;
        }

        .nav-label {
            padding: 12px 11px;
            color: #ffdce3;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 5px;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            transition: background .2s, transform .2s;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, .12);
            color: white;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, .2);
            color: white;
            font-weight: 700;
            box-shadow: inset 3px 0 0 white;
        }

        .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding: 15px 10px;
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 9px;
            color: #ffe4e9;
            font-size: 11px;
            line-height: 1.7;
        }

        .sidebar-bottom strong {
            color: white;
        }

        /* MAIN & TOPBAR */
        .main {
            min-width: 0;
            flex: 1;
        }

        .topbar {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 32px;
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .breadcrumb {
            color: var(--muted);
            font-size: 11px;
        }

        .breadcrumb strong {
            color: var(--red);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--red-light);
            color: var(--red);
            border: 1px solid var(--red-border);
            font-weight: 800;
            flex-shrink: 0;
        }

        .admin-name {
            font-size: 12px;
            font-weight: 700;
        }

        .admin-role {
            color: var(--muted);
            font-size: 10px;
            margin-top: 4px;
        }

        /* CONTENT */
        .content {
            padding: 30px 32px;
            max-width: 1600px;
            margin: auto;
        }

        .page-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 25px;
        }

        .page-heading h1 {
            color: var(--red-dark);
            font-size: 25px;
            font-weight: 800;
            margin-bottom: 9px;
        }

        .page-heading p {
            color: #6c6c77;
            line-height: 1.6;
            font-size: 12px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 15px;
            background: white;
            border: 1px solid var(--red);
            border-radius: 8px;
            color: var(--red);
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
            transition: .2s;
        }

        .back-button:hover {
            background: var(--red);
            color: white;
        }

        /* SUMMARY CARDS */
        .summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .summary-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #fff 60%, #fff1f3);
            border: 1px solid var(--red-border);
            border-radius: 12px;
            padding: 20px;
        }

        .summary-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--red);
        }

        .summary-label {
            color: #6f6f79;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .summary-value {
            color: var(--red);
            font-size: 27px;
            font-weight: 800;
        }

        .summary-note {
            margin-top: 7px;
            color: var(--muted);
            font-size: 10px;
        }

        /* TABLE PANEL */
        .panel {
            background: white;
            border: 1px solid var(--red-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(110, 0, 25, .04);
        }

        .panel-header {
            padding: 22px;
            background: linear-gradient(110deg, #a90024 0%, #c8102e 55%, #e31837 100%);
            color: white;
            border-bottom: 1px solid var(--red-dark);
        }

        .panel-header h2 {
            font-size: 16px;
            margin-bottom: 7px;
            font-weight: 800;
        }

        .panel-header p {
            color: #ffe6eb;
            font-size: 11px;
            line-height: 1.6;
        }

        /* FILTERS */
        .filters {
            display: grid;
            grid-template-columns: minmax(180px, 1.5fr) repeat(4, minmax(130px, 1fr));
            gap: 12px;
            padding: 20px 22px;
            border-bottom: 1px solid var(--border);
            background: #fff;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }

        .field label {
            color: #5f5f69;
            font-size: 10px;
            font-weight: 700;
        }

        .field input,
        .field select {
            width: 100%;
            min-width: 0;
            height: 39px;
            border: 1px solid #dedee4;
            border-radius: 7px;
            padding: 0 10px;
            background: white;
            color: #333;
            font-family: inherit;
            font-size: 11px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .field input:focus,
        .field select:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(200, 16, 46, .08);
        }

        .field input::placeholder {
            color: #aaaab2;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: flex-end;
            grid-column: 1 / -1;
            flex-wrap: wrap;
        }

        .button {
            min-height: 38px;
            padding: 0 16px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 7px;
            border: 1px solid transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            transition: .2s;
        }

        .button-primary {
            background: linear-gradient(135deg, #e31837, #b50025);
            color: white;
            box-shadow: 0 3px 8px rgba(200, 16, 46, .14);
        }

        .button-primary:hover {
            background: var(--red-dark);
            box-shadow: 0 4px 12px rgba(200, 16, 46, .2);
        }

        .button-secondary {
            background: white;
            border-color: var(--red-border);
            color: var(--red);
        }

        .button-secondary:hover {
            background: var(--red-light);
            border-color: var(--red);
        }

        /* TABLE */
        .table-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 22px;
            background: #fff4f5;
            color: var(--red-dark);
            font-size: 11px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        thead {
            background: #fff0f2;
        }

        th {
            padding: 14px 17px;
            text-align: left;
            color: #8e1930;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .3px;
            border-top: 1px solid #f7d6dc;
            border-bottom: 1px solid #f0c4cc;
        }

        td {
            padding: 16px 17px;
            border-bottom: 1px solid #f0f0f2;
            vertical-align: middle;
            font-size: 11px;
        }

        tbody tr {
            transition: background .15s;
        }

        tbody tr:hover {
            background: #fff8f9;
        }

        .person {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .person-avatar {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            background: linear-gradient(145deg, #e31837, #a90024);
            color: white;
            border-radius: 9px;
            font-weight: 800;
        }

        .person-name {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .person-email {
            color: var(--muted);
            font-size: 10px;
        }

        /* STATUS BADGES */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-green {
            color: #187342;
            background: #e2f7eb;
        }

        .badge-red {
            color: #b10e27;
            background: #ffe5e9;
        }

        .badge-orange {
            color: #965600;
            background: #fff0d6;
        }

        .badge-gray {
            color: #62626d;
            background: #f0f0f3;
        }

        /* LOCATION */
        .location {
            max-width: 190px;
            white-space: normal;
            line-height: 1.5;
        }

        .location-name {
            font-weight: 700;
            margin-bottom: 4px;
            color: #3b3b45;
        }

        .location-coord {
            color: var(--muted);
            font-size: 10px;
            overflow-wrap: anywhere;
        }

        .detail-link {
            color: var(--red);
            text-decoration: none;
            font-weight: 700;
        }

        .detail-link:hover {
            color: var(--red-dark);
            text-decoration: underline;
        }

        /* EMPTY STATE */
        .empty-state {
            padding: 45px 20px;
            text-align: center;
            color: var(--muted);
            white-space: normal;
            line-height: 1.7;
        }

        .empty-state strong {
            color: var(--red-dark);
        }

        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            padding: 18px 22px;
        }

        .pagination-info {
            color: var(--muted);
            font-size: 11px;
        }

        .pagination-links {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .pagination-links a,
        .pagination-links span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 9px;
            border: 1px solid #f0c6ce;
            border-radius: 6px;
            text-decoration: none;
            color: var(--red);
            background: white;
            font-size: 11px;
            transition: .2s;
        }

        .pagination-links a:hover {
            background: var(--red-light);
            border-color: var(--red);
        }

        .pagination-links .active {
            background: var(--red);
            border-color: var(--red);
            color: white;
        }

        .pagination-links .disabled {
            color: #bbb;
            border-color: #eee;
            background: #fafafa;
        }

        /* FOOTER */
        .footer {
            padding: 22px 0 5px;
            text-align: center;
            color: #a0a0aa;
            font-size: 10px;
        }

        /* TABLET */
        @media (max-width: 1150px) {
            .filters {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .content {
                padding: 25px 20px;
            }
        }

        @media (max-width: 850px) {
            .sidebar {
                width: 75px;
                padding: 20px 10px;
            }

            .brand {
                justify-content: center;
                padding: 0 0 25px;
            }

            .brand-text,
            .nav-label,
            .nav-text {
                display: none;
            }

            .sidebar-bottom {
                display: none;
            }

            .nav-link {
                justify-content: center;
                padding: 13px 5px;
            }

            .nav-icon {
                font-size: 18px;
            }

            .content {
                padding: 24px 18px;
            }

            .topbar {
                padding: 14px 18px;
            }

            .summary {
                gap: 10px;
            }

            .summary-card {
                padding: 16px;
            }
        }

        /* MOBILE */
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }

            .topbar {
                min-height: 62px;
                padding: 12px;
            }

            .content {
                padding: 20px 12px;
            }

            .page-heading {
                flex-direction: column;
            }

            .page-heading h1 {
                font-size: 22px;
            }

            .page-heading p {
                font-size: 12px;
            }

            .back-button {
                width: 100%;
            }

            .summary {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .summary-card {
                padding: 16px 18px;
            }

            .summary-value {
                font-size: 23px;
            }

            .filters {
                grid-template-columns: 1fr 1fr;
                padding: 16px;
                gap: 12px;
            }

            .field-search {
                grid-column: 1 / -1;
            }

            .filter-actions {
                grid-column: 1 / -1;
            }

            .filter-actions .button {
                flex: 1;
            }

            .panel-header {
                padding: 18px 16px;
            }

            .table-meta {
                padding: 14px 16px;
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-profile .profile-text {
                display: none;
            }

            .pagination {
                padding: 16px;
            }

            .pagination-links {
                width: 100%;
            }

            .pagination-links a,
            .pagination-links span {
                min-width: 30px;
            }
        }

        @media (max-width: 380px) {
            .filters {
                grid-template-columns: 1fr;
            }

            .field-search {
                grid-column: auto;
            }

            .filter-actions {
                flex-direction: column;
            }

            .filter-actions .button {
                width: 100%;
            }

            .page-heading h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="layout">

        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">LP</div>
                <div class="brand-text">
                    <div class="brand-name">LET'S PLAY</div>
                    <div class="brand-subtitle">Intern Attendance System</div>
                </div>
            </div>

            <div class="nav-label">MENU UTAMA</div>

            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                <span class="nav-icon">⌂</span>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="{{ route('admin.data-presensi') }}" class="nav-link active">
                <span class="nav-icon">▤</span>
                <span class="nav-text">Data Presensi</span>
            </a>

            <div class="nav-label">MANAJEMEN</div>

            <a href="{{ route('admin.dashboard') }}#peserta" class="nav-link">
                <span class="nav-icon">♙</span>
                <span class="nav-text">Data Peserta</span>
            </a>

            <div class="sidebar-bottom">
                <strong>Let's Play Indonesia</strong><br>
                Sistem Presensi Intern
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div class="breadcrumb">
                    Admin / <strong>Data Presensi</strong>
                </div>

                <div class="admin-profile">
                    <div class="avatar">
                        {{ strtoupper(substr(auth()->user()->nama ?? 'A', 0, 1)) }}
                    </div>
                    <div class="profile-text">
                        <div class="admin-name">
                            {{ auth()->user()->nama ?? 'Administrator' }}
                        </div>
                        <div class="admin-role">Administrator</div>
                    </div>
                </div>
            </header>

            <section class="content">
                <div class="page-heading">
                    <div>
                        <h1>Data Presensi</h1>
                        <p>
                            Kelola dan pantau seluruh riwayat kehadiran peserta magang
                            Let's Play Indonesia.
                        </p>
                    </div>

                    <a href="{{ route('admin.dashboard') }}" class="back-button">
                        ← Kembali ke Dashboard
                    </a>
                </div>

                <div class="summary">
                    <div class="summary-card">
                        <div class="summary-label">TOTAL DATA DITEMUKAN</div>
                        <div class="summary-value">
                            {{ number_format($presensi->total(), 0, ',', '.') }}
                        </div>
                        <div class="summary-note">
                            Sesuai pencarian dan filter
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="summary-label">DATA PER HALAMAN</div>
                        <div class="summary-value">
                            {{ $presensi->count() }}
                        </div>
                        <div class="summary-note">
                            Maksimal 15 catatan presensi
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="summary-label">HALAMAN AKTIF</div>
                        <div class="summary-value">
                            {{ $presensi->currentPage() }}
                        </div>
                        <div class="summary-note">
                            Dari {{ $presensi->lastPage() }} halaman
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2>Riwayat Presensi Peserta</h2>
                        <p>
                            Gunakan pencarian dan filter untuk menemukan data yang diperlukan.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('admin.data-presensi') }}">
                        <div class="filters">
                            <div class="field field-search">
                                <label for="search">NAMA PESERTA</label>
                                <input type="text" id="search" name="search" placeholder="Cari nama peserta..." value="{{ $filter['search'] ?? '' }}">
                            </div>

                            <div class="field">
                                <label for="tanggal_mulai">DARI TANGGAL</label>
                                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ $filter['tanggal_mulai'] ?? '' }}">
                            </div>

                            <div class="field">
                                <label for="tanggal_selesai">SAMPAI TANGGAL</label>
                                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ $filter['tanggal_selesai'] ?? '' }}">
                            </div>

                            <div class="field">
                                <label for="status_presensi">STATUS PRESENSI</label>
                                <select id="status_presensi" name="status_presensi">
                                    <option value="">Semua Status</option>
                                    <option value="Tepat Waktu" @selected(($filter['status_presensi'] ?? '' )==='Tepat Waktu' )>
                                        Tepat Waktu
                                    </option>
                                    <option value="Terlambat" @selected(($filter['status_presensi'] ?? '' )==='Terlambat' )>
                                        Terlambat
                                    </option>
                                    <option value="Alpha" @selected(($filter['status_presensi'] ?? '' )==='Alpha' )>
                                        Alpha
                                    </option>
                                    <option value="Menunggu Konfirmasi" @selected(($filter['status_presensi'] ?? '' )==='Menunggu Konfirmasi' )>
                                        Menunggu Konfirmasi
                                    </option>
                                </select>
                            </div>

                            <div class="field">
                                <label for="tipe_kerja">TIPE KERJA</label>
                                <select id="tipe_kerja" name="tipe_kerja">
                                    <option value="">Semua Tipe</option>
                                    <option value="WFO" @selected(($filter['tipe_kerja'] ?? '' )==='WFO' )>
                                        WFO
                                    </option>
                                    <option value="WFH" @selected(($filter['tipe_kerja'] ?? '' )==='WFH' )>
                                        WFH
                                    </option>
                                </select>
                            </div>

                            <div class="filter-actions">
                                <button type="submit" class="button button-primary">
                                    Terapkan Filter
                                </button>

                                <a href="{{ route('admin.data-presensi') }}" class="button button-secondary">
                                    Reset Filter
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-meta">
                        <span>Daftar riwayat kehadiran</span>
                        <span>
                            {{ $presensi->firstItem() ?? 0 }}–{{ $presensi->lastItem() ?? 0 }}
                            dari {{ $presensi->total() }} data
                        </span>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>PESERTA</th>
                                    <th>TANGGAL</th>
                                    <th>JAM</th>
                                    <th>TIPE KERJA</th>
                                    <th>LOKASI</th>
                                    <th>STATUS</th>
                                    <th>KONFIRMASI</th>
                                    <th>POIN</th>
                                    <th>DETAIL</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($presensi as $item)
                                <tr>
                                    <td>
                                        <div class="person">
                                            <div class="person-avatar">
                                                {{ strtoupper(substr($item->user?->nama ?? 'P', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="person-name">
                                                    {{ $item->user?->nama ?? 'Peserta tidak ditemukan' }}
                                                </div>
                                                <div class="person-email">
                                                    {{ $item->user?->email ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                                    </td>

                                    <td>{{ $item->jam_presensi ?? '-' }}</td>

                                    <td>
                                        <span class="badge badge-gray">
                                            {{ $item->tipe_kerja ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="location">
                                            <div class="location-name">
                                                @if ($item->tipe_kerja === 'WFH')
                                                Lokasi WFH
                                                @else
                                                {{ $item->lokasiKantor?->nama_lokasi ?? 'Lokasi tidak diketahui' }}
                                                @endif
                                            </div>

                                            @if ($item->latitude_user !== null && $item->longitude_user !== null)
                                            <div class="location-coord">
                                                {{ $item->latitude_user }},
                                                {{ $item->longitude_user }}
                                            </div>
                                            @else
                                            <div class="location-coord">
                                                Koordinat tidak tersedia
                                            </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        @if ($item->status_presensi === 'Tepat Waktu')
                                        <span class="badge badge-green">
                                            Tepat Waktu
                                        </span>
                                        @elseif ($item->status_presensi === 'Terlambat')
                                        <span class="badge badge-red">
                                            Terlambat
                                        </span>
                                        @elseif ($item->status_presensi === 'Menunggu Konfirmasi')
                                        <span class="badge badge-orange">
                                            Menunggu Konfirmasi
                                        </span>
                                        @else
                                        <span class="badge badge-gray">
                                            {{ $item->status_presensi ?? '-' }}
                                        </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($item->status_konfirmasi === 'Diajukan')
                                        <span class="badge badge-orange">Diajukan</span>
                                        @elseif ($item->status_konfirmasi === 'Disetujui')
                                        <span class="badge badge-green">Disetujui</span>
                                        @elseif ($item->status_konfirmasi === 'Ditolak')
                                        <span class="badge badge-red">Ditolak</span>
                                        @else
                                        <span class="badge badge-gray">
                                            {{ $item->status_konfirmasi ?: '-' }}
                                        </span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>
                                            {{ number_format($item->point_didapat ?? 0, 0, ',', '.') }}
                                        </strong>
                                    </td>

                                    <td>
                                        <a class="detail-link" href="https://www.google.com/maps?q={{ $item->latitude_user }},{{ $item->longitude_user }}" target="_blank" rel="noopener noreferrer">
                                            Lihat Lokasi ↗
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <strong>Data presensi tidak ditemukan.</strong><br>
                                            Coba ubah kata kunci atau reset filter.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        <div class="pagination-info">
                            Menampilkan {{ $presensi->count() }} data pada halaman ini
                        </div>

                        <div class="pagination-links">
                            @if ($presensi->onFirstPage())
                            <span class="disabled">←</span>
                            @else
                            <a href="{{ $presensi->previousPageUrl() }}">←</a>
                            @endif

                            @foreach ($presensi->getUrlRange(
                            max(1, $presensi->currentPage() - 2),
                            min($presensi->lastPage(), $presensi->currentPage() + 2)
                            ) as $page => $url)
                            @if ($page == $presensi->currentPage())
                            <span class="active">{{ $page }}</span>
                            @else
                            <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                            @endforeach

                            @if ($presensi->hasMorePages())
                            <a href="{{ $presensi->nextPageUrl() }}">→</a>
                            @else
                            <span class="disabled">→</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="footer">
                    © {{ date('Y') }} Let's Play Indonesia · Intern Attendance System
                </div>
            </section>
        </main>
    </div>
</body>

</html>