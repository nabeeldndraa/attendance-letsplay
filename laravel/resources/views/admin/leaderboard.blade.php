@extends('layouts.admin')
@section('title', 'Leaderboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/leaderboard.css') }}">
@endpush

@php
    /*
     * Variabel yang dibaca dari controller (semua opsional, ada fallback contoh):
     *   $leaderboard : koleksi/array baris, tiap baris punya field
     *                  nama, divisi, poin, tepat_waktu, terlambat, izin, alpa
     *                  (sudah terurut dari poin terbesar)
     *   $namaBulan   : "March 2028"
     *   $sisaHari    : sisa hari menuju reset poin
     *   $daftarBulan : array [value => label] untuk dropdown bulan
     *   $bulanDipilih: value bulan yang aktif
     */
    $dataContoh = [
        ['nama' => 'Princess Ember Suyani', 'divisi' => '3D Illustration',  'poin' => 2005, 'tepat_waktu' => 26, 'terlambat' => 0, 'izin' => 0, 'alpa' => 0],
        ['nama' => 'Donnalls Duck',         'divisi' => 'Programming',      'poin' => 1980, 'tepat_waktu' => 25, 'terlambat' => 0, 'izin' => 1, 'alpa' => 0],
        ['nama' => 'Mikcy',                 'divisi' => 'Public Relation',  'poin' => 1930, 'tepat_waktu' => 25, 'terlambat' => 1, 'izin' => 0, 'alpa' => 0],
    ];

    $rows = collect(isset($leaderboard) ? $leaderboard : $dataContoh)->values()->map(function ($r, $i) {
        return [
            'rank'        => $i + 1,
            'nama'        => data_get($r, 'nama', '-'),
            'divisi'      => data_get($r, 'divisi', '-'),
            'poin'        => (int) data_get($r, 'poin', 0),
            'tepat_waktu' => (int) data_get($r, 'tepat_waktu', 0),
            'terlambat'   => (int) data_get($r, 'terlambat', 0),
            'izin'        => (int) data_get($r, 'izin', 0),
            'alpa'        => (int) data_get($r, 'alpa', 0),
        ];
    });

    $fmt        = fn ($n) => number_format($n, 0, ',', '.');
    $podium     = [2 => $rows->get(1), 1 => $rows->get(0), 3 => $rows->get(2)]; // urutan tampil: 2 - 1 - 3
    $judulBulan = $namaBulan ?? 'March 2028';
    $sisa       = $sisaHari ?? 12;
    $bulanList  = $daftarBulan ?? [$judulBulan => $judulBulan];
    $bulanAktif = $bulanDipilih ?? array_key_first($bulanList);

    // data untuk pop-up pilih bulan (key $daftarBulan berformat "YYYY-MM")
    $ymOk       = fn ($v) => preg_match('/^\d{4}-\d{2}$/', (string) $v) === 1;
    $kunciBulan = collect(array_keys($bulanList))->filter($ymOk)->sort()->values();
    $bulanMin   = $kunciBulan->first();
    $bulanMax   = $kunciBulan->last();
    $bulanYM    = $ymOk($bulanAktif) ? $bulanAktif : '';
    $labelAktif = $bulanList[$bulanAktif] ?? $judulBulan;
    $urlEkspor  = \Illuminate\Support\Facades\Route::has('admin.leaderboard.export')
                    ? route('admin.leaderboard.export') : '#';
    $urlLeader  = \Illuminate\Support\Facades\Route::has('admin.leaderboard')
                    ? route('admin.leaderboard') : '#';
@endphp

