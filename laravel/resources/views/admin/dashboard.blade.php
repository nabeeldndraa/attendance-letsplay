@extends('layouts.admin')
@section('title', 'Dashboard Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@section('content')
@php
    // ---- Sapaan otomatis (WIB) ----
    $now      = \Carbon\Carbon::now('Asia/Jakarta')->locale('id');
    $greeting = $now->hour < 12 ? 'Good Morning' : ($now->hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $bulanIni = $now->translatedFormat('F Y');

    // Bulan yang dipilih di Attendance Overview (query ?bulan=YYYY-MM, default bulan ini)
    $bulanPilih = $now->copy()->startOfMonth();
    $bulanReq   = (string) request('bulan');
    if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanReq)) {
        $bulanPilih = \Carbon\Carbon::createFromFormat('!Y-m', $bulanReq, 'Asia/Jakarta')->locale('id');
        if ($bulanPilih->gt($now)) {
            $bulanPilih = $now->copy()->startOfMonth();
        }
    }
    $bulanLabel = $bulanPilih->translatedFormat('F Y');
    $bulanValue = $bulanPilih->format('Y-m');
    $bulanMax   = $now->format('Y-m');

    // ---- Batas baris tabel (kalau lebih, muncul "Lihat semua") ----
    $maxBaris = 5;

    // ---- Data dari controller (TIDAK diubah) ----
    $presensiList  = $presensiTerbaru->take($maxBaris);
    $presensiTotal = $presensiTerbaru->count();

    /*
     * TODO (backend): 3 data di bawah belum dikirim controller, jadi sementara DUMMY.
     * Cukup kirim variabelnya dari controller dengan nama yang sama, otomatis terpakai:
     *   $menungguKonfirmasi   -> koleksi pengajuan: nama (atau user->nama), status, alasan
     *   $leaderboardBulanIni  -> daftar [nama, poin] urut dari poin tertinggi
     *   $ringkasanBulanIni    -> ['hadir' => .., 'terlambat' => .., 'alpha' => ..]
     */
    $menunggu = collect($menungguKonfirmasi ?? [
        ['nama' => 'Nama 1', 'status' => 'Terlambat', 'alasan' => 'Blablabla'],
    ]);
    $menungguList = $menunggu->take($maxBaris);

    $leaderboard = collect($leaderboardBulanIni ?? [
        ['nama' => 'Nama 1', 'poin' => 300],
        ['nama' => 'Nama 2', 'poin' => 290],
        ['nama' => 'Nama 3', 'poin' => 285],
    ])->take(3)->values();

    $bulan   = $ringkasanBulanIni ?? ['hadir' => 62, 'terlambat' => 23, 'alpha' => 15];
    $totalBulan = max(array_sum($bulan), 1);
    $pct = fn ($n) => round($n / $totalBulan * 100);
    $p1  = $bulan['hadir'] / $totalBulan * 100;
    $p2  = $p1 + $bulan['terlambat'] / $totalBulan * 100;

    // ---- Route tujuan "Lihat semua" (aman kalau route belum ada) ----
    $urlPresensi    = \Illuminate\Support\Facades\Route::has('admin.data-presensi') ? route('admin.data-presensi') : '#';
    $urlLeaderboard = \Illuminate\Support\Facades\Route::has('admin.leaderboard')   ? route('admin.leaderboard')   : '#';
    $urlKonfirmasi  = \Illuminate\Support\Facades\Route::has('admin.konfirmasi')    ? route('admin.konfirmasi')    : '#';

    // ---- Kartu ringkasan hari ini ----
    $kartu = [
        ['key' => 'total_peserta_aktif',     'label' => 'Peserta Aktif',        'note' => 'Peserta dengan akun aktif',   'icon' => 'p-adm-user',      'tone' => 'pink'],
        ['key' => 'total_presensi_hari_ini', 'label' => 'Presensi Hari Ini',    'note' => 'Total presensi tercatat',     'icon' => 'p-adm-clock',     'tone' => 'green'],
        ['key' => 'tepat_waktu',             'label' => 'Tepat Waktu',          'note' => 'Presensi tepat waktu',        'icon' => 'p-adm-check',     'tone' => 'blue'],
        ['key' => 'terlambat',               'label' => 'Terlambat',            'note' => 'Presensi berstatus terlambat', 'icon' => 's-alert',        'tone' => 'orange'],
        ['key' => 'menunggu_konfirmasi',     'label' => 'Menunggu Konfirmasi',  'note' => 'Pengajuan konfirmasi admin',  'icon' => 'p-adm-hourglass', 'tone' => 'purple'],
        ['key' => 'wfo',                     'label' => 'Presensi WFO',         'note' => 'Work From Office',            'icon' => 'p-adm-home',      'tone' => 'green'],
        ['key' => 'wfh',                     'label' => 'Presensi WFH',         'note' => 'Work From Home',              'icon' => 'p-adm-grid',      'tone' => 'pink'],
    ];

    // Warna badge status
    $badge = function ($status) {
        return match ($status) {
            'Tepat Waktu' => 'adm-dash-badge--green',
            'Terlambat'   => 'adm-dash-badge--orange',
            default       => 'adm-dash-badge--red',
        };
    };
