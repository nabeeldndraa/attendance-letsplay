<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data User - Let's Play Indonesia</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f6fa;
            color: #242424;
        }

        .topbar {
            background: #b91c2c;
            color: white;
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            font-size: 19px;
            font-weight: 700;
        }

        .brand small {
            display: block;
            font-size: 12px;
            font-weight: 400;
            margin-top: 5px;
            opacity: .85;
        }

        .topbar a {
            color: white;
            text-decoration: none;
            font-size: 13px;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 24px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .page-heading h1 {
            font-size: 25px;
            margin-bottom: 7px;
        }

        .page-heading p {
            color: #777;
            font-size: 14px;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 11px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: #b91c2c;
            color: white;
        }

        .btn-primary:hover {
            background: #991b1b;
        }

        .btn-light {
            background: #f1f1f1;
            color: #333;
        }

        .btn-edit {
            background: #fff0f0;
            color: #b91c2c;
        }

        .btn-status {
            background: #f1f1f1;
            color: #333;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #eee;
        }

        .stat-card span {
            display: block;
            font-size: 13px;
            color: #777;
            margin-bottom: 10px;
        }

        .stat-card strong {
            font-size: 27px;
        }

        .panel {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            overflow: hidden;
        }

        .panel-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid #eee;
        }

        .panel-header h2 {
            font-size: 17px;
        }

        .search-form {
            display: flex;
            gap: 8px;
        }

        .search-form input {
            width: 260px;
            max-width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
        }

        .search-form input:focus,
        .field input:focus,
        .field select:focus {
            border-color: #b91c2c;
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

        th, td {
            padding: 15px 18px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }

        th {
            background: #fafafa;
            color: #666;
            font-size: 12px;
            font-weight: 700;
        }

        tbody tr:hover {
            background: #fffafa;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .muted {
            color: #888;
            font-size: 12px;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-aktif {
            background: #e6f7ed;
            color: #187747;
        }

        .status-nonaktif {
            background: #feecec;
            color: #b91c2c;
        }

        .actions {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .actions form {
            display: inline;
        }

        .alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #e6f7ed;
            color: #187747;
        }

        .alert-error {
            background: #feecec;
            color: #b91c2c;
        }

        .validation-errors {
            margin: 8px 0 16px;
            padding-left: 20px;
        }

        .pagination {
            padding: 20px;
        }

        .pagination nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pagination nav p {
            color: #777;
            font-size: 13px;
        }

        .pagination nav div:last-child {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span[aria-current="page"] span,
        .pagination span[aria-disabled="true"] span {
            display: inline-block;
            padding: 7px 11px;
            border: 1px solid #eee;
            border-radius: 6px;
            text-decoration: none;
            color: #444;
            font-size: 13px;
        }

        .pagination span[aria-current="page"] span {
            background: #b91c2c;
            border-color: #b91c2c;
            color: white;
        }

        .empty {
            padding: 40px 20px;
            text-align: center;
            color: #777;
        }

        @media (max-width: 760px) {
            .topbar {
                padding: 18px;
            }

            .container {
                padding: 0 14px;
                margin: 22px auto;
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-card strong {
                font-size: 23px;
            }

            .panel-header {
                align-items: stretch;
                flex-direction: column;
                padding: 16px;
            }

            .search-form input {
                width: 100%;
                min-width: 0;
            }

            .search-form {
                width: 100%;
            }

            .search-form button {
                flex-shrink: 0;
            }
        }
    </style>
</head>
<body>

<header class="topbar">
    <div class="brand">
        Let's Play Indonesia
        <small>Admin Panel · Sistem Presensi Intern</small>
    </div>

    <a href="{{ route('admin.dashboard') }}">← Kembali ke Dashboard</a>
</header>

<main class="container">

    <div class="page-heading">
        <div>
            <h1>Data User</h1>
            <p>Kelola akun dan informasi peserta magang.</p>
        </div>

        <a href="{{ route('admin.data-user.create') }}"
           class="btn btn-primary">
            + Tambah User
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Terjadi kesalahan:</strong>
            <ul class="validation-errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="stats">
        <div class="stat-card">
            <span>Total peserta terdaftar</span>
            <strong>{{ $users->total() }}</strong>
        </div>

        <div class="stat-card">
            <span>Peserta aktif</span>
            <strong>
                {{ \App\Models\User::where('role', 'user')->where('status_akun', 'aktif')->count() }}
            </strong>
        </div>

        <div class="stat-card">
            <span>Peserta nonaktif</span>
            <strong>
                {{ \App\Models\User::where('role', 'user')->where('status_akun', 'nonaktif')->count() }}
            </strong>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Daftar Peserta</h2>

            <form action="{{ route('admin.data-user') }}"
                  method="GET"
                  class="search-form">

                <input
                    type="text"
                    name="search"
                    value="{{ $keyword }}"
                    placeholder="Cari nama, email, instansi..."
                >

                <button type="submit" class="btn btn-primary">
                    Cari
                </button>

                @if ($keyword)
                    <a href="{{ route('admin.data-user') }}"
                       class="btn btn-light">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Peserta</th>
                        <th>Instansi</th>
                        <th>Bidang Magang</th>
                        <th>Periode Magang</th>
                        <th>Status Akun</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                {{ $users->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="user-name">{{ $user->nama }}</div>
                                <div class="muted">{{ $user->email }}</div>
                                <div class="muted">{{ $user->no_wa }}</div>
                            </td>

                            <td>{{ $user->asal_instansi }}</td>

                            <td>{{ $user->bidang_magang }}</td>

                            <td>
                                {{ $user->tanggal_mulai?->format('d M Y') ?? '-' }}
                                <div class="muted">sampai</div>
                                {{ $user->tanggal_selesai?->format('d M Y') ?? '-' }}
                            </td>

                            <td>
                                <span class="status {{ $user->status_akun === 'aktif' ? 'status-aktif' : 'status-nonaktif' }}">
                                    {{ ucfirst($user->status_akun) }}
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        href="{{ route('admin.data-user.edit', $user->id) }}"
                                        class="btn btn-edit">
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.data-user.status', $user->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin mengubah status akun peserta ini?')">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn btn-status">
                                            {{ $user->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">
                                @if ($keyword)
                                    Tidak ditemukan peserta yang cocok dengan pencarian.
                                @else
                                    Belum ada data peserta magang.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $users->links() }}
        </div>
    </section>
</main>

</body>
</html>