@section('content')
    {{-- simbol ikon khusus halaman ini --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <symbol id="adm-lb-download" viewBox="0 0 24 24"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></symbol>
    </svg>

    <section class="adm-lb">

        {{-- header: judul + info reset poin (1 baris) --}}
        <header class="adm-lb-head">
            <h1 class="adm-lb-head-title">{{ $judulBulan }} Leaderboard</h1>

            <div class="adm-lb-reset">
                <svg class="adm-lb-ico" aria-hidden="true"><use href="#i-clock"/></svg>
                <span>Point Reset in <strong>{{ $sisa }} Days</strong></span>
            </div>
        </header>

        {{-- panel merah --}}
        <div class="adm-lb-panel">

            @if ($rows->isEmpty())
                <div class="adm-lb-empty">Belum ada data leaderboard untuk bulan ini.</div>
            @else
                {{-- podium top 3 --}}
                <div class="adm-lb-podium">
                    @foreach ($podium as $pos => $p)
                        @if ($p)
                            <article class="adm-lb-card adm-lb-card--{{ $pos }}">
                                <span class="adm-lb-badge">{{ $pos }}</span>
                                <h3 class="adm-lb-card-name">{{ $p['nama'] }}</h3>
                                <p class="adm-lb-card-role">{{ $p['divisi'] }}</p>
                                <span class="adm-lb-pts">{{ $fmt($p['poin']) }} pts</span>
                            </article>
                        @endif
                    @endforeach
                </div>

                <h2 class="adm-lb-title">Complete Rankings</h2>

                {{-- tabel: semua intern langsung tampil --}}
                <div class="adm-lb-tablecard">
                    <div class="adm-lb-scroll">
                        <table class="adm-lb-table">
                            <thead>
                                <tr>
                                    <th class="adm-lb-c">Rank</th>
                                    <th>Name</th>
                                    <th class="adm-lb-c">Point</th>
                                    <th class="adm-lb-c">On-Time</th>
                                    <th class="adm-lb-c">Late</th>
                                    <th class="adm-lb-c">Permission</th>
                                    <th class="adm-lb-c">Absent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $r)
                                    <tr>
                                        <td class="adm-lb-c adm-lb-rank">{{ $r['rank'] }}</td>
                                        <td>{{ $r['nama'] }}</td>
                                        <td class="adm-lb-c">{{ $fmt($r['poin']) }}</td>
                                        <td class="adm-lb-c">{{ $r['tepat_waktu'] }}</td>
                                        <td class="adm-lb-c">{{ $r['terlambat'] }}</td>
                                        <td class="adm-lb-c">{{ $r['izin'] }}</td>
                                        <td class="adm-lb-c">{{ $r['alpa'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- bar bawah (lengket di bawah layar): pilih bulan + ekspor --}}
        <div class="adm-lb-bar">
            <form method="GET" action="{{ $urlLeader }}" class="adm-lb-month" id="adm-lb-month-form">
                <span class="adm-lb-month-label" id="adm-lb-month-lbl">Pilih Bulan</span>
                <input type="hidden" name="bulan" id="adm-lb-bulan" value="{{ $bulanAktif }}">
                <div class="adm-lb-select">
                    <svg class="adm-lb-ico adm-lb-select-ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                    <button type="button" class="adm-lb-select-btn" id="adm-lb-month-btn"
                            aria-haspopup="dialog" aria-labelledby="adm-lb-month-lbl adm-lb-month-btn"
                            data-adm-lb-open="adm-lb-modal-month">{{ $labelAktif }}</button>
                    <svg class="adm-lb-ico adm-lb-select-caret" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                </div>
            </form>

            <button type="button" class="adm-lb-export" data-adm-lb-open="adm-lb-modal-export">Ekspor</button>
        </div>
    </section>

    {{-- POP-UP: pilih bulan & tahun (tanpa tanggal) --}}
    <dialog class="adm-lb-modal adm-lb-modal--cal" id="adm-lb-modal-month" aria-labelledby="adm-lb-cal-title"
            data-value="{{ $bulanYM }}" data-min="{{ $bulanMin }}" data-max="{{ $bulanMax }}">
        <div class="adm-lb-modal-head">
            <div>
                <h2 id="adm-lb-cal-title">Pilih Bulan</h2>
                <p>Pilih bulan dan tahun leaderboard</p>
            </div>
            <button type="button" class="adm-lb-x" data-adm-lb-close aria-label="Tutup">
                <svg class="adm-lb-ico" aria-hidden="true"><use href="#i-x"/></svg>
            </button>
        </div>

        <div class="adm-lb-cal-year">
            <button type="button" class="adm-lb-cal-nav" id="adm-lb-cal-prev" aria-label="Tahun sebelumnya">
                <svg class="adm-lb-ico" aria-hidden="true"><use href="#i-chevron-left"/></svg>
            </button>
            <strong id="adm-lb-cal-year" aria-live="polite"></strong>
            <button type="button" class="adm-lb-cal-nav" id="adm-lb-cal-next" aria-label="Tahun berikutnya">
                <svg class="adm-lb-ico" aria-hidden="true"><use href="#i-chevron-right"/></svg>
            </button>
        </div>

        <div class="adm-lb-cal-grid" id="adm-lb-cal-grid" role="group" aria-label="Daftar bulan"></div>

        <div class="adm-lb-modal-foot">
            <button type="button" class="adm-lb-btn adm-lb-btn--ghost" data-adm-lb-close>Batal</button>
            <button type="button" class="adm-lb-btn adm-lb-btn--solid" id="adm-lb-cal-apply">Terapkan</button>
        </div>
    </dialog>

    {{-- POP-UP: ekspor --}}
    <dialog class="adm-lb-modal adm-lb-modal--sm" id="adm-lb-modal-export" aria-labelledby="adm-lb-exp-title">
        <form method="GET" action="{{ $urlEkspor }}">
            <input type="hidden" name="bulan" value="{{ $bulanAktif }}">

            <div class="adm-lb-modal-head">
                <div>
                    <h2 id="adm-lb-exp-title">Ekspor Leaderboard</h2>
                    <p>{{ $judulBulan }}</p>
                </div>
                <button type="button" class="adm-lb-x" data-adm-lb-close aria-label="Tutup">
                    <svg class="adm-lb-ico" aria-hidden="true"><use href="#i-x"/></svg>
                </button>
            </div>

            <div class="adm-lb-modal-body">
                <p class="adm-lb-exp-text">Pilih format file untuk mengunduh data peringkat bulan ini.</p>

                <div class="adm-lb-formats">
                    <label class="adm-lb-format">
                        <input type="radio" name="format" value="xlsx" checked>
                        <span class="adm-lb-format-box">
                            <strong>Excel</strong>
                            <small>.xlsx</small>
                        </span>
                    </label>
                    <label class="adm-lb-format">
                        <input type="radio" name="format" value="pdf">
                        <span class="adm-lb-format-box">
                            <strong>PDF</strong>
                            <small>.pdf</small>
                        </span>
                    </label>
                    <label class="adm-lb-format">
                        <input type="radio" name="format" value="csv">
                        <span class="adm-lb-format-box">
                            <strong>CSV</strong>
                            <small>.csv</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="adm-lb-modal-foot">
                <button type="button" class="adm-lb-btn adm-lb-btn--ghost" data-adm-lb-close>Batal</button>
                <button type="submit" class="adm-lb-btn adm-lb-btn--solid">
                    <svg class="adm-lb-ico" aria-hidden="true"><use href="#adm-lb-download"/></svg>
                    Unduh
                </button>
            </div>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/leaderboard.js') }}"></script>
@endpush