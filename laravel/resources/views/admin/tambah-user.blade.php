@extends('layouts.admin')
@section('title', 'Tambah User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/data-user-tambah.css') }}">
@endpush

@section('content')
@php
    /*
     * TODO (samakan dengan route backend):
     *  - nama route simpan : admin.data-user.store   (method POST)
     * Nama field mengikuti AdminUserController@store:
     *  nama, email, no_wa, asal_instansi, bidang_magang, tanggal_mulai, tanggal_selesai,
     *  password, password_confirmation
     * Role ("user") dan status akun ("aktif") diisi otomatis oleh backend.
     */
    $routeStore = 'admin.data-user.store';

    $formAction = \Illuminate\Support\Facades\Route::has($routeStore) ? route($routeStore) : '#';

    /*
     * TODO (backend) - UPLOAD EXCEL (belum ada di controller, jadi ini baru tampilan):
     *  - route upload     : admin.data-user.import  (method POST, multipart/form-data)
     *  - nama field file  : file   (.xlsx / .xls / .csv, maks. 2 MB)
     *  - template Excel   : public/templates/template-tambah-user.xlsx
     *  - kolom (baris 1)  : nama, email, no_wa, asal_instansi, bidang_magang,
     *                       tanggal_mulai, tanggal_selesai, password
     *  - kalau ada baris yang gagal, kirim session('import_errors') berupa daftar
     *    ['baris' => 3, 'pesan' => '...'] (atau teks biasa); sukses lewat session('success').
     */
    $routeImport  = 'admin.data-user.import';
    $importAction = \Illuminate\Support\Facades\Route::has($routeImport) ? route($routeImport) : '#';
    $templateUrl  = asset('templates/template-tambah-user.xlsx');
    $importErrors = session('import_errors');
    $maxMb        = 2;

    // Mode awal: manual. Kalau upload gagal (ada error file / baris gagal), buka mode Excel.
    $modeAwal = ($errors->has('file') || !empty($importErrors)) ? 'excel' : 'manual';

    $nilaiMulai   = old('tanggal_mulai');
    $nilaiSelesai = old('tanggal_selesai');
    $nilaiBidang  = old('bidang_magang');

    /*
     * Saran pilihan Divisi / Posisi Magang diambil dari data peserta.
     * Kalau controller mengirim $daftarBidang, otomatis dipakai.
     * Pengguna tetap bisa mengetik nilai lain.
     */
    $opsiBidang = collect($daftarBidang ?? \App\Models\User::where('role', 'user')
            ->whereNotNull('bidang_magang')->where('bidang_magang', '!=', '')
            ->distinct()->pluck('bidang_magang'))
        ->filter()->unique()->sort()->values();
@endphp

{{-- Ikon khusus halaman ini (prefix p-admadd-) --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="p-admadd-userplus" viewBox="0 0 24 24"><circle cx="9" cy="7.5" r="4" fill="currentColor"/><path d="M1.5 20c0-3.800 3.300-6 7.500-6s7.500 2.200 7.500 6Z" fill="currentColor"/><path d="M19 7v6M16 10h6" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" fill="none"/></symbol>
    <symbol id="p-admadd-arrow" viewBox="0 0 24 24"><path d="M20 12H5M11 6l-6 6 6 6"/></symbol>
 <symbol id="p-admadd-upload" viewBox="0 0 24 24"><path d="M7 18a4.500 4.500 0 0 1-.600-8.960A6 6 0 0 1 18 8.500a4 4 0 0 1 .500 7.970"/><path d="M12 12v8M8.500 15.500 12 12l3.500 3.500"/></symbol>
    <symbol id="p-admadd-sheet" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/><path d="M14 3v5h5M8.500 12.500h7M8.500 16h7M12 12.500V19"/></symbol>
    <symbol id="p-admadd-download" viewBox="0 0 24 24"><path d="M12 4v11M7.500 11 12 15.500 16.500 11M5 19.500h14"/></symbol>
    <symbol id="p-admadd-save" viewBox="0 0 24 24"><path fill="currentColor" d="M5 3h11l4 4v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 2v5h8V5H7Zm5 8a2.500 2.500 0 1 0 0 5 2.500 2.500 0 0 0 0-5Z"/></symbol>
</svg>

<div class="adm-add">

    {{-- ===== JUDUL ===== --}}
    <div class="adm-add-head">
        <h1>Tambah User</h1>
        <p>Tambahkan akun peserta magang baru.</p>
    </div>


    {{-- ===== PILIH CARA TAMBAH USER ===== --}}
    <div class="adm-add-modes" role="radiogroup" aria-label="Cara menambahkan user">
        <label class="adm-add-mode {{ $modeAwal === 'manual' ? 'is-active' : '' }}">
            <input type="radio" name="adm_mode" value="manual" {{ $modeAwal === 'manual' ? 'checked' : '' }}>
            <span class="adm-add-mode-ico"><svg aria-hidden="true"><use href="#p-admadd-userplus"/></svg></span>
            <span class="adm-add-mode-text">
                <strong>Input Manual</strong>
                <small>Isi data satu peserta lewat formulir.</small>
            </span>
            <span class="adm-add-mode-dot" aria-hidden="true"></span>
        </label>

        <label class="adm-add-mode {{ $modeAwal === 'excel' ? 'is-active' : '' }}">
            <input type="radio" name="adm_mode" value="excel" {{ $modeAwal === 'excel' ? 'checked' : '' }}>
            <span class="adm-add-mode-ico"><svg class="is-line" aria-hidden="true"><use href="#p-admadd-sheet"/></svg></span>
            <span class="adm-add-mode-text">
                <strong>Upload Excel</strong>
                <small>Tambahkan banyak peserta sekaligus dari file.</small>
            </span>
            <span class="adm-add-mode-dot" aria-hidden="true"></span>
        </label>
    </div>


    {{-- ===== PANEL 1: INPUT MANUAL ===== --}}
    <div class="adm-add-panel {{ $modeAwal === 'manual' ? '' : 'is-disabled' }}" data-panel="manual"
         @if ($modeAwal !== 'manual') inert aria-disabled="true" @endif>

    <form action="{{ $formAction }}" method="POST" class="adm-add-card" id="adm-add-form">
        @csrf

        <header class="adm-add-card-head">
            <svg aria-hidden="true"><use href="#p-admadd-userplus"/></svg>
            <h2>Informasi User</h2>
            <span class="adm-add-collapsed-note">Pilih &ldquo;Input Manual&rdquo; di atas untuk membuka formulir</span>
        </header>

        <div class="adm-add-collapse"><div class="adm-add-collapse-inner">

        @if ($errors->any())
            <div class="adm-add-alert" role="alert">
                <svg aria-hidden="true"><use href="#s-alert"/></svg>
                <div>
                    <strong>Periksa kembali isian kamu:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="adm-add-grid">

            {{-- Nama --}}
            <div class="adm-add-field">
                <label for="nama">Nama Lengkap <i>*</i></label>
                <input type="text" id="nama" name="nama" required autocomplete="off"
                       placeholder="Masukkan nama lengkap"
                       class="adm-add-input @error('nama') has-error @enderror"
                       value="{{ old('nama') }}">
                @error('nama') <p class="adm-add-error">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div class="adm-add-field">
                <label for="email">Email <i>*</i></label>
                <input type="email" id="email" name="email" required autocomplete="off"
                       placeholder="contoh@gmail.com"
                       class="adm-add-input @error('email') has-error @enderror"
                       value="{{ old('email') }}">
                @error('email') <p class="adm-add-error">{{ $message }}</p> @enderror
            </div>

            {{-- No. Telepon --}}
            <div class="adm-add-field">
                <label for="no_wa">No. Telepon <i>*</i></label>
                <input type="tel" id="no_wa" name="no_wa" required autocomplete="off"
                       placeholder="08xxxxxxxxxx"
                       class="adm-add-input @error('no_wa') has-error @enderror"
                       value="{{ old('no_wa') }}">
                @error('no_wa') <p class="adm-add-error">{{ $message }}</p> @enderror
            </div>

            {{-- Instansi --}}
            <div class="adm-add-field">
                <label for="asal_instansi">Instansi <i>*</i></label>
                <input type="text" id="asal_instansi" name="asal_instansi" required autocomplete="off"
                       placeholder="Contoh: Politeknik Negeri Jember"
                       class="adm-add-input @error('asal_instansi') has-error @enderror"
                       value="{{ old('asal_instansi') }}">
                @error('asal_instansi') <p class="adm-add-error">{{ $message }}</p> @enderror
            </div>

            {{-- Divisi / Posisi Magang (pilih dari daftar atau ketik sendiri) --}}
            <div class="adm-add-field">
                <label for="bidang_magang">Divisi / Posisi Magang <i>*</i></label>

                <div class="adm-add-combo" data-combo>
                    <input type="text" id="bidang_magang" name="bidang_magang" required autocomplete="off"
                           role="combobox" aria-expanded="false" aria-controls="adm-add-bidang-list"
                           placeholder="Pilih atau ketik posisi"
                           class="adm-add-input adm-add-combo-input @error('bidang_magang') has-error @enderror"
                           value="{{ $nilaiBidang }}">

                    <button type="button" class="adm-add-combo-toggle" aria-label="Tampilkan pilihan">
                        <svg aria-hidden="true"><use href="#i-chevron-down"/></svg>
                    </button>

                    <ul class="adm-add-combo-list" id="adm-add-bidang-list" role="listbox" hidden>
                        @foreach ($opsiBidang as $opsi)
                            <li><button type="button" role="option" data-value="{{ $opsi }}" class="adm-add-option">{{ $opsi }}</button></li>
                        @endforeach
                        <li class="adm-add-combo-empty" hidden>Tidak ada pilihan yang cocok. Nilai yang kamu ketik akan dipakai.</li>
                    </ul>
                </div>
                @error('bidang_magang') <p class="adm-add-error">{{ $message }}</p> @enderror
            </div>

            {{-- Tanggal masuk & selesai --}}
            <div class="adm-add-dates">

                <div class="adm-add-field">
                    <label for="tanggal_mulai_view">Tanggal Masuk Magang <i>*</i></label>

                    <div class="adm-add-date" data-date>
                        <input type="text" id="tanggal_mulai_view" class="adm-add-input adm-add-date-view @error('tanggal_mulai') has-error @enderror"
                               placeholder="Pilih tanggal" autocomplete="off" inputmode="none" required>
                        <input type="hidden" id="tanggal_mulai" name="tanggal_mulai" value="{{ $nilaiMulai }}">

                        <button type="button" class="adm-add-date-btn" aria-label="Buka kalender">
                            <svg aria-hidden="true"><use href="#i-calendar"/></svg>
                        </button>

                        <div class="adm-add-cal" role="dialog" aria-label="Pilih tanggal masuk" hidden></div>
                    </div>
                    @error('tanggal_mulai') <p class="adm-add-error">{{ $message }}</p> @enderror
                </div>

                <div class="adm-add-field">
                    <label for="tanggal_selesai_view">Tanggal Selesai Magang <i>*</i></label>

                    <div class="adm-add-date adm-add-date--right" data-date data-min-input="tanggal_mulai">
                        <input type="text" id="tanggal_selesai_view" class="adm-add-input adm-add-date-view @error('tanggal_selesai') has-error @enderror"
                               placeholder="Pilih tanggal" autocomplete="off" inputmode="none" required>
                        <input type="hidden" id="tanggal_selesai" name="tanggal_selesai" value="{{ $nilaiSelesai }}">

                        <button type="button" class="adm-add-date-btn" aria-label="Buka kalender">
                            <svg aria-hidden="true"><use href="#i-calendar"/></svg>
                        </button>

                        <div class="adm-add-cal" role="dialog" aria-label="Pilih tanggal selesai" hidden></div>
                    </div>
                    @error('tanggal_selesai') <p class="adm-add-error">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- Password --}}
            <div class="adm-add-field">
                <label for="password">Password <i>*</i></label>

                <div class="adm-add-pass">
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"
                           placeholder="Minimal 8 karakter"
                           class="adm-add-input adm-add-pass-input @error('password') has-error @enderror">

                    <button type="button" class="adm-add-pass-toggle" data-pass-toggle
                            aria-label="Tampilkan password" aria-pressed="false">
                        <svg class="is-on" aria-hidden="true"><use href="#i-eye"/></svg>
                        <svg class="is-off" aria-hidden="true"><use href="#i-eye-off"/></svg>
                    </button>
                </div>

                @error('password')
                    <p class="adm-add-error">{{ $message }}</p>
                @else
                    <p class="adm-add-hint">Gunakan minimal 8 karakter.</p>
                @enderror
            </div>

            {{-- Konfirmasi password --}}
            <div class="adm-add-field">
                <label for="password_confirmation">Konfirmasi Password <i>*</i></label>

                <div class="adm-add-pass">
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                           autocomplete="new-password" placeholder="Ulangi password"
                           class="adm-add-input adm-add-pass-input">

                    <button type="button" class="adm-add-pass-toggle" data-pass-toggle
                            aria-label="Tampilkan password" aria-pressed="false">
                        <svg class="is-on" aria-hidden="true"><use href="#i-eye"/></svg>
                        <svg class="is-off" aria-hidden="true"><use href="#i-eye-off"/></svg>
                    </button>
                </div>

                <p class="adm-add-error" data-confirm-error hidden>Konfirmasi password tidak sama dengan password.</p>
            </div>

            {{-- Catatan status akun --}}
            <div class="adm-add-field adm-add-field--full">
                <div class="adm-add-note">
                    <svg aria-hidden="true"><use href="#i-info"/></svg>
                    <span>Akun baru otomatis berstatus <strong>Aktif</strong> dan langsung bisa dipakai untuk login.</span>
                </div>
            </div>

        </div>

        {{-- Tombol --}}
        <footer class="adm-add-actions">
            <a href="{{ route('admin.data-user') }}" class="adm-add-btn adm-add-btn--cancel">
                <svg aria-hidden="true"><use href="#p-admadd-arrow"/></svg>
                Batal
            </a>

            <button type="submit" class="adm-add-btn adm-add-btn--save">
                <svg aria-hidden="true"><use href="#p-admadd-save"/></svg>
                Simpan User
            </button>
        </footer>

        </div></div>{{-- /collapse manual --}}

    </form>

    </div>{{-- /panel manual --}}


    {{-- ===== PANEL 2: UPLOAD EXCEL ===== --}}
    <div class="adm-add-panel {{ $modeAwal === 'excel' ? '' : 'is-disabled' }}" data-panel="excel"
         @if ($modeAwal !== 'excel') inert aria-disabled="true" @endif>

        <form action="{{ $importAction }}" method="POST" enctype="multipart/form-data"
              class="adm-add-card" id="adm-add-upload-form">
            @csrf

            <header class="adm-add-card-head">
                <svg class="is-line" aria-hidden="true"><use href="#p-admadd-sheet"/></svg>
                <h2>Upload File Excel</h2>
                <span class="adm-add-collapsed-note">Pilih &ldquo;Upload Excel&rdquo; di atas untuk membuka formulir</span>
            </header>

            <div class="adm-add-collapse"><div class="adm-add-collapse-inner">

            {{-- Baris yang gagal diimpor (dikirim backend lewat session) --}}
            @if (!empty($importErrors))
                <div class="adm-add-alert" role="alert">
                    <svg aria-hidden="true"><use href="#s-alert"/></svg>
                    <div>
                        <strong>Beberapa baris tidak dapat diimpor:</strong>
                        <ul>
                            @foreach ($importErrors as $err)
                                <li>
                                    @if (is_array($err))
                                        @if (isset($err['baris'])) <b>Baris {{ $err['baris'] }}:</b> @endif
                                        {{ $err['pesan'] ?? '' }}
                                    @else
                                        {{ $err }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Langkah-langkah --}}
            <ol class="adm-add-steps">
                <li>
                    <span class="adm-add-step-no">1</span>
                    <div>
                        <strong>Unduh template</strong>
                        <p>Gunakan template agar nama kolomnya sesuai.</p>
                    </div>
                </li>
                <li>
                    <span class="adm-add-step-no">2</span>
                    <div>
                        <strong>Isi data peserta</strong>
                        <p>Satu baris untuk satu peserta, mulai dari baris ke-2.</p>
                    </div>
                </li>
                <li>
                    <span class="adm-add-step-no">3</span>
                    <div>
                        <strong>Upload file</strong>
                        <p>Pilih file yang sudah diisi, lalu klik Upload.</p>
                    </div>
                </li>
            </ol>

            <div class="adm-add-template">
                <div class="adm-add-template-info">
                    <strong>Kolom yang wajib ada</strong>
                    <div class="adm-add-chips">
                        @foreach (['nama', 'email', 'no_wa', 'asal_instansi', 'bidang_magang', 'tanggal_mulai', 'tanggal_selesai', 'password'] as $kolom)
                            <span>{{ $kolom }}</span>
                        @endforeach
                    </div>
                    <p>Tanggal memakai format <b>YYYY-MM-DD</b> (contoh: 2026-11-02). Password minimal 8 karakter.
                       Akun otomatis berstatus Aktif.</p>
                </div>

                <a href="{{ $templateUrl }}" download class="adm-add-btn adm-add-btn--cancel">
                    <svg class="adm-add-btn-ico" aria-hidden="true"><use href="#p-admadd-download"/></svg>
                    Unduh Template
                </a>
            </div>

            {{-- Area upload --}}
            <div class="adm-add-dropzone" data-dropzone data-max-mb="{{ $maxMb }}"
                 role="button" tabindex="0" aria-label="Pilih file Excel">

                <input type="file" name="file" id="adm-add-file" accept=".xlsx,.xls,.csv" hidden>

                <div class="adm-add-dz-empty">
                    <span class="adm-add-dz-ico"><svg aria-hidden="true"><use href="#p-admadd-upload"/></svg></span>
                    <p class="adm-add-dz-title">Tarik file ke sini, atau <u>pilih dari komputer</u></p>
                    <p class="adm-add-dz-sub">Format .xlsx, .xls, atau .csv &middot; maksimal {{ $maxMb }} MB</p>
                </div>

                <div class="adm-add-dz-file" hidden>
                    <span class="adm-add-dz-file-ico"><svg aria-hidden="true"><use href="#p-admadd-sheet"/></svg></span>
                    <div class="adm-add-dz-file-info">
                        <strong class="adm-add-dz-file-name"></strong>
                        <small class="adm-add-dz-file-size"></small>
                    </div>
                    <button type="button" class="adm-add-dz-remove" aria-label="Hapus file">
                        <svg aria-hidden="true"><use href="#i-x"/></svg>
                    </button>
                </div>
            </div>

            <p class="adm-add-error" data-file-error {{ $errors->has('file') ? '' : 'hidden' }}>{{ $errors->first('file') }}</p>

            <footer class="adm-add-actions">
                <a href="{{ route('admin.data-user') }}" class="adm-add-btn adm-add-btn--cancel">
                    <svg aria-hidden="true"><use href="#p-admadd-arrow"/></svg>
                    Batal
                </a>

                <button type="submit" class="adm-add-btn adm-add-btn--save" id="adm-add-upload-btn" disabled>
                    <svg class="is-line" aria-hidden="true"><use href="#p-admadd-upload"/></svg>
                    Upload &amp; Tambahkan
                </button>
            </footer>
            </div></div>{{-- /collapse excel --}}
        </form>

    </div>{{-- /panel excel --}}

</div>


{{-- ===== POP-UP KONFIRMASI (simpan / tinggalkan halaman) ===== --}}
<div class="adm-add-modal" id="adm-add-modal" hidden>
    <div class="adm-add-modal-backdrop" data-modal-close></div>

    <div class="adm-add-modal-box" role="dialog" aria-modal="true" aria-labelledby="adm-add-modal-title">
        <span class="adm-add-modal-icon">
            <svg class="adm-add-modal-icon--save" aria-hidden="true"><use href="#s-ok"/></svg>
            <svg class="adm-add-modal-icon--leave" aria-hidden="true"><use href="#s-alert"/></svg>
        </span>

        <h3 id="adm-add-modal-title" class="adm-add-modal-title"></h3>
        <p class="adm-add-modal-text"></p>

        <div class="adm-add-modal-actions">
            <button type="button" class="adm-add-btn adm-add-btn--outline" id="adm-add-modal-cancel">Batal</button>
            <button type="button" class="adm-add-btn adm-add-btn--save" id="adm-add-modal-confirm">Ya</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/data-user-tambah.js') }}"></script>
@endpush