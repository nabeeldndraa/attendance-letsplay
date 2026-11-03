@extends('layouts.admin')

@section('title', 'Notifikasi')

@push('styles')
    {{-- CSS dasar tema notifikasi (sama dengan halaman user) + tambahan khusus admin --}}
    <link rel="stylesheet" href="{{ asset('css/notifikasi.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-notifikasi.css') }}">
@endpush


@section('content')

    <div class="notif adm-notif" id="notifApp"
         data-url-data="{{ url('/admin/notifikasi/data') }}"
         data-url-mark-all="{{ url('/admin/notifikasi/dibaca-semua') }}"
         data-url-base="{{ url('/admin/notifikasi') }}"
         data-csrf="{{ csrf_token() }}"
         data-demo="{{ config('app.debug') ? 'true' : 'false' }}">

        <x-page-hero title="Notifikasi"
                     subtitle="Informasi terbaru seputar aktivitas, presensi, dan peserta magang"
                     :back="route('admin.dashboard')" />

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

                    {{-- isi filter: dipindah ke pop-up oleh admin-notifikasi.js di layar sempit --}}
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

                {{-- tombol setujui / tolak (hanya untuk permintaan yang butuh keputusan) --}}
                <div class="adm-notif-decide" id="detailDecide" hidden>
                    <button type="button" class="adm-notif-btn adm-notif-btn--ok" data-aksi="setujui">
                        <svg class="notif-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                        Setujui
                    </button>
                    <button type="button" class="adm-notif-btn adm-notif-btn--no" data-aksi="tolak">
                        <svg class="notif-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                        Tolak
                    </button>
                </div>

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
    <script src="{{ asset('js/admin/admin-notifikasi.js') }}"></script>
@endpush
