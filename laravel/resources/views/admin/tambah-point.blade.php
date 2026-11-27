{{--
    HALAMAN ADMIN: TAMBAH POINT USER
    Admin memilih satu / beberapa peserta magang lalu memberi poin tambahan
    (mis. karena ikut event tertentu).

    Data dari controller (opsional, backend TIDAK diubah):
      $interns : koleksi peserta AKTIF. Tiap item: id, nama, email, instansi, bidang, poin (opsional)
    Route    : admin.tambah-point        (GET, halaman ini)
               admin.tambah-point.store  (POST, simpan; dicek dengan Route::has)
    Field POST: user_ids[] (array id peserta), poin (integer >= 1), keterangan (opsional, maks 120 huruf)

    Selama controller belum mengirim $interns, halaman memakai DATA DUMMY (blok di bawah).
    Selama route simpan belum ada, form berjalan dalam MODE TAMPILAN (tidak mengirim apa pun).
--}}
@extends('layouts.admin')
@section('title', 'Tambah Point User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-tambah-point.css') }}">
@endpush

@section('content')
    @php
        /*
         * TODO (backend): hapus blok dummy ini begitu controller mengirim $interns.
         */
        if (! isset($interns)) {
            $interns = collect([
                ['filah',   'nafilah@gmail.com',              'poltek',        'video grafer',  120],
                ['ikan',    'ikan@gmail.com',                 'ikan',          'programming',    85],
                ['mrt',     'martarinarachminafilah@gmail.com','poltek jember', 'UI/UX Designer', 240],
                ['Budi Santoso',  'budi.santoso@gmail.com',   'Universitas Brawijaya', 'programming',   60],
                ['Citra Lestari', 'citra.lestari@gmail.com',  'Politeknik Malang',     'UI/UX Designer', 95],
                ['Dimas Prakoso', 'dimas.prakoso@gmail.com',  'Universitas Jember',    'video grafer',   40],
                ['Eka Putri',     'eka.putri@gmail.com',      'Telkom University',     'social media',  150],
                ['Fajar Nugroho', 'fajar.nugroho@gmail.com',  'Universitas Brawijaya', 'programming',   10],
                ['Gita Permata',  'gita.permata@gmail.com',   'Politeknik Negeri Jember','social media', 75],
                ['Hendra Wijaya', 'hendra.wijaya@gmail.com',  'Universitas Negeri Malang','video grafer',  0],
            ])->map(fn ($d, $i) => (object) [
                'id' => $i + 1, 'nama' => $d[0], 'email' => $d[1],
                'instansi' => $d[2], 'bidang' => $d[3], 'poin' => $d[4],
            ]);
        }

        $totalPeserta = $interns->count();
        $adaStore     = \Illuminate\Support\Facades\Route::has('admin.tambah-point.store');
        $idTerpilih   = collect(old('user_ids', []))->map(fn ($v) => (string) $v)->all();
    @endphp

    {{-- ikon khusus halaman ini --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <symbol id="tp-tick" viewBox="0 0 24 24"><path d="m5 12.500 4.500 4.500L19 7.500"/></symbol>
    </svg>

    <x-page-hero title="Tambah Point User"
                 subtitle="Beri poin tambahan kepada peserta magang yang mengikuti event tertentu."
                 :back="route('admin.dashboard')" />

    @if (session('success'))
        <div class="tp-alert tp-alert--ok" role="status">{{ session('success') }}</div>
    @endif
    <div id="tpDemo" class="tp-alert tp-alert--ok" role="status" hidden></div>

    {{-- ===== Statistik ===== --}}
    <div class="tp-stats">
        <div class="tp-stat">
            <span class="tp-stat-label">Peserta aktif</span>
            <strong class="tp-stat-num">{{ $totalPeserta }}</strong>
        </div>
        <div class="tp-stat">
            <span class="tp-stat-label">Peserta dipilih</span>
            <strong class="tp-stat-num" id="tpStatDipilih">0</strong>
        </div>
        <div class="tp-stat">
            <span class="tp-stat-label">Total poin yang dibagikan</span>
            <strong class="tp-stat-num" id="tpStatTotal">0</strong>
        </div>
    </div>

    <form method="POST" action="{{ $adaStore ? route('admin.tambah-point.store') : '#' }}"
          id="tpForm" class="tp-layout" novalidate
          @if (! $adaStore) data-demo="1" @endif>
        @csrf

        {{-- ===== KIRI: pilih peserta ===== --}}
        <section class="tp-card tp-pick" aria-labelledby="tpJudulPilih">
            <div class="tp-pick-head">
                <h2 id="tpJudulPilih" class="tp-title">1. Pilih Peserta</h2>
                <button type="button" class="tp-jump" id="tpJump" hidden>Lanjut beri poin ↓</button>
            </div>

            <label class="tp-search">
                <svg class="ico" aria-hidden="true"><use href="#i-search"/></svg>
                <input type="search" id="tpCari" autocomplete="off"
                       placeholder="Cari nama, email, instansi..." aria-label="Cari peserta">
            </label>

            <div class="tp-bar">
                <label class="tp-check-all">
                    <input type="checkbox" id="tpSemua">
                    <span class="tp-box" aria-hidden="true"><svg class="ico"><use href="#tp-tick"/></svg></span>
                    <span>Pilih semua yang tampil</span>
                </label>
                <span class="tp-count" id="tpCount">Menampilkan {{ $totalPeserta }} dari {{ $totalPeserta }} peserta</span>
            </div>

            <div class="tp-table" role="group" aria-label="Daftar peserta">
                <div class="tp-thead" aria-hidden="true">
                    <span></span>
                    <span>Peserta</span>
                    <span class="tp-col-ins">Instansi</span>
                    <span class="tp-col-bid">Bidang Magang</span>
                    <span class="tp-col-poin">Poin Saat Ini</span>
                </div>

                <div class="tp-scroll" id="tpScroll">
                    @foreach ($interns as $u)
                        <label class="tp-row"
                               data-cari="{{ \Illuminate\Support\Str::lower($u->nama.' '.$u->email.' '.$u->instansi.' '.$u->bidang) }}">
                            <input type="checkbox" class="tp-check" name="user_ids[]" value="{{ $u->id }}"
                                   data-nama="{{ $u->nama }}" @checked(in_array((string) $u->id, $idTerpilih, true))>
                            <span class="tp-box" aria-hidden="true"><svg class="ico"><use href="#tp-tick"/></svg></span>

                            <span class="tp-person">
                                <span class="tp-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u->nama, 0, 1)) }}</span>
                                <span class="tp-person-text">
                                    <strong>{{ $u->nama }}</strong>
                                    <small>{{ $u->email }}</small>
                                    <small class="tp-meta">{{ $u->instansi }} · {{ $u->bidang }}</small>
                                </span>
                            </span>

                            <span class="tp-col-ins">{{ $u->instansi }}</span>
                            <span class="tp-col-bid">{{ $u->bidang }}</span>
                            <span class="tp-col-poin">
                                @if (isset($u->poin))
                                    <b>{{ number_format($u->poin, 0, ',', '.') }}</b> poin
                                @else
                                    –
                                @endif
                            </span>
                        </label>
                    @endforeach

                    <div class="tp-empty" id="tpEmpty" hidden>
                        <svg class="ico" aria-hidden="true"><use href="#i-search"/></svg>
                        <p>Tidak ada peserta yang cocok dengan pencarian.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== KANAN: isi poin ===== --}}
        <aside class="tp-card tp-give" id="tpGive" aria-labelledby="tpJudulBeri">
            <h2 id="tpJudulBeri" class="tp-title">2. Beri Poin</h2>

            <div class="tp-field">
                <span class="tp-label">Peserta dipilih</span>
                <p class="tp-chips-empty" id="tpChipsEmpty">Belum ada peserta dipilih.</p>
                <ul class="tp-chips" id="tpChips" aria-live="polite"></ul>
                <p class="tp-error" id="tpErrPeserta" role="alert">@error('user_ids'){{ $message }}@enderror</p>
            </div>

            <div class="tp-field">
                <label class="tp-label" for="tpPoin">Jumlah poin per peserta</label>
                <input type="number" id="tpPoin" name="poin" min="1" step="1" inputmode="numeric"
                       value="{{ old('poin') }}" placeholder="Contoh: 50"
                       class="tp-input {{ $errors->has('poin') ? 'is-invalid' : '' }}">
                <div class="tp-presets" role="group" aria-label="Pilihan cepat">
                    @foreach ([5, 10, 25, 50, 100] as $p)
                        <button type="button" class="tp-preset" data-poin="{{ $p }}">+{{ $p }}</button>
                    @endforeach
                </div>
                <p class="tp-error" id="tpErrPoin" role="alert">@error('poin'){{ $message }}@enderror</p>
            </div>

            <div class="tp-field">
                <label class="tp-label" for="tpKet">Keterangan / nama event </label>
                <input type="text" id="tpKet" name="keterangan" maxlength="120" class="tp-input"
                       value="{{ old('keterangan') }}" placeholder="Contoh: Workshop Desain 12 Nov">
            </div>

            <div class="tp-summary" id="tpSummary">Pilih peserta dan isi jumlah poin.</div>

            <button type="submit" class="tp-btn tp-btn--full" id="tpKirim">
                <svg class="ico" aria-hidden="true"><use href="#i-star"/></svg>
                Beri Poin
            </button>
        </aside>
    </form>

    {{-- ===== Dialog konfirmasi ===== --}}
    <dialog class="tp-dialog" id="tpConfirm" aria-labelledby="tpConfirmJudul">
        <h3 id="tpConfirmJudul">Konfirmasi pemberian poin</h3>
        <p id="tpConfirmText"></p>
        <div class="tp-dialog-actions">
            <button type="button" class="tp-btn tp-btn--ghost" id="tpBatal">Batal</button>
            <button type="button" class="tp-btn" id="tpYa">Ya, beri poin</button>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-tambah-point.js') }}"></script>
@endpush
