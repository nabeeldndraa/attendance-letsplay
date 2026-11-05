@extends('layouts.admin')
@section('title', 'Edit User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/data-user-edit.css') }}">
@endpush

@section('content')
@php
    /*
     * TODO (samakan dengan form edit LAMA milik backend):
     *  - nama route update  : admin.data-user.update
     *  - method HTTP        : PUT (ganti ke PATCH kalau route-nya memakai PATCH)
     * Nama field mengikuti yang dipakai halaman register / data user:
     *  nama, email, no_wa, asal_instansi, bidang_magang, tanggal_mulai, tanggal_selesai, status_akun
     */
    $routeUpdate  = 'admin.data-user.update';
    $methodUpdate = 'PUT';

    $formAction = \Illuminate\Support\Facades\Route::has($routeUpdate)
        ? route($routeUpdate, $user->id)
        : '#';

    // Nilai awal: old() (setelah validasi gagal) lebih diutamakan daripada data tersimpan
    $nilaiMulai   = old('tanggal_mulai',   $user->tanggal_mulai?->format('Y-m-d'));
    $nilaiSelesai = old('tanggal_selesai', $user->tanggal_selesai?->format('Y-m-d'));
    $nilaiStatus  = old('status_akun', $user->status_akun);
    $statusAktif  = $nilaiStatus === 'aktif';
    $nilaiBidang  = old('bidang_magang', $user->bidang_magang);

    /*
     * Saran pilihan Divisi / Posisi Magang diambil dari data peserta.
     * Kalau controller mengirim $daftarBidang, otomatis dipakai.
     * Pengguna tetap bisa mengetik nilai lain.
     */
    $opsiBidang = collect($daftarBidang ?? \App\Models\User::where('role', 'user')
            ->whereNotNull('bidang_magang')->where('bidang_magang', '!=', '')
            ->distinct()->pluck('bidang_magang'))
        ->push($user->bidang_magang)
        ->filter()->unique()->sort()->values();
@endphp

{{-- Ikon khusus halaman ini (prefix p-admedit-) --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="p-admedit-userplus" viewBox="0 0 24 24"><circle cx="9" cy="7.5" r="4" fill="currentColor"/><path d="M1.5 20c0-3.800 3.300-6 7.500-6s7.500 2.200 7.500 6Z" fill="currentColor"/><path d="M19 7v6M16 10h6" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" fill="none"/></symbol>
    <symbol id="p-admedit-arrow" viewBox="0 0 24 24"><path d="M20 12H5M11 6l-6 6 6 6"/></symbol>
    <symbol id="p-admedit-save" viewBox="0 0 24 24"><path fill="currentColor" d="M5 3h11l4 4v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 2v5h8V5H7Zm5 8a2.500 2.500 0 1 0 0 5 2.500 2.500 0 0 0 0-5Z"/></symbol>
</svg>

<div class="adm-edit">

    {{-- ===== JUDUL ===== --}}
    <div class="adm-edit-head">
        <h1>Edit User</h1>
        <p>Ubah informasi peserta magang.</p>
    </div>


    {{-- ===== FORM ===== --}}
    <form action="{{ $formAction }}" method="POST" class="adm-edit-card" id="adm-edit-form">
        @csrf
        @method($methodUpdate)

        <header class="adm-edit-card-head">
            <svg aria-hidden="true"><use href="#p-admedit-userplus"/></svg>
            <h2>Informasi User</h2>
        </header>

        @if ($errors->any())
            <div class="adm-edit-alert" role="alert">
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

        <div class="adm-edit-grid">

            {{-- Nama --}}
            <div class="adm-edit-field">
                <label for="nama">Nama Lengkap <i>*</i></label>
                <input type="text" id="nama" name="nama" required autocomplete="name"
                       class="adm-edit-input @error('nama') has-error @enderror"
                       value="{{ old('nama', $user->nama) }}">
                @error('nama') <p class="adm-edit-error">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div class="adm-edit-field">
                <label for="email">Email <i>*</i></label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       class="adm-edit-input @error('email') has-error @enderror"
                       value="{{ old('email', $user->email) }}">
                @error('email') <p class="adm-edit-error">{{ $message }}</p> @enderror
            </div>

            {{-- No. Telepon --}}
            <div class="adm-edit-field">
                <label for="no_wa">No. Telepon <i>*</i></label>
                <input type="tel" id="no_wa" name="no_wa" required autocomplete="tel"
                       class="adm-edit-input @error('no_wa') has-error @enderror"
                       value="{{ old('no_wa', $user->no_wa) }}">
                @error('no_wa') <p class="adm-edit-error">{{ $message }}</p> @enderror
            </div>

            {{-- Instansi --}}
            <div class="adm-edit-field">
                <label for="asal_instansi">Instansi <i>*</i></label>
                <input type="text" id="asal_instansi" name="asal_instansi" required
                       class="adm-edit-input @error('asal_instansi') has-error @enderror"
                       value="{{ old('asal_instansi', $user->asal_instansi) }}">
                @error('asal_instansi') <p class="adm-edit-error">{{ $message }}</p> @enderror
            </div>

            {{-- Divisi / Posisi Magang (pilih dari daftar atau ketik sendiri) --}}
            <div class="adm-edit-field">
                <label for="bidang_magang">Divisi / Posisi Magang <i>*</i></label>

                <div class="adm-edit-combo" data-combo>
                    <input type="text" id="bidang_magang" name="bidang_magang" required autocomplete="off"
                           role="combobox" aria-expanded="false" aria-controls="adm-edit-bidang-list"
                           class="adm-edit-input adm-edit-combo-input @error('bidang_magang') has-error @enderror"
                           value="{{ $nilaiBidang }}">

                    <button type="button" class="adm-edit-combo-toggle" aria-label="Tampilkan pilihan">
                        <svg aria-hidden="true"><use href="#i-chevron-down"/></svg>
                    </button>

                    <ul class="adm-edit-combo-list" id="adm-edit-bidang-list" role="listbox" hidden>
                        @foreach ($opsiBidang as $opsi)
                            <li><button type="button" role="option" data-value="{{ $opsi }}" class="adm-edit-option">{{ $opsi }}</button></li>
                        @endforeach
                        <li class="adm-edit-combo-empty" hidden>Tidak ada pilihan yang cocok. Nilai yang kamu ketik akan dipakai.</li>
                    </ul>
                </div>
                @error('bidang_magang') <p class="adm-edit-error">{{ $message }}</p> @enderror
            </div>

            {{-- Tanggal masuk & selesai --}}
            <div class="adm-edit-dates">

                <div class="adm-edit-field">
                    <label for="tanggal_mulai_view">Tanggal Masuk Magang <i>*</i></label>

                    <div class="adm-edit-date" data-date>
                        <input type="text" id="tanggal_mulai_view" class="adm-edit-input adm-edit-date-view @error('tanggal_mulai') has-error @enderror"
                               placeholder="Pilih tanggal" autocomplete="off" inputmode="none" required>
                        <input type="hidden" id="tanggal_mulai" name="tanggal_mulai" value="{{ $nilaiMulai }}">

                        <button type="button" class="adm-edit-date-btn" aria-label="Buka kalender">
                            <svg aria-hidden="true"><use href="#i-calendar"/></svg>
                        </button>

                        <div class="adm-edit-cal" role="dialog" aria-label="Pilih tanggal masuk" hidden></div>
                    </div>
                    @error('tanggal_mulai') <p class="adm-edit-error">{{ $message }}</p> @enderror
                </div>

                <div class="adm-edit-field">
                    <label for="tanggal_selesai_view">Tanggal Selesai Magang <i>*</i></label>

                    <div class="adm-edit-date adm-edit-date--right" data-date data-min-input="tanggal_mulai">
                        <input type="text" id="tanggal_selesai_view" class="adm-edit-input adm-edit-date-view @error('tanggal_selesai') has-error @enderror"
                               placeholder="Pilih tanggal" autocomplete="off" inputmode="none" required>
                        <input type="hidden" id="tanggal_selesai" name="tanggal_selesai" value="{{ $nilaiSelesai }}">

                        <button type="button" class="adm-edit-date-btn" aria-label="Buka kalender">
                            <svg aria-hidden="true"><use href="#i-calendar"/></svg>
                        </button>

                        <div class="adm-edit-cal" role="dialog" aria-label="Pilih tanggal selesai" hidden></div>
                    </div>
                    @error('tanggal_selesai') <p class="adm-edit-error">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- Status akun --}}
            <div class="adm-edit-field adm-edit-field--full">
                <span class="adm-edit-label">Status Akun</span>

                {{-- nilai "nonaktif" dikirim kalau toggle dimatikan; "aktif" menimpanya kalau dinyalakan --}}
                <input type="hidden" name="status_akun" value="nonaktif">

                <label class="adm-edit-switch">
                    <input type="checkbox" name="status_akun" value="aktif" {{ $statusAktif ? 'checked' : '' }}>
                    <span class="adm-edit-switch-track" aria-hidden="true"></span>
                    <span class="adm-edit-switch-text" data-on="Aktif" data-off="Nonaktif">{{ $statusAktif ? 'Aktif' : 'Nonaktif' }}</span>
                </label>
                @error('status_akun') <p class="adm-edit-error">{{ $message }}</p> @enderror
            </div>

        </div>

        {{-- Tombol --}}
        <footer class="adm-edit-actions">
            <a href="{{ route('admin.data-user') }}" class="adm-edit-btn adm-edit-btn--cancel">
                <svg aria-hidden="true"><use href="#p-admedit-arrow"/></svg>
                Batal
            </a>

            <button type="submit" class="adm-edit-btn adm-edit-btn--save">
                <svg aria-hidden="true"><use href="#p-admedit-save"/></svg>
                Simpan Perubahan
            </button>
        </footer>

    </form>

</div>

{{-- ===== POP-UP KONFIRMASI (simpan / tinggalkan halaman) ===== --}}
<div class="adm-edit-modal" id="adm-edit-modal" hidden>
    <div class="adm-edit-modal-backdrop" data-modal-close></div>

    <div class="adm-edit-modal-box" role="dialog" aria-modal="true" aria-labelledby="adm-edit-modal-title">
        <span class="adm-edit-modal-icon">
            <svg class="adm-edit-modal-icon--save" aria-hidden="true"><use href="#s-ok"/></svg>
            <svg class="adm-edit-modal-icon--leave" aria-hidden="true"><use href="#s-alert"/></svg>
        </span>

        <h3 id="adm-edit-modal-title" class="adm-edit-modal-title"></h3>
        <p class="adm-edit-modal-text"></p>

        <div class="adm-edit-modal-actions">
            <button type="button" class="adm-edit-btn adm-edit-btn--outline" id="adm-edit-modal-cancel">Batal</button>
            <button type="button" class="adm-edit-btn adm-edit-btn--save" id="adm-edit-modal-confirm">Ya</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/data-user-edit.js') }}"></script>
@endpush