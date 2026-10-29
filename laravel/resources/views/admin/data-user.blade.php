@extends('layouts.admin')
@section('title', 'Data User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/data-user.css') }}">
@endpush

@section('content')
@php
    // ---- Filter (dikirim lewat query string: instansi, bidang, periode) ----
    $instansiDipilih = (string) request('instansi');
    $bidangDipilih   = (string) request('bidang');
    $periodeReq      = (string) request('periode');
    $periodeValid    = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodeReq) ? $periodeReq : '';
    $periodeLabel    = $periodeValid
        ? \Carbon\Carbon::createFromFormat('!Y-m', $periodeValid)->locale('id')->translatedFormat('F Y')
        : null;

    $filterAktif = $keyword || $instansiDipilih !== '' || $bidangDipilih !== '' || $periodeValid !== '';

    /*
     * Pilihan dropdown diambil dari data peserta. Kalau nanti controller mengirim
     * $daftarInstansi / $daftarBidang, otomatis dipakai menggantikan query ini.
     */
    $opsiInstansi = collect($daftarInstansi ?? \App\Models\User::where('role', 'user')
        ->whereNotNull('asal_instansi')->where('asal_instansi', '!=', '')
        ->distinct()->orderBy('asal_instansi')->pluck('asal_instansi'));

    $opsiBidang = collect($daftarBidang ?? \App\Models\User::where('role', 'user')
        ->whereNotNull('bidang_magang')->where('bidang_magang', '!=', '')
        ->distinct()->orderBy('bidang_magang')->pluck('bidang_magang'));

    $filterSelect = [
        ['name' => 'instansi', 'label' => 'Instansi',      'semua' => 'Semua Instansi', 'value' => $instansiDipilih, 'opsi' => $opsiInstansi],
        ['name' => 'bidang',   'label' => 'Bidang Magang', 'semua' => 'Semua Bidang',   'value' => $bidangDipilih,   'opsi' => $opsiBidang],
    ];

    // ---- Nomor halaman untuk pagination (1 ... 4 5 6 ... 10) ----
    $halaman  = $users->currentPage();
    $terakhir = $users->lastPage();
    $jendela  = collect([1, $terakhir, $halaman - 1, $halaman, $halaman + 1])
        ->filter(fn ($p) => $p >= 1 && $p <= $terakhir)
        ->unique()->sort()->values();
@endphp

{{-- Ikon khusus halaman ini (prefix p-admuser-) --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="p-admuser-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
</svg>

<div class="adm-user">

    {{-- ===== JUDUL + TOMBOL TAMBAH ===== --}}
    <div class="adm-user-head">
        <div>
            <h1>Data User</h1>
            <p>Kelola akun dan informasi peserta magang.</p>
        </div>

        <a href="{{ route('admin.data-user.create') }}" class="adm-user-btn adm-user-btn--primary adm-user-btn--lg">
            <svg aria-hidden="true"><use href="#p-admuser-plus"/></svg>
            Tambah User
        </a>
    </div>


    {{-- ===== NOTIFIKASI ===== --}}
    @if (session('success'))
        <div class="adm-user-alert adm-user-alert--success" role="status">
            <svg aria-hidden="true"><use href="#s-ok"/></svg>
            <div class="adm-user-alert-text">{{ session('success') }}</div>
            <button type="button" class="adm-user-alert-close" aria-label="Tutup notifikasi">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="adm-user-alert adm-user-alert--error" role="alert">
            <svg aria-hidden="true"><use href="#s-alert"/></svg>
            <div class="adm-user-alert-text">
                <strong>Terjadi kesalahan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="adm-user-alert-close" aria-label="Tutup notifikasi">&times;</button>
        </div>
    @endif


    {{-- ===== STATISTIK ===== --}}
    <section class="adm-user-stats">
        <article class="adm-user-stat">
            <span>Total peserta terdaftar</span>
            <strong>{{ $users->total() }}</strong>
        </article>

        <article class="adm-user-stat">
            <span>Peserta aktif</span>
            <strong>
                {{ \App\Models\User::where('role', 'user')->where('status_akun', 'aktif')->count() }}
            </strong>
        </article>

        <article class="adm-user-stat">
            <span>Peserta nonaktif</span>
            <strong>
                {{ \App\Models\User::where('role', 'user')->where('status_akun', 'nonaktif')->count() }}
            </strong>
        </article>
    </section>


    {{-- ===== FILTER + PENCARIAN =====
         Semua dikirim lewat GET. Filter baru berlaku saat tombol "Cari" ditekan. --}}
    <form action="{{ route('admin.data-user') }}" method="GET" class="adm-user-search">

        {{-- Filter: Instansi & Bidang Magang (dropdown kustom) --}}
        @foreach ($filterSelect as $f)
            <div class="adm-user-filter">
                <span class="adm-user-filter-label">{{ $f['label'] }}</span>

                <div class="adm-user-select" data-select>
                    <input type="hidden" name="{{ $f['name'] }}" value="{{ $f['value'] }}">

                    <button type="button" class="adm-user-select-btn" aria-haspopup="listbox" aria-expanded="false">
                        <span class="adm-user-select-text {{ $f['value'] === '' ? 'is-placeholder' : '' }}">{{ $f['value'] !== '' ? $f['value'] : $f['semua'] }}</span>
                        <svg class="adm-user-select-arrow" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                    </button>

                    <ul class="adm-user-select-list" role="listbox" hidden>
                        <li>
                            <button type="button" role="option" data-value=""
                                    class="adm-user-option {{ $f['value'] === '' ? 'is-selected' : '' }}">{{ $f['semua'] }}</button>
                        </li>
                        @foreach ($f['opsi'] as $opsi)
                            <li>
                                <button type="button" role="option" data-value="{{ $opsi }}"
                                        class="adm-user-option {{ $f['value'] === (string) $opsi ? 'is-selected' : '' }}">{{ $opsi }}</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach

        {{-- Filter: Periode Magang (pilih bulan + tahun) --}}
        <div class="adm-user-filter">
            <span class="adm-user-filter-label">Periode Magang</span>

            <div class="adm-user-mp-wrap">
                <input type="hidden" name="periode" value="{{ $periodeValid }}">

                <button type="button" class="adm-user-select-btn" aria-haspopup="dialog" aria-expanded="false">
                    <!-- <svg class="adm-user-select-ico" aria-hidden="true"><use href="#i-calendar"/></svg> -->
                    <span class="adm-user-select-text {{ $periodeLabel ? '' : 'is-placeholder' }}"
                          data-placeholder="Semua Periode">{{ $periodeLabel ?? 'Semua Periode' }}</span>
                    <svg class="adm-user-select-arrow" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                </button>

                <div class="adm-user-mp" role="dialog" aria-label="Pilih periode magang" hidden></div>
            </div>
        </div>

        {{-- Pencarian --}}
        <label class="adm-user-search-field">
            <svg aria-hidden="true"><use href="#i-search"/></svg>
            <input
                type="text"
                name="search"
                value="{{ $keyword }}"
                placeholder="Cari nama, email, instansi..."
                autocomplete="off"
            >
        </label>

        <button type="submit" class="adm-user-btn adm-user-btn--primary adm-user-btn--lg">Cari</button>

        @if ($filterAktif)
            <a href="{{ route('admin.data-user') }}" class="adm-user-btn adm-user-btn--light adm-user-btn--lg">Reset</a>
        @endif

    </form>


    {{-- ===== DAFTAR PESERTA ===== --}}
    <section class="adm-user-panel">

        <h2 class="adm-user-panel-title">Daftar Peserta</h2>

        <div class="adm-user-table-wrap">
            <table class="adm-user-table">
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
                        @php $aktif = $user->status_akun === 'aktif'; @endphp
                        <tr>
                            <td class="adm-user-no">{{ $users->firstItem() + $loop->index }}</td>

                            <td>
                                <div class="adm-user-name">{{ $user->nama }}</div>
                                <div class="adm-user-muted">{{ $user->email }}</div>
                                <div class="adm-user-muted">{{ $user->no_wa }}</div>
                            </td>

                            <td>{{ $user->asal_instansi }}</td>

                            <td>{{ $user->bidang_magang }}</td>

                            <td>
                                {{ $user->tanggal_mulai?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                                <div class="adm-user-muted">sampai</div>
                                {{ $user->tanggal_selesai?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                            </td>

                            <td>
                                <span class="adm-user-status {{ $aktif ? 'adm-user-status--aktif' : 'adm-user-status--nonaktif' }}">
                                    {{ ucfirst($user->status_akun) }}
                                </span>
                            </td>

                            <td>
                                <div class="adm-user-actions">
                                    <a href="{{ route('admin.data-user.edit', $user->id) }}"
                                       class="adm-user-btn adm-user-btn--edit">
                                        Edit
                                    </a>

                                    {{-- Konfirmasi memakai pop-up kustom (lihat js/admin/data-user.js) --}}
                                    <form action="{{ route('admin.data-user.status', $user->id) }}"
                                          method="POST"
                                          data-confirm
                                          data-confirm-name="{{ $user->nama }}"
                                          data-confirm-tone="{{ $aktif ? 'danger' : 'success' }}"
                                          data-confirm-title="{{ $aktif ? 'Nonaktifkan akun?' : 'Aktifkan akun?' }}"
                                          data-confirm-text="{{ $aktif
                                                ? 'Akun ' . $user->nama . ' tidak akan bisa login sampai diaktifkan kembali.'
                                                : 'Akun ' . $user->nama . ' akan bisa login kembali.' }}"
                                          data-confirm-button="{{ $aktif ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="adm-user-btn adm-user-btn--status">
                                            {{ $aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="adm-user-empty">
                                @if ($filterAktif)
                                    Tidak ditemukan peserta yang cocok dengan pencarian atau filter.
                                @else
                                    Belum ada data peserta magang.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        {{-- ===== PAGINATION (bahasa Indonesia) ===== --}}
        <div class="adm-user-pager-wrap">
            <p class="adm-user-pager-info">
                Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}
                dari {{ $users->total() }} peserta
            </p>

            @if ($users->hasPages())
                <nav class="adm-user-pager" aria-label="Navigasi halaman">
                    <ul>
                        <li>
                            @if ($users->onFirstPage())
                                <span class="adm-user-page is-disabled">Sebelumnya</span>
                            @else
                                <a href="{{ $users->previousPageUrl() }}" rel="prev" class="adm-user-page">Sebelumnya</a>
                            @endif
                        </li>

                        @foreach ($jendela as $i => $p)
                            @if ($i > 0 && $p - $jendela[$i - 1] > 1)
                                <li><span class="adm-user-page-gap">…</span></li>
                            @endif

                            <li>
                                @if ($p === $halaman)
                                    <span class="adm-user-page is-active" aria-current="page">{{ $p }}</span>
                                @else
                                    <a href="{{ $users->url($p) }}" class="adm-user-page" aria-label="Halaman {{ $p }}">{{ $p }}</a>
                                @endif
                            </li>
                        @endforeach

                        <li>
                            @if ($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}" rel="next" class="adm-user-page">Berikutnya</a>
                            @else
                                <span class="adm-user-page is-disabled">Berikutnya</span>
                            @endif
                        </li>
                    </ul>
                </nav>
            @endif
        </div>

    </section>

</div>


{{-- ===== POP-UP KONFIRMASI (dipakai tombol Aktifkan/Nonaktifkan) ===== --}}
<div class="adm-user-modal" id="adm-user-modal" hidden>
    <div class="adm-user-modal-backdrop" data-modal-close></div>

    <div class="adm-user-modal-box" role="dialog" aria-modal="true" aria-labelledby="adm-user-modal-title">
        <span class="adm-user-modal-icon">
            <svg class="adm-user-modal-icon--danger" aria-hidden="true"><use href="#s-alert"/></svg>
            <svg class="adm-user-modal-icon--success" aria-hidden="true"><use href="#s-ok"/></svg>
        </span>

        <h3 id="adm-user-modal-title" class="adm-user-modal-title"></h3>
        <p class="adm-user-modal-text"></p>

        <div class="adm-user-modal-actions">
            <button type="button" class="adm-user-btn adm-user-btn--outline adm-user-btn--lg" data-modal-close>Batal</button>
            <button type="button" class="adm-user-btn adm-user-btn--primary adm-user-btn--lg" id="adm-user-modal-confirm">Ya</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/data-user.js') }}"></script>
@endpush