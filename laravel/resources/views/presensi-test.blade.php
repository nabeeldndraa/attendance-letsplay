@extends('layouts.app')

@section('title', 'Presensi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/presensi.css') }}">
@endpush


@section('content')

    {{-- Ikon khusus halaman presensi (ikon umum ada di partials/icons) --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="p-building" viewBox="0 0 24 24"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2M10 6h4M10 10h4M10 14h4M10 18h4"/></symbol>
            <symbol id="p-send" viewBox="0 0 24 24"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7Z"/></symbol>
            <symbol id="p-shield" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></symbol>
            <symbol id="p-heart" viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="m12 13-1-1 2-2-3-3 2-2"/></symbol>
            <symbol id="p-flag" viewBox="0 0 24 24"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1Z"/><path d="M4 22v-7"/></symbol>
            <symbol id="p-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></symbol>
            <symbol id="p-check" viewBox="0 0 24 24"><path d="m5 12 5 5 9-10"/></symbol>
            <symbol id="p-check-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></symbol>
            <symbol id="p-x-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></symbol>
            <symbol id="p-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
            <symbol id="p-back" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></symbol>
        </defs>
    </svg>


    <div class="pres-content"
         id="presensiApp"
         data-url-status="{{ url('/presensi/status-hari-ini') }}"
         data-url-cek-lokasi="{{ url('/presensi/cek-lokasi') }}"
         data-url-presensi="{{ url('/presensi') }}"
         data-url-izin="{{ url('/presensi/izin') }}">

        {{-- JUDUL --}}
        <div class="pres-hero">
            <a href="{{ url('/dashboard-test') }}" class="pres-hero__back" aria-label="Kembali">
                <svg class="pres-ico" aria-hidden="true"><use href="#p-back"/></svg>
            </a>
            <div>
                <h1>Buat Presensi</h1>
                <p>Lengkapi informasi di bawah ini untuk melakukan presensi hari ini.</p>
            </div>
        </div>


        {{-- JANGAN LUPA --}}
        <aside class="pres-notice">
            <img class="pres-notice__cat" src="{{ asset('images/kucing_elso.png') }}" alt="">
            <svg class="pres-ico pres-notice__pin" aria-hidden="true"><use href="#i-pin"/></svg>
            <div>
                <h2>Jangan lupa!</h2>
                <p>Pastikan GPS Device kamu hidup ya dan berada di area yang sesuai ya!</p>
            </div>
        </aside>


        {{-- ===== CARD: INFORMASI PRESENSI ===== --}}
        <section class="pres-card pres-card--info" id="cardPresensi">

            <button type="button" class="pres-lock" data-activate="presensi"
                    aria-label="Aktifkan form Informasi Presensi"></button>

            <div class="pres-card__body" id="bodyPresensi">

                <h2 class="pres-card__title">Informasi Presensi</h2>

                <div class="pres-banner" id="statusPresensi" hidden>
                    ✓ Kamu sudah melakukan presensi hari ini.
                </div>

                <div class="pres-fields">

                    {{-- Tanggal & jam --}}
                    <div class="pres-field pres-field--datetime">
                        <span class="pres-label pres-only-desktop">Tanggal &amp; Jam (Otomatis)</span>

                        <div class="pres-readonly">
                            <svg class="pres-ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                            <span id="tanggalHariIni">--</span>
                            <em class="pres-only-mobile">(Otomatis)</em>
                        </div>

                        <div class="pres-readonly">
                            <svg class="pres-ico" aria-hidden="true"><use href="#i-clock"/></svg>
                            <span id="jamSekarang">--:-- WIB</span>
                        </div>
                    </div>

                    {{-- Jenis presensi --}}
                    <div class="pres-field pres-field--jenis">
                        <span class="pres-label">Jenis Presensi</span>

                        <div class="pres-choice">
                            <button type="button" class="pres-choice__btn is-active" data-value="WFO" aria-pressed="true">
                                <svg class="pres-ico" aria-hidden="true"><use href="#p-building"/></svg>
                                WFO
                            </button>
                            <button type="button" class="pres-choice__btn" data-value="WFH" aria-pressed="false">
                                <svg class="pres-ico" aria-hidden="true"><use href="#i-home"/></svg>
                                WFH
                            </button>
                        </div>

                        <input type="hidden" id="tipe_kerja" value="WFO">
                    </div>

                    {{-- Lokasi kantor (hanya WFO) --}}
                    <div class="pres-field pres-field--lokasi" id="lokasiSection">
                        <span class="pres-label">Lokasi Kantor</span>

                        <div class="pres-select">
                            <svg class="pres-ico" aria-hidden="true"><use href="#i-pin"/></svg>
                            <span class="pres-select__name" id="namaLokasi">Mendeteksi lokasi...</span>
                            <svg class="pres-ico" aria-hidden="true"><use href="#p-chevron"/></svg>
                        </div>

                        <div class="pres-loc-status is-loading" id="lokasiBox" aria-live="polite">
                            <span class="pres-loc-status__icon" id="lokasiIcon"><span class="pres-spinner"></span></span>

                            <div class="pres-loc-status__text">
                                <strong id="statusLokasi">Mengambil lokasi...</strong>
                                <span id="jarakLokasi">Pastikan GPS perangkatmu aktif.</span>
                            </div>

                            <button type="button" class="pres-btn-outline" id="btnLokasi" hidden>Coba Lagi</button>
                            <button type="button" class="pres-btn-outline" id="btnPeta" disabled>Lihat Peta</button>
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div class="pres-field pres-field--progres">
                        <label class="pres-label" for="progress_hari_ini">Progress yang akan dikerjakan hari ini</label>
                        <textarea class="pres-textarea" id="progress_hari_ini" maxlength="200"
                                  placeholder="Contoh: Membuat landing page event, revisi UI..."></textarea>
                        <div class="pres-counter" id="counterProgress">0 / 200 karakter</div>
                    </div>

                    {{-- Syarat --}}
                    <div class="pres-field pres-field--syarat">
                        <div class="pres-syarat" id="syaratWfo">
                            <h3>Syarat Presensi WFO</h3>
                            <ul>
                                <li><span class="pres-bullet"><svg class="pres-ico"><use href="#p-check"/></svg></span>Pastikan kamu berada dalam radius area kantor (maks. 100 meter)</li>
                                <li><span class="pres-bullet"><svg class="pres-ico"><use href="#p-check"/></svg></span>Lokasi perangkat aktif</li>
                                <li><span class="pres-bullet"><svg class="pres-ico"><use href="#p-check"/></svg></span>Isi progres pekerjaan hari ini dengan jelas</li>
                            </ul>
                        </div>

                        <div class="pres-syarat" id="syaratWfh" hidden>
                            <h3>Syarat Presensi WFH</h3>
                            <ul>
                                <li><span class="pres-bullet"><svg class="pres-ico"><use href="#p-check"/></svg></span>WFH tidak membutuhkan validasi lokasi kantor</li>
                                <li><span class="pres-bullet"><svg class="pres-ico"><use href="#p-check"/></svg></span>Kamu dapat melakukan presensi dari lokasi mana pun</li>
                                <li><span class="pres-bullet"><svg class="pres-ico"><use href="#p-check"/></svg></span>Isi progres pekerjaan hari ini dengan jelas</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </section>


        {{-- ===== CARD: IZIN / SAKIT ===== --}}
        <section class="pres-card pres-card--izin is-locked" id="cardIzin">

            <button type="button" class="pres-lock" data-activate="izin"
                    aria-label="Aktifkan form Izin atau Sakit"></button>

            <div class="pres-card__body" id="bodyIzin" inert>

                <h2 class="pres-card__title">Izin/Sakit</h2>
                <p class="pres-hint">Jika kamu berhalangan hadir, ajukan izin atau sakit disini</p>

                <div class="pres-choice pres-choice--sm">
                    <button type="button" class="pres-choice__btn is-active" data-jenis="sakit" aria-pressed="true">
                        <svg class="pres-ico" aria-hidden="true"><use href="#p-heart"/></svg>
                        Sakit
                    </button>
                    <button type="button" class="pres-choice__btn" data-jenis="izin" aria-pressed="false">
                        <svg class="pres-ico" aria-hidden="true"><use href="#p-flag"/></svg>
                        Izin
                    </button>
                </div>

                <label class="pres-label pres-label--izin" for="keterangan_izin">Keterangan</label>
                <textarea class="pres-textarea pres-textarea--izin" id="keterangan_izin"
                          placeholder="Jelaskan alasan dan detail izin (mis. keperluan keluarga, urusan kampus, dll)"></textarea>

            </div>
        </section>


        {{-- ===== CARD: ATURAN PRESENSI ===== --}}
        <section class="pres-card pres-card--aturan">

            <h2 class="pres-card__title">Aturan Presensi</h2>

            <ul class="pres-rules">
                <li class="pres-rule pres-rule--green">
                    <svg class="pres-ico pres-rule__icon" aria-hidden="true"><use href="#i-clock"/></svg>
                    <span class="pres-rule__time">06.00 - 10.00 WIB</span>
                    <span class="pres-rule__label">Presensi Tepat Waktu (+10 poin)</span>
                </li>
                <li class="pres-rule pres-rule--yellow">
                    <svg class="pres-ico pres-rule__icon" aria-hidden="true"><use href="#i-clock"/></svg>
                    <span class="pres-rule__time">10.00 - 12.00 WIB</span>
                    <span class="pres-rule__label">Terlambat (konfirmasi admin) (+10 poin)</span>
                </li>
                <li class="pres-rule pres-rule--orange">
                    <svg class="pres-ico pres-rule__icon" aria-hidden="true"><use href="#i-clock"/></svg>
                    <span class="pres-rule__time">12.00 - 15.00 WIB</span>
                    <span class="pres-rule__label">Terlambat (+5 poin)</span>
                </li>
                <li class="pres-rule pres-rule--red">
                    <svg class="pres-ico pres-rule__icon" aria-hidden="true"><use href="#i-clock"/></svg>
                    <span class="pres-rule__time">Setelah 15.00 WIB</span>
                    <span class="pres-rule__label">Alpha (-10 poin)</span>
                </li>
            </ul>

            <div class="pres-note">
                <svg class="pres-ico" aria-hidden="true"><use href="#p-info"/></svg>
                Poin kamu akan direset setiap awal bulan.
            </div>

        </section>


        {{-- ===== SUBMIT ===== --}}
        <div class="pres-submit">

            <div class="pres-submit__bar">
                <div class="pres-submit__info">
                    <svg class="pres-ico pres-submit__shield" aria-hidden="true"><use href="#p-shield"/></svg>
                    <div>
                        <strong>Pastikan semua informasi sudah diisi dengan benar.</strong>
                        <span>Kamu tidak dapat mengubah data setelah presensi dikirim.</span>
                    </div>
                </div>

                <button type="button" class="pres-submit__btn" id="btnPresensi" disabled>
                    <svg class="pres-ico" aria-hidden="true"><use href="#p-send"/></svg>
                    <span id="btnPresensiLabel">Kirim Presensi</span>
                </button>
            </div>

            <div id="message" class="pres-message" role="alert" hidden></div>

        </div>

    </div>

@endsection


@push('scripts')
    <script src="{{ asset('js/presensi.js') }}"></script>
@endpush