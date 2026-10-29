@extends('layouts.app')

@section('title', 'Notifikasi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/notifikasi.css') }}">
@endpush


@section('content')

    <div class="notif" id="notifApp"
         data-url-history="{{ url('/history-test') }}"
         data-url-profil="{{ url('/profil-test') }}"
         data-url-data="{{ url('/notifikasi/data') }}"
         data-url-mark-all="{{ url('/notifikasi/dibaca-semua') }}"
         data-url-mark-base="{{ url('/notifikasi') }}">

        <x-page-hero title="Notifikasi"
                     subtitle="Informasi terbaru seputar presensi, magang, dan akun kamu"
                     :back="url('/dashboard-test')" />

        <div class="notif-grid">

            {{-- ================= DAFTAR ================= --}}
            <section class="notif-main" aria-label="Daftar notifikasi">

                <div class="notif-tools">

                    <label class="notif-search">
                        <svg class="notif-ico notif-search__ico" aria-hidden="true"><use href="#i-search"/></svg>
                        <input type="search" id="notifSearch" placeholder="Cari notifikasi..."
                               autocomplete="off" aria-label="Cari notifikasi">
                    </label>

                    <div class="notif-sort" id="sortWrap">
                        <button type="button" class="notif-sort__btn" id="sortBtn"
                                aria-haspopup="listbox" aria-expanded="false" aria-label="Urutkan notifikasi">
                            <span id="sortLabel">Terbaru</span>
                            <svg class="notif-ico notif-sort__chev" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                        </button>

                        <ul class="notif-sort__menu" id="sortMenu" role="listbox" aria-label="Urutkan" hidden>
                            <li role="option" tabindex="-1" data-value="terbaru" aria-selected="true">
                                <span>Terbaru</span>
                                <svg class="notif-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                            </li>
                            <li role="option" tabindex="-1" data-value="terlama" aria-selected="false">
                                <span>Terlama</span>
                                <svg class="notif-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                            </li>
                            <li role="option" tabindex="-1" data-value="belum" aria-selected="false">
                                <span>Belum dibaca</span>
                                <svg class="notif-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                            </li>
                        </ul>
                    </div>

                    {{-- tombol filter: hanya tampil di layar sempit (filter jadi pop-up) --}}
                    <button type="button" class="notif-filter-btn" id="btnFilter" aria-label="Filter notifikasi">
                        <svg class="notif-ico" aria-hidden="true"><use href="#i-filter"/></svg>
                        <span class="notif-filter-btn__dot" id="filterDot" hidden></span>
                    </button>

                </div>

                <ul class="notif-list" id="notifList"></ul>

                <div class="notif-empty" id="notifEmpty" hidden>
                    <span class="notif-empty__icon"><svg class="notif-ico" aria-hidden="true"><use href="#i-bell"/></svg></span>
                    <strong>Tidak ada notifikasi</strong>
                    <p id="notifEmptyText">Notifikasi sedang dimuat...</p>
                </div>

                <div class="notif-foot">
                    <p class="notif-foot__info" id="notifInfo"></p>
                    <nav class="notif-pager" id="notifPager" aria-label="Halaman notifikasi"></nav>
                </div>

            </section>


            {{-- ================= FILTER (desktop) ================= --}}
            <aside class="notif-side">
                <section class="notif-card">

                    <div class="notif-card__head">
                        <svg class="notif-ico" aria-hidden="true"><use href="#i-filter"/></svg>
                        <h2>Filter Notifikasi</h2>
                    </div>

                    {{-- isi filter: dipindah ke pop-up oleh notifikasi.js di layar sempit --}}
                    <div id="slotFilterDesktop">
                        <div class="notif-filter" id="filterBody">

                            <ul class="notif-cats" id="catList"></ul>

                            <button type="button" class="notif-markall" id="btnMarkAll">
                                <svg class="notif-ico notif-markall__ico" aria-hidden="true"><use href="#i-check-circle"/></svg>
                                <span class="notif-markall__text">
                                    <strong>Tandai Semua Dibaca</strong>
                                    <small>Semua notifikasi akan ditandai sebagai sudah dibaca.</small>
                                </span>
                                <svg class="notif-ico notif-markall__chev" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                            </button>

                        </div>
                    </div>

                </section>
            </aside>

        </div>


        {{-- ================= POP-UP FILTER (mobile) ================= --}}
        <dialog class="notif-dialog" id="filterDialog" aria-labelledby="filterTitle">
            <div class="notif-dialog__panel">
                <span class="notif-dialog__grab" aria-hidden="true"></span>

                <div class="notif-sheet-head">
                    <span class="notif-sheet-head__icon"><svg class="notif-ico" aria-hidden="true"><use href="#i-filter"/></svg></span>
                    <div>
                        <h2 id="filterTitle">Filter Notifikasi</h2>
                        <p>Pilih kategori notifikasi yang ingin ditampilkan.</p>
                    </div>
                </div>

                <div id="slotFilterSheet"></div>
            </div>
        </dialog>


        {{-- ================= POP-UP DETAIL ================= --}}
        <dialog class="notif-dialog" id="detailDialog" aria-labelledby="detailTitle">
            <div class="notif-dialog__panel" id="detailPanel">
                <span class="notif-dialog__grab" aria-hidden="true"></span>

                <div class="notif-detail__top">
                    <span class="notif-icon" id="detailIcon"></span>
                    <div>
                        <h2 id="detailTitle"></h2>
                        <div class="notif-detail__meta">
                            <span class="notif-chip" id="detailChip"></span>
                            <span id="detailTime"></span>
                        </div>
                    </div>
                </div>

                <p class="notif-detail__text" id="detailText"></p>

                <div class="notif-detail__actions">
                    <a href="#" class="notif-btn notif-btn--primary" id="detailLink" hidden></a>
                    <button type="button" class="notif-btn notif-btn--ghost" id="detailClose">Tutup</button>
                </div>
            </div>
        </dialog>


        {{-- Toast --}}
        <div class="notif-toast" id="toast" role="status" aria-live="polite" hidden>
            <svg class="notif-ico" aria-hidden="true"><use href="#s-ok"/></svg>
            <span id="toastText"></span>
        </div>

    </div>

@endsection


@push('scripts')
    <script src="{{ asset('js/notifikasi.js') }}"></script>
@endpush