@endphp

{{-- Ikon khusus halaman ini (prefix p-adm-) --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="p-adm-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4.2" fill="#e11d2e"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7Z" fill="#e11d2e"/></symbol>
    <symbol id="p-adm-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#1fa34a"/><path d="M12 6.5V12l3.5 2" stroke="#fff" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    <symbol id="p-adm-check" viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5" stroke="#2f6fed" stroke-width="3.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    <symbol id="p-adm-hourglass" viewBox="0 0 24 24"><g stroke="#7c5cd6" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12M6 21h12"/><path d="M7 3v3.5a5 5 0 0 0 2 4L12 12 9 13.5a5 5 0 0 0-2 4V21M17 3v3.5a5 5 0 0 1-2 4L12 12l3 1.5a5 5 0 0 1 2 4V21"/></g></symbol>
    <symbol id="p-adm-home" viewBox="0 0 24 24"><path d="M12 3 2.5 11.5h2.8V20h5v-5.5h3.4V20h5v-8.5h2.8L12 3Z" fill="#22a85a"/></symbol>
    <symbol id="p-adm-grid" viewBox="0 0 24 24"><g stroke="#ef4444" stroke-width="2.2" fill="none"><rect x="3.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="3.5" width="7" height="7" rx="2"/><rect x="3.5" y="13.5" width="7" height="7" rx="2"/><rect x="13.5" y="13.5" width="7" height="7" rx="2"/></g></symbol>
    <symbol id="p-adm-star" viewBox="0 0 24 24"><path fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.400 6.100 20.500l1.200-6.500L2.500 9.400l6.600-.9L12 2.500Z"/></symbol>
</svg>

<div class="adm-dash">

    {{-- ===== HERO ===== --}}
    <section class="adm-dash-hero">

        <div class="adm-dash-hero-text">
            <h1>{{ $greeting }}, Admin!</h1>
            <p class="adm-dash-hero-sub">Pantau aktivitas dan presensi peserta magang Let's Play Indonesia.</p>
            <p class="adm-dash-hero-date">
                <svg aria-hidden="true"><use href="#i-calendar"/></svg>
                {{ \Carbon\Carbon::parse($hariIni)->locale('id')->translatedFormat('l, d F Y') }}
            </p>
        </div>

        <div class="adm-dash-hero-art">
            <img src="{{ asset('images/kucing_elso2.png') }}" alt="" class="adm-dash-hero-cat">
        </div>

    </section>


    {{-- ===== RINGKASAN HARI INI ===== --}}
    <h2 class="adm-dash-section-title">Ringkasan Hari Ini</h2>

    <section class="adm-dash-stats">
        @foreach ($kartu as $k)
            <article class="adm-dash-stat">
                <span class="adm-dash-stat-icon adm-dash-tone--{{ $k['tone'] }}">
                    <svg aria-hidden="true"><use href="#{{ $k['icon'] }}"/></svg>
                </span>

                <div class="adm-dash-stat-body">
                    <p class="adm-dash-stat-label">{{ $k['label'] }}</p>
                    <p class="adm-dash-stat-value">{{ $statistik[$k['key']] ?? 0 }}</p>
                    <p class="adm-dash-stat-note">{{ $k['note'] }}</p>
                </div>
            </article>
        @endforeach
    </section>


    {{-- ===== PRESENSI TERBARU + MENUNGGU KONFIRMASI ===== --}}
    <section class="adm-dash-panels">

        <article class="adm-dash-panel" id="presensi">
            <header class="adm-dash-panel-head">
                <div>
                    <h3>Presensi Terbaru</h3>
                    <p>Riwayat presensi seluruh peserta</p>
                </div>
                <span class="adm-dash-count">{{ $presensiTotal }} data</span>
            </header>

            <div class="adm-dash-table-wrap">
                <table class="adm-dash-table adm-dash-table--presensi">
                    <thead>
                        <tr>
                            <th>Peserta</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Tipe</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($presensiList as $presensi)
                            <tr>
                                <td>
                                    <div class="adm-dash-person">
                                        <span class="adm-dash-avatar">
                                            {{ strtoupper(substr($presensi->user?->nama ?? 'P', 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="adm-dash-person-name">{{ $presensi->user?->nama ?? 'Peserta' }}</div>
                                            <div class="adm-dash-person-role">{{ $presensi->user?->bidang_magang ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($presensi->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $presensi->jam_presensi ?? '-' }}</td>
                                <td>{{ $presensi->tipe_kerja }}</td>
                                <td>
                                    <span class="adm-dash-badge {{ $badge($presensi->status_presensi) }}">
                                        {{ $presensi->status_presensi }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="adm-dash-empty">Belum ada data presensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($presensiTotal > $maxBaris)
                <footer class="adm-dash-panel-foot">
                    <a href="{{ $urlPresensi }}" class="adm-dash-more">
                        Lihat semua <svg aria-hidden="true"><use href="#i-chevron-right"/></svg>
                    </a>
                </footer>
            @endif
        </article>


        <article class="adm-dash-panel" id="konfirmasi">
            <header class="adm-dash-panel-head">
                <div>
                    <h3>Menunggu Konfirmasi</h3>
                    <p>Pengajuan yang perlu ditinjau admin</p>
                </div>
                <span class="adm-dash-count">{{ $menunggu->count() }} peserta</span>
            </header>

            <div class="adm-dash-table-wrap">
                <table class="adm-dash-table adm-dash-table--konfirmasi">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($menungguList as $item)
                            @php
                                $namaItem   = data_get($item, 'nama') ?? data_get($item, 'user.nama') ?? '-';
                                $statusItem = data_get($item, 'status') ?? data_get($item, 'status_presensi') ?? '-';
                                $alasanItem = data_get($item, 'alasan') ?? '-';
                            @endphp
                            <tr>
                                <td class="adm-dash-strong">{{ $namaItem }}</td>
                                <td><span class="adm-dash-badge {{ $badge($statusItem) }}">{{ $statusItem }}</span></td>
                                <td class="adm-dash-reason" title="{{ $alasanItem }}">{{ $alasanItem }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="adm-dash-empty">Tidak ada yang menunggu konfirmasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($menunggu->count() > $maxBaris)
                <footer class="adm-dash-panel-foot">
                    <a href="{{ $urlKonfirmasi }}" class="adm-dash-more">
                        Lihat semua <svg aria-hidden="true"><use href="#i-chevron-right"/></svg>
                    </a>
                </footer>
            @endif
        </article>

    </section>


    {{-- ===== RINGKASAN BULAN INI ===== --}}
    <h2 class="adm-dash-section-title">Ringkasan Bulan Ini</h2>

    <section class="adm-dash-month" id="ringkasan-bulan">

        {{-- Leaderboard --}}
        <article class="adm-dash-lb">
            <header class="adm-dash-lb-head">
                <svg class="adm-dash-lb-trophy" aria-hidden="true"><use href="#i-trophy"/></svg>

                <div class="adm-dash-lb-title">
                    <h3>Leaderboard</h3>
                    <p>{{ $bulanIni }}</p>
                </div>

                <a href="{{ $urlLeaderboard }}" class="adm-dash-lb-more">
                    Lihat semua <svg aria-hidden="true"><use href="#i-chevron-right"/></svg>
                </a>
            </header>

            <ol class="adm-dash-lb-list">
                @forelse ($leaderboard as $i => $row)
                    <li class="adm-dash-lb-row">
                        <span class="adm-dash-lb-star adm-dash-lb-star--{{ $i + 1 }}">
                            <svg aria-hidden="true"><use href="#p-adm-star"/></svg>
                            <b>{{ $i + 1 }}</b>
                        </span>
                        <span class="adm-dash-lb-name">{{ data_get($row, 'nama') }}</span>
                        <span class="adm-dash-lb-pts">{{ data_get($row, 'poin') }}</span>
                    </li>
                @empty
                    <li class="adm-dash-lb-empty">Belum ada data leaderboard.</li>
                @endforelse
            </ol>
        </article>


        {{-- Attendance Overview --}}
        <article class="adm-dash-att">
            <header class="adm-dash-att-head">
                <h3>Attendance Overview</h3>
                {{-- Pilih bulan (hanya bulan + tahun). Memilih bulan mengirim ?bulan=YYYY-MM ke halaman ini. --}}
                <form method="GET" action="{{ url()->current() }}#ringkasan-bulan"
                      class="adm-dash-mp-wrap" data-max="{{ $bulanMax }}">
                    <input type="hidden" name="bulan" value="{{ $bulanValue }}">

                    <button type="button" class="adm-dash-month-chip" aria-haspopup="dialog" aria-expanded="false">
                        <svg class="adm-dash-chip-ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                        <span class="adm-dash-month-text">{{ $bulanLabel }}</span>
                        <svg class="adm-dash-chip-arrow" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                    </button>

                    <div class="adm-dash-mp" role="dialog" aria-label="Pilih bulan" hidden></div>
                </form>
            </header>

            <div class="adm-dash-att-body">

                <div class="adm-dash-donut" style="--p1: {{ round($p1, 2) }}%; --p2: {{ round($p2, 2) }}%;">
                    <div class="adm-dash-donut-center">
                        <span>Total</span>
                        <strong>{{ array_sum($bulan) }}</strong>
                    </div>
                </div>

                <ul class="adm-dash-legend">
                    <li class="adm-dash-legend-row adm-dash-legend-row--hadir">
                        <span class="adm-dash-legend-name"><i></i>Hadir</span>
                        <b>{{ $bulan['hadir'] }}</b>
                        <span>{{ $pct($bulan['hadir']) }}%</span>
                    </li>
                    <li class="adm-dash-legend-row adm-dash-legend-row--telat">
                        <span class="adm-dash-legend-name"><i></i>Terlambat</span>
                        <b>{{ $bulan['terlambat'] }}</b>
                        <span>{{ $pct($bulan['terlambat']) }}%</span>
                    </li>
                    <li class="adm-dash-legend-row adm-dash-legend-row--alpha">
                        <span class="adm-dash-legend-name"><i></i>Alpha</span>
                        <b>{{ $bulan['alpha'] }}</b>
                        <span>{{ $pct($bulan['alpha']) }}%</span>
                    </li>
                </ul>

            </div>
        </article>

    </section>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush