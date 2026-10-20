@extends('layouts.app')

@section('title', 'Profil')

@php
    $user = auth()->user();

    /*
     * TODO (backend): data profil masih DUMMY (nama, posisi, email dicoba
     * diambil dari user login). Ganti dengan data asli dari controller.
     */
    $profil = [
        'nama'      => $user->nama ?? 'Nabil Zivkolin Danendra',
        'posisi'    => $user->bidang_magang ?? 'UI / UX Designer',
        'email'     => $user->email ?? 'nabiizivkolin@gmail.com',
        'telepon'   => '0812 3456 7890',
        'institusi' => 'Politeknik Negeri Jember',
        'mulai'     => '2026-07-13',
        'selesai'   => '2027-01-13',
    ];
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profil.css') }}">
@endpush


@section('content')

    <div class="prof" id="profilApp" data-view="lihat" data-profil="{{ json_encode($profil) }}">

        <x-page-hero title="Profil Saya"
                     subtitle="Kelola informasi pribadi dan akun kamu di sini"
                     :back="url('/dashboard-test')" />

        <div class="prof-grid">

            {{-- ================= KOLOM KIRI ================= --}}
            <div class="prof-col">

                {{-- Identitas --}}
                <section class="prof-identity" aria-label="Identitas">
                    <span class="prof-avatar" aria-hidden="true">
                        <svg class="prof-avatar__ico"><use href="#i-user"/></svg>
                    </span>

                    <div class="prof-identity__text">
                        <h2 data-bind="nama"></h2>
                        <p data-bind="posisi"></p>
                        <span class="prof-chip">
                            <svg class="prof-ico" aria-hidden="true"><use href="#i-briefcase"/></svg>
                            Internship
                        </span>
                    </div>

                    <button type="button" class="prof-edit-btn" id="btnEdit">
                        <svg class="prof-ico" aria-hidden="true"><use href="#i-edit"/></svg>
                        Edit Profil
                    </button>
                </section>


                <form id="formProfil" class="prof-form" novalidate autocomplete="off">

                    {{-- Informasi pribadi --}}
                    <section class="prof-card">
                        <div class="prof-card__head">
                            <svg class="prof-ico" aria-hidden="true"><use href="#i-user"/></svg>
                            <h2>Informasi Pribadi</h2>
                        </div>

                        <div class="prof-fields">

                            <div class="prof-item" data-field="nama">
                                <label class="prof-field">
                                    <svg class="prof-ico prof-field__ico" aria-hidden="true"><use href="#i-user"/></svg>
                                    <span class="prof-field__body">
                                        <span class="prof-field__label">Nama Lengkap</span>
                                        <input class="prof-field__input" name="nama" type="text" maxlength="60" autocomplete="name" readonly tabindex="-1">
                                    </span>
                                </label>
                                <p class="prof-error" hidden></p>
                            </div>

                            <div class="prof-item" data-field="email">
                                <label class="prof-field">
                                    <svg class="prof-ico prof-field__ico" aria-hidden="true"><use href="#i-mail"/></svg>
                                    <span class="prof-field__body">
                                        <span class="prof-field__label">Email</span>
                                        <input class="prof-field__input" name="email" type="email" maxlength="80" autocomplete="email" readonly tabindex="-1">
                                    </span>
                                </label>
                                <p class="prof-error" hidden></p>
                            </div>

                            <div class="prof-item" data-field="telepon">
                                <label class="prof-field">
                                    <svg class="prof-ico prof-field__ico" aria-hidden="true"><use href="#i-phone"/></svg>
                                    <span class="prof-field__body">
                                        <span class="prof-field__label">Nomor Telepon</span>
                                        <input class="prof-field__input" name="telepon" type="tel" inputmode="tel" maxlength="20" autocomplete="tel" readonly tabindex="-1">
                                    </span>
                                </label>
                                <p class="prof-error" hidden></p>
                            </div>

                            <div class="prof-item" data-field="institusi">
                                <label class="prof-field">
                                    <svg class="prof-ico prof-field__ico" aria-hidden="true"><use href="#i-building"/></svg>
                                    <span class="prof-field__body">
                                        <span class="prof-field__label">Institusi</span>
                                        <input class="prof-field__input" name="institusi" type="text" maxlength="80" readonly tabindex="-1">
                                    </span>
                                </label>
                                <p class="prof-error" hidden></p>
                            </div>

                        </div>
                    </section>


                    {{-- Informasi magang --}}
                    <section class="prof-card">
                        <div class="prof-card__head">
                            <svg class="prof-ico" aria-hidden="true"><use href="#i-briefcase"/></svg>
                            <h2>Informasi Magang</h2>
                        </div>

                        <div class="prof-fields">

                            <div class="prof-item" data-field="posisi">
                                <label class="prof-field">
                                    <svg class="prof-ico prof-field__ico" aria-hidden="true"><use href="#i-user"/></svg>
                                    <span class="prof-field__body">
                                        <span class="prof-field__label">Posisi</span>
                                        <input class="prof-field__input" name="posisi" type="text" maxlength="60" readonly tabindex="-1">
                                    </span>
                                </label>
                                <p class="prof-error" hidden></p>
                            </div>

                            {{-- Mode lihat: satu baris periode --}}
                            <div class="prof-item prof-periode-view">
                                <div class="prof-field">
                                    <svg class="prof-ico prof-field__ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                                    <span class="prof-field__body">
                                        <span class="prof-field__label">Periode</span>
                                        <span class="prof-field__input" id="periodeText"></span>
                                    </span>
                                </div>
                            </div>

                            {{-- Mode edit: dua tanggal (kalender pop-up) --}}
                            <div class="prof-item prof-dates" data-field="tanggal">
                                <div class="prof-dates__row">
                                    <button type="button" class="prof-field prof-field--btn" data-date="mulai" aria-haspopup="dialog">
                                        <svg class="prof-ico prof-field__ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                                        <span class="prof-field__body">
                                            <span class="prof-field__label">Mulai Magang</span>
                                            <span class="prof-field__input" data-date-text="mulai"></span>
                                        </span>
                                    </button>

                                    <button type="button" class="prof-field prof-field--btn" data-date="selesai" aria-haspopup="dialog">
                                        <svg class="prof-ico prof-field__ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                                        <span class="prof-field__body">
                                            <span class="prof-field__label">Selesai Magang</span>
                                            <span class="prof-field__input" data-date-text="selesai"></span>
                                        </span>
                                    </button>
                                </div>
                                <p class="prof-error" hidden></p>
                            </div>

                        </div>
                    </section>


                    <div class="prof-actions">
                        <button type="submit" class="prof-btn prof-btn--primary">Simpan</button>
                        <button type="button" class="prof-btn prof-btn--ghost" id="btnBatalProfil">Batal</button>
                    </div>

                </form>
            </div>


            {{-- ================= KOLOM KANAN ================= --}}
            <div class="prof-side">

                {{-- Menu akun --}}
                <section class="prof-card prof-menu">
                    <div class="prof-card__head">
                        <svg class="prof-ico" aria-hidden="true"><use href="#i-settings"/></svg>
                        <h2>Akun &amp; Pengaturan</h2>
                    </div>

                    <div class="prof-fields">
                        <button type="button" class="prof-row" id="rowPassword">
                            <svg class="prof-ico prof-row__ico" aria-hidden="true"><use href="#i-lock"/></svg>
                            <span>Ubah Password</span>
                            <svg class="prof-ico prof-row__chev" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                        </button>

                        <button type="button" class="prof-row prof-row--danger" id="rowLogout">
                            <svg class="prof-ico prof-row__ico" aria-hidden="true"><use href="#i-logout"/></svg>
                            <span>Keluar</span>
                            <svg class="prof-ico prof-row__chev" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                        </button>
                    </div>
                </section>


                {{-- Ubah password --}}
                <section class="prof-card prof-password" aria-labelledby="passTitle">

                    <div class="prof-card__head prof-only-desktop">
                        <svg class="prof-ico" aria-hidden="true"><use href="#i-settings"/></svg>
                        <h2>Akun &amp; Pengaturan</h2>
                    </div>

                    <h3 class="prof-pass-title" id="passTitle">Ubah Password</h3>

                    <form id="formPassword" novalidate autocomplete="off">

                        <div class="prof-pass-box">

                            <div class="prof-pass-group">
                                <label class="prof-pass-label" for="pwBaru">Password Baru</label>
                                <div class="prof-input">
                                    <svg class="prof-ico prof-input__lock" aria-hidden="true"><use href="#i-lock"/></svg>
                                    <input type="password" id="pwBaru" autocomplete="new-password">
                                    <button type="button" class="prof-input__eye" data-eye aria-label="Tampilkan password" aria-pressed="false">
                                        <svg class="prof-ico" aria-hidden="true"><use href="#i-eye"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="prof-pass-group">
                                <label class="prof-pass-label" for="pwKonfirmasi">Konfirmasi Password Baru</label>
                                <div class="prof-input">
                                    <svg class="prof-ico prof-input__lock" aria-hidden="true"><use href="#i-lock"/></svg>
                                    <input type="password" id="pwKonfirmasi" autocomplete="new-password">
                                    <button type="button" class="prof-input__eye" data-eye aria-label="Tampilkan password" aria-pressed="false">
                                        <svg class="prof-ico" aria-hidden="true"><use href="#i-eye"/></svg>
                                    </button>
                                </div>
                                <p class="prof-error" id="errKonfirmasi" hidden>Konfirmasi password belum sama.</p>
                            </div>

                        </div>

                        <ul class="prof-rules" id="passRules" aria-live="polite">
                            <li data-rule="len"><svg class="prof-rules__ico" aria-hidden="true"><use href="#s-x"/></svg>Minimal 6 karakter</li>
                            <li data-rule="case"><svg class="prof-rules__ico" aria-hidden="true"><use href="#s-x"/></svg>Kombinasi huruf besar (A-Z) dan huruf kecil (a-z)</li>
                            <li data-rule="num"><svg class="prof-rules__ico" aria-hidden="true"><use href="#s-x"/></svg>Mengandung minimal satu angka (0-9)</li>
                            <li data-rule="sym"><svg class="prof-rules__ico" aria-hidden="true"><use href="#s-x"/></svg>Mengandung simbol atau karakter khusus (contoh: @, $, !, %, ?, &amp;)</li>
                        </ul>

                        <div class="prof-actions prof-actions--static">
                            <button type="submit" class="prof-btn prof-btn--primary" id="btnSimpanPassword" disabled>Simpan</button>
                            <button type="button" class="prof-btn prof-btn--ghost" id="btnBatalPassword">Batal</button>
                        </div>

                    </form>
                </section>

            </div>
        </div>


        {{-- ================= POP-UP KALENDER ================= --}}
        <dialog class="prof-dialog prof-cal" id="dateDialog" aria-labelledby="calField">
            <div class="prof-dialog__panel">

                <div class="prof-cal__bar">
                    <div>
                        <small>Pilih tanggal</small>
                        <strong id="calField">Mulai Magang</strong>
                    </div>
                    <button type="button" class="prof-cal__x" data-cal-close aria-label="Tutup">
                        <svg class="prof-ico" aria-hidden="true"><use href="#i-x"/></svg>
                    </button>
                </div>

                <div class="prof-cal__body">
                    <div class="prof-cal__nav">
                        <button type="button" id="calPrev" aria-label="Sebelumnya">
                            <svg class="prof-ico" aria-hidden="true"><use href="#i-chevron-left"/></svg>
                        </button>
                        <button type="button" class="prof-cal__title" id="calTitle"></button>
                        <button type="button" id="calNext" aria-label="Berikutnya">
                            <svg class="prof-ico" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                        </button>
                    </div>

                    <div class="prof-cal__dow" id="calDow" aria-hidden="true">
                        <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                    </div>

                    <div class="prof-cal__grid" id="calGrid"></div>
                </div>

                <div class="prof-cal__foot">
                    <button type="button" class="prof-link" id="calToday">Hari ini</button>
                    <button type="button" class="prof-link prof-link--muted" data-cal-close>Tutup</button>
                </div>

            </div>
        </dialog>


        {{-- ================= POP-UP KONFIRMASI (keluar / buang perubahan) ================= --}}
        <dialog class="prof-dialog prof-confirm" id="confirmDialog" aria-labelledby="confirmTitle">
            <div class="prof-dialog__panel">
                <span class="prof-confirm__icon" id="confirmIcon" aria-hidden="true"></span>
                <h2 id="confirmTitle"></h2>
                <p id="confirmText"></p>

                <div class="prof-confirm__actions">
                    <button type="button" class="prof-btn prof-btn--ghost" id="confirmNo">Batal</button>
                    <button type="button" class="prof-btn prof-btn--primary" id="confirmYes">Ya</button>
                </div>
            </div>
        </dialog>


        {{-- Toast --}}
        <div class="prof-toast" id="toast" role="status" aria-live="polite" hidden>
            <svg class="prof-ico" aria-hidden="true"><use href="#s-ok"/></svg>
            <span id="toastText"></span>
        </div>

        {{-- Form logout (dikirim setelah konfirmasi) --}}
        <form id="logoutForm" action="{{ url('/logout') }}" method="POST" hidden>
            @csrf
        </form>

    </div>

@endsection


@push('scripts')
    <script src="{{ asset('js/profil.js') }}"></script>
@endpush
