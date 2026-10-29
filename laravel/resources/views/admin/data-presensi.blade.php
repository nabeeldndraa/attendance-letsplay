@extends('layouts.admin')

@section('title', 'Data Presensi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/data-presensi.css') }}">
@endpush

@php
    /*
     * Variabel dari controller (TIDAK diubah): $presensi (paginator) dan $filter (array).
     * Nama field form lama (search, tanggal_mulai, tanggal_selesai, status_presensi, tipe_kerja)
     * dan route (admin.data-presensi, admin.dashboard) sama seperti sebelumnya.
     *
     * BARU (tampilan saja, belum ada di database):
     *   - filter  : izin            -> '', 'Izin', 'Sakit'
     *   - kolom   : jenis_izin      -> 'Izin' / 'Sakit' / null
     *               alasan_izin     -> teks alasan
     *               progress        -> teks biasa (mis. "Selesai")
     *
     * Tinggal ganti $pakaiDummy ke false kalau kolom di atas sudah ada di tabel.
     * Selama true, baris tertentu diberi data contoh supaya tampilan & pop-up bisa dicoba.
     */
    $pakaiDummy = true;

    // Pagination ikut membawa query string (filter tidak hilang saat pindah halaman)
    $presensi->withQueryString();

    $izinDipilih = request()->query('izin');
    $izinDipilih = is_string($izinDipilih) ? $izinDipilih : '';

    $opsiStatus = [
        ''                    => 'Semua Status',
        'Tepat Waktu'         => 'Tepat Waktu',
        'Terlambat'           => 'Terlambat',
        'Alpha'               => 'Alpha',
        'Menunggu Konfirmasi' => 'Menunggu Konfirmasi',
    ];

    $opsiTipe = [
        ''    => 'Semua Tipe',
        'WFO' => 'WFO',
        'WFH' => 'WFH',
    ];

    $opsiIzin = [
        ''      => 'Semua',
        'Izin'  => 'Izin',
        'Sakit' => 'Sakit',
    ];

    $dropdown = [
        ['name' => 'status_presensi', 'label' => 'Status Presensi', 'opsi' => $opsiStatus, 'nilai' => $filter['status_presensi'] ?? ''],
        ['name' => 'tipe_kerja',      'label' => 'Tipe Kerja',      'opsi' => $opsiTipe,   'nilai' => $filter['tipe_kerja'] ?? ''],
        ['name' => 'izin',            'label' => 'Izin',            'opsi' => $opsiIzin,   'nilai' => $filter['izin'] ?? $izinDipilih],
    ];

    // hanya terima format Y-m-d yang benar-benar tanggal (2026-13-45 ditolak)
    $tglValid = function ($v) {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', (string) $v, $m)) {
            return '';
        }

        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $v : '';
    };
    $tglLabel = fn ($v) => $v ? \Carbon\Carbon::parse($v)->locale('id')->translatedFormat('j M Y') : '';

    $kolomTanggal = [
        ['name' => 'tanggal_mulai',   'label' => 'Dari Tanggal',   'nilai' => $tglValid($filter['tanggal_mulai'] ?? ''),   'minFrom' => null,            'align' => 'left'],
        ['name' => 'tanggal_selesai', 'label' => 'Sampai Tanggal', 'nilai' => $tglValid($filter['tanggal_selesai'] ?? ''), 'minFrom' => 'tanggal_mulai', 'align' => 'right'],
    ];

    // jumlah filter yang sedang aktif (ditampilkan di tombol Filter versi mobile)
    $jumlahFilterAktif =
        collect($dropdown)->filter(fn ($d) => $d['nilai'] !== '' && array_key_exists($d['nilai'], $d['opsi']))->count()
        + collect($kolomTanggal)->filter(fn ($t) => $t['nilai'] !== '')->count();

    $kelasStatus = fn ($s) => match ($s) {
        'Tepat Waktu'         => 'ok',
        'Terlambat'           => 'late',
        'Alpha'               => 'alpha',
        'Menunggu Konfirmasi' => 'wait',
        default               => 'gray',
    };

    $kelasKonfirmasi = fn ($s) => match ($s) {
        'Diajukan'  => 'wait',
        'Disetujui' => 'ok',
        'Ditolak'   => 'alpha',
        default     => 'gray',
    };

    // baca kolom yang mungkin belum ada di tabel tanpa error
    $ambil = fn ($model, $kolom) => $model->getAttributes()[$kolom] ?? null;

    $halaman  = $presensi->currentPage();
    $terakhir = $presensi->lastPage();
    $awal     = max(1, $halaman - 2);
    $akhir    = min($terakhir, $halaman + 2);
@endphp


@section('content')

    <div class="adm-pres">

        {{-- ===== JUDUL ===== --}}
        <div class="adm-pres-head">
            <div>
                <h1>Data Presensi</h1>
                <p>Kelola dan pantau seluruh riwayat kehadiran peserta magang Let's Play Indonesia.</p>
            </div>
        </div>


        {{-- ===== RINGKASAN ===== --}}
        <div class="adm-pres-stats">
            <div class="adm-pres-stat">
                <span>Data ditemukan</span>
                <strong>{{ number_format($presensi->total(), 0, ',', '.') }}</strong>
            </div>

            <div class="adm-pres-stat">
                <span>Data di halaman ini</span>
                <strong>{{ $presensi->count() }}</strong>
            </div>

            <div class="adm-pres-stat">
                <span>Halaman aktif</span>
                <strong>{{ $halaman }} <small>/ {{ $terakhir }}</small></strong>
            </div>
        </div>


        {{-- ===== FILTER & PENCARIAN ===== --}}
        <form class="adm-pres-search" method="GET" action="{{ route('admin.data-presensi') }}">

            {{--
                Desktop : filter tampil sebagai baris biasa.
                Mobile  : blok ini berubah jadi pop-up dari bawah (dibuka tombol "Filter").
                Satu set input saja, jadi tidak ada form ganda.
            --}}
            <div class="adm-pres-sheet" id="sheetFilter" aria-label="Filter presensi">

                <div class="adm-pres-sheet-head">
                    <h2>Filter</h2>
                    <button type="button" class="adm-pres-icon-btn adm-pres-sheet-close" data-sheet-close aria-label="Tutup filter">
                        <svg class="adm-pres-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="adm-pres-filters">

                    {{-- Dropdown kustom (nilai dikirim lewat input tersembunyi) --}}
                    @foreach ($dropdown as $d)
                        @php
                            $nilaiAda = array_key_exists($d['nilai'], $d['opsi']) ? $d['nilai'] : '';
                        @endphp

                        <div class="adm-pres-filter">
                            <span class="adm-pres-filter-label">{{ $d['label'] }}</span>

                            <div class="adm-pres-pick" data-select>
                                <input type="hidden" name="{{ $d['name'] }}" value="{{ $nilaiAda }}">

                                <button type="button" class="adm-pres-select-btn" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="adm-pres-select-text {{ $nilaiAda === '' ? 'is-placeholder' : '' }}">{{ $d['opsi'][$nilaiAda] }}</span>
                                    <svg class="adm-pres-ico adm-pres-select-arrow" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                                </button>

                                <ul class="adm-pres-select-list" role="listbox" hidden>
                                    @foreach ($d['opsi'] as $nilai => $teks)
                                        <li role="presentation">
                                            <button type="button" role="option"
                                                    class="adm-pres-option {{ (string) $nilai === (string) $nilaiAda ? 'is-selected' : '' }}"
                                                    data-value="{{ $nilai }}"
                                                    aria-selected="{{ (string) $nilai === (string) $nilaiAda ? 'true' : 'false' }}">{{ $teks }}</button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach

                    {{-- Kalender kustom --}}
                    @foreach ($kolomTanggal as $t)
                        <div class="adm-pres-filter">
                            <span class="adm-pres-filter-label">{{ $t['label'] }}</span>

                            <div class="adm-pres-pick" data-date
                                 data-align="{{ $t['align'] }}"
                                 @if ($t['minFrom']) data-min-from="{{ $t['minFrom'] }}" @endif>

                                <input type="hidden" name="{{ $t['name'] }}" id="{{ $t['name'] }}" value="{{ $t['nilai'] }}">

                                <button type="button" class="adm-pres-select-btn" aria-haspopup="dialog" aria-expanded="false">
                                    <svg class="adm-pres-ico adm-pres-select-ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                                    <span class="adm-pres-select-text {{ $t['nilai'] === '' ? 'is-placeholder' : '' }}"
                                          data-placeholder="Pilih tanggal">{{ $t['nilai'] !== '' ? $tglLabel($t['nilai']) : 'Pilih tanggal' }}</span>
                                </button>

                                <div class="adm-pres-cal" role="dialog" aria-label="Pilih tanggal" hidden></div>
                            </div>
                        </div>
                    @endforeach

                </div>

                <div class="adm-pres-sheet-foot">
                    <button type="submit" class="adm-pres-btn adm-pres-btn--primary">Terapkan</button>
                    <a href="{{ route('admin.data-presensi') }}" class="adm-pres-btn adm-pres-btn--light">Reset</a>
                </div>

            </div>

            <div class="adm-pres-sheet-backdrop" data-sheet-backdrop data-sheet-close></div>


            <div class="adm-pres-searchbar">

                <div class="adm-pres-searchrow">
                    <label class="adm-pres-search-field">
                        <svg class="adm-pres-ico" aria-hidden="true"><use href="#i-search"/></svg>
                        <input type="text" id="search" name="search" placeholder="Cari nama peserta..."
                               value="{{ $filter['search'] ?? '' }}" autocomplete="off" aria-label="Cari nama peserta">
                    </label>

                    {{-- hanya tampil di mobile --}}
                    <button type="button" class="adm-pres-btn adm-pres-btn--outline adm-pres-filter-btn"
                            data-sheet-open aria-controls="sheetFilter" aria-expanded="false">
                        <svg class="adm-pres-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/></svg>
                        Filter
                        @if ($jumlahFilterAktif > 0)
                            <span class="adm-pres-filter-count" aria-label="{{ $jumlahFilterAktif }} filter aktif">{{ $jumlahFilterAktif }}</span>
                        @endif
                    </button>
                </div>

                <div class="adm-pres-actions">
                    <button type="submit" class="adm-pres-btn adm-pres-btn--primary">Cari</button>
                    <a href="{{ route('admin.data-presensi') }}" class="adm-pres-btn adm-pres-btn--light">Reset</a>
                </div>

            </div>

        </form>


        {{-- ===== DAFTAR ===== --}}
        <section class="adm-pres-panel">

            <h2 class="adm-pres-panel-title">Riwayat Presensi Peserta</h2>
            <p class="adm-pres-panel-sub">Gunakan pencarian dan filter untuk menemukan data yang diperlukan.</p>

            <div class="adm-pres-table-wrap">
                <table class="adm-pres-table">
                    <thead>
                        <tr>
                            <th>Peserta</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Tipe Kerja</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Izin</th>
                            <th>Konfirmasi</th>
                            <th>Progress</th>
                            <th>Poin</th>
                            <th>Detail</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($presensi as $item)
                            @php
                                $jenisIzin  = $ambil($item, 'jenis_izin');
                                $alasanIzin = $ambil($item, 'alasan_izin');
                                $progress   = $ambil($item, 'progress');

                                // data contoh (hapus dengan $pakaiDummy = false)
                                if ($pakaiDummy) {
                                    $jenisIzin  ??= [null, null, 'Izin', null, 'Sakit'][$loop->index % 5];
                                    $alasanIzin ??= match ($jenisIzin) {
                                        'Izin'  => "Ada keperluan keluarga yang tidak bisa ditinggalkan.\nTugas hari ini akan disusul besok.",
                                        'Sakit' => 'Demam dan sedang istirahat sesuai anjuran dokter.',
                                        default => null,
                                    };
                                    $progress   ??= ['Mengerjakan halaman login', 'Revisi tampilan dashboard', 'Selesai', 'Menunggu review', 'Belum dimulai'][$loop->index % 5];
                                }

                                $adaIzin = in_array($jenisIzin, ['Izin', 'Sakit'], true);

                                $progressTeks = is_scalar($progress) && trim((string) $progress) !== '' ? trim((string) $progress) : null;

                                $nama = $item->user?->nama ?? 'Peserta tidak ditemukan';
                            @endphp

                            <tr>
                                <td>
                                    <div class="adm-pres-person">
                                        <span class="adm-pres-avatar">{{ strtoupper(substr($item->user?->nama ?? 'P', 0, 1)) }}</span>
                                        <div>
                                            <div class="adm-pres-name">{{ $nama }}</div>
                                            <div class="adm-pres-muted">{{ $item->user?->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>

                                <td>{{ $item->jam_presensi ? substr($item->jam_presensi, 0, 5) : '-' }}</td>

                                <td><span class="adm-pres-badge adm-pres-badge--gray">{{ $item->tipe_kerja ?? '-' }}</span></td>

                                <td>
                                    <div class="adm-pres-location">
                                        <div class="adm-pres-name">
                                            @if ($item->tipe_kerja === 'WFH')
                                                Lokasi WFH
                                            @else
                                                {{ $item->lokasiKantor?->nama_lokasi ?? 'Lokasi tidak diketahui' }}
                                            @endif
                                        </div>

                                        <div class="adm-pres-muted">
                                            @if ($item->latitude_user !== null && $item->longitude_user !== null)
                                                {{ $item->latitude_user }}, {{ $item->longitude_user }}
                                            @else
                                                Koordinat tidak tersedia
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="adm-pres-badge adm-pres-badge--{{ $kelasStatus($item->status_presensi) }}">
                                        {{ $item->status_presensi ?? '-' }}
                                    </span>
                                </td>

                                {{-- Izin --}}
                                <td>
                                    @if ($adaIzin)
                                        <span class="adm-pres-badge adm-pres-badge--{{ $jenisIzin === 'Sakit' ? 'sakit' : 'izin' }}">{{ $jenisIzin }}</span>
                                    @else
                                        <span class="adm-pres-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="adm-pres-badge adm-pres-badge--{{ $kelasKonfirmasi($item->status_konfirmasi) }}">
                                        {{ $item->status_konfirmasi ?: '-' }}
                                    </span>
                                </td>

                                {{-- Progress --}}
                                <td>
                                    @if ($progressTeks !== null)
                                        <div class="adm-pres-progress-text">{{ $progressTeks }}</div>
                                    @else
                                        <span class="adm-pres-muted">-</span>
                                    @endif
                                </td>

                                <td><strong>{{ number_format($item->point_didapat ?? 0, 0, ',', '.') }}</strong></td>

                                {{-- Detail: izin -> Lihat Alasan (pop-up), selain itu -> Lihat Lokasi --}}
                                <td>
                                    @if ($adaIzin)
                                        <button type="button" class="adm-pres-link adm-pres-link--btn"
                                                data-alasan-btn
                                                data-nama="{{ $nama }}"
                                                data-tanggal="{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}"
                                                data-jenis="{{ $jenisIzin }}"
                                                data-alasan="{{ $alasanIzin }}">
                                            <svg class="adm-pres-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5M10 13h6M10 17h6"/></svg>
                                            Lihat Alasan
                                        </button>
                                    @elseif ($item->latitude_user !== null && $item->longitude_user !== null)
                                        <a class="adm-pres-link"
                                           href="https://www.google.com/maps?q={{ $item->latitude_user }},{{ $item->longitude_user }}"
                                           target="_blank" rel="noopener noreferrer">
                                            <svg class="adm-pres-ico" aria-hidden="true"><use href="#i-pin"/></svg>
                                            Lihat Lokasi
                                        </a>
                                    @else
                                        <span class="adm-pres-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="adm-pres-empty">
                                    <strong>Data presensi tidak ditemukan.</strong><br>
                                    Coba ubah kata kunci atau reset filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>


            {{-- ===== PAGINATION ===== --}}
            <div class="adm-pres-pager-wrap">
                <p class="adm-pres-pager-info">
                    Menampilkan {{ $presensi->firstItem() ?? 0 }}–{{ $presensi->lastItem() ?? 0 }}
                    dari {{ $presensi->total() }} data
                </p>

                @if ($terakhir > 1)
                    <nav class="adm-pres-pager" aria-label="Halaman data presensi">
                        <ul>
                            <li>
                                @if ($presensi->onFirstPage())
                                    <span class="adm-pres-page is-disabled" aria-hidden="true">←</span>
                                @else
                                    <a class="adm-pres-page" href="{{ $presensi->previousPageUrl() }}" aria-label="Halaman sebelumnya">←</a>
                                @endif
                            </li>

                            @if ($awal > 1)
                                <li><a class="adm-pres-page" href="{{ $presensi->url(1) }}">1</a></li>
                                @if ($awal > 2)
                                    <li><span class="adm-pres-page-gap" aria-hidden="true">…</span></li>
                                @endif
                            @endif

                            @foreach ($presensi->getUrlRange($awal, $akhir) as $page => $url)
                                <li>
                                    @if ($page == $halaman)
                                        <span class="adm-pres-page is-active" aria-current="page">{{ $page }}</span>
                                    @else
                                        <a class="adm-pres-page" href="{{ $url }}">{{ $page }}</a>
                                    @endif
                                </li>
                            @endforeach

                            @if ($akhir < $terakhir)
                                @if ($akhir < $terakhir - 1)
                                    <li><span class="adm-pres-page-gap" aria-hidden="true">…</span></li>
                                @endif
                                <li><a class="adm-pres-page" href="{{ $presensi->url($terakhir) }}">{{ $terakhir }}</a></li>
                            @endif

                            <li>
                                @if ($presensi->hasMorePages())
                                    <a class="adm-pres-page" href="{{ $presensi->nextPageUrl() }}" aria-label="Halaman berikutnya">→</a>
                                @else
                                    <span class="adm-pres-page is-disabled" aria-hidden="true">→</span>
                                @endif
                            </li>
                        </ul>
                    </nav>
                @endif
            </div>

        </section>


        {{-- ===== POP-UP ALASAN IZIN (satu modal dipakai semua baris) ===== --}}
        <div class="adm-pres-modal" id="modalAlasan" hidden>
            <div class="adm-pres-modal-backdrop" data-modal-close></div>

            <div class="adm-pres-modal-box" role="dialog" aria-modal="true" aria-labelledby="modalAlasanJudul" tabindex="-1">
                <div class="adm-pres-modal-head">
                    <h2 id="modalAlasanJudul" data-modal-judul>Alasan Izin</h2>
                    <button type="button" class="adm-pres-icon-btn" data-modal-close aria-label="Tutup">
                        <svg class="adm-pres-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="adm-pres-modal-meta">
                    <div>
                        <strong data-modal-nama>-</strong>
                        <span data-modal-tanggal>-</span>
                    </div>
                    <span class="adm-pres-badge adm-pres-badge--izin" data-modal-jenis>Izin</span>
                </div>

                <p class="adm-pres-modal-reason" data-modal-alasan>-</p>

                <button type="button" class="adm-pres-btn adm-pres-btn--light adm-pres-modal-close" data-modal-close>Tutup</button>
            </div>
        </div>

    </div>

@endsection


@push('scripts')
    <script src="{{ asset('js/admin/data-presensi.js') }}"></script>
@endpush