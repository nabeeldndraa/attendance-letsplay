@extends('layouts.app')

@section('title', 'History')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/history.css') }}">
@endpush


@section('content')

    <div class="hist" id="historyApp">

        {{-- JUDUL + PERIODE (periode pindah ke kartu atas di mobile, diatur history.js) --}}
        <x-page-hero title="Riwayat Presensi"
                     subtitle="Lihat riwayat presensi kamu selama periode magang"
                     :back="url('/dashboard-test')">

            <div id="slotPeriodDesktop">
                <div class="hist-period" id="periodControl">

                    <button type="button" class="hist-period__btn" id="periodBtn"
                            aria-haspopup="dialog" aria-expanded="false" aria-label="Pilih periode (bulan dan tahun)">
                        <svg class="hist-ico hist-period__ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                        <span id="periodLabel">-</span>
                        <svg class="hist-ico hist-period__chev" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                    </button>

                    <div class="hist-mp" id="periodPop" role="dialog" aria-label="Pilih bulan dan tahun" hidden>
                        <div class="hist-mp__nav">
                            <button type="button" id="mpPrev" aria-label="Tahun sebelumnya">
                                <svg class="hist-ico" aria-hidden="true"><use href="#i-chevron-left"/></svg>
                            </button>
                            <strong id="mpYear">2026</strong>
                            <button type="button" id="mpNext" aria-label="Tahun berikutnya">
                                <svg class="hist-ico" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                            </button>
                        </div>

                        <div class="hist-mp__grid" id="mpGrid"></div>

                        <p class="hist-mp__hint">Hanya bulan yang memiliki data presensi yang dapat dipilih.</p>
                    </div>

                </div>
            </div>

        </x-page-hero>


        {{-- RINGKASAN (mobile: dalam 1 kartu bersama periode & pencarian) --}}
        <section class="hist-top" aria-label="Ringkasan kehadiran">

            <div class="hist-summary">

                {{-- Persentase kehadiran --}}
                <article class="hist-sum hist-sum--rate">
                    <div class="hist-sum__text hist-only-desktop">
                        <h2>Persentase Kehadiran</h2>
                        <p>Pertahankan performa kehadiran kamu agar mendapat predikat terbaik.</p>
                    </div>

                    <div class="hist-donut" role="img" aria-label="Persentase kehadiran">
                        <svg viewBox="0 0 120 120" aria-hidden="true">
                            <circle class="hist-donut__track" cx="60" cy="60" r="52" pathLength="100"/>
                            <circle class="hist-donut__bar" id="donutBar" cx="60" cy="60" r="52" pathLength="100"
                                    transform="rotate(-90 60 60)" stroke-dasharray="0 100"/>
                        </svg>
                        <span class="hist-donut__val" id="sumPct">0%</span>
                    </div>

                    <div class="hist-sum__pct hist-only-mobile">
                        <strong id="sumPctM">0%</strong>
                        <span>kehadiran</span>
                    </div>
                </article>

                {{-- Total presensi --}}
                <article class="hist-sum hist-sum--total">
                    <span class="hist-sum__icon hist-sum__icon--pink">
                        <svg class="hist-ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                    </span>
                    <div class="hist-sum__body">
                        <p class="hist-sum__label">Total Presensi</p>
                        <p class="hist-sum__value" id="sumTotal">0</p>
                        <p class="hist-sum__sub">dari <span id="sumHari">60</span> hari internship</p>
                    </div>
                </article>

                {{-- Tepat waktu (desktop saja) --}}
                <article class="hist-sum hist-sum--tepat">
                    <span class="hist-sum__icon hist-sum__icon--blue">
                        <svg class="hist-cico" aria-hidden="true"><use href="#s-clock"/></svg>
                    </span>
                    <div class="hist-sum__body">
                        <p class="hist-sum__label">Tepat Waktu</p>
                        <p class="hist-sum__value" id="sumTepat">0</p>
                        <p class="hist-sum__sub" id="sumPredikat">-</p>
                    </div>
                </article>

            </div>

            {{-- Periode & pencarian versi mobile --}}
            <div class="hist-filters-m">
                <span class="hist-filters-m__label">Periode</span>
                <div id="slotPeriodMobile"></div>
                <div id="slotSearchMobile"></div>
            </div>

        </section>


        {{-- DAFTAR RIWAYAT --}}
        <section class="hist-list" aria-labelledby="histListTitle">

            <div class="hist-list__head">
                <h2 id="histListTitle">Daftar Riwayat Presensi</h2>

                <div id="slotSearchDesktop">
                    <label class="hist-search" id="searchControl">
                        <svg class="hist-ico" aria-hidden="true"><use href="#i-search"/></svg>
                        <input type="search" id="historySearch" placeholder="Cari tanggal atau keterangan..."
                               autocomplete="off" aria-label="Cari tanggal atau keterangan">
                    </label>
                </div>
            </div>

            <div class="hist-thead" aria-hidden="true">
                <span>Hari &amp; Tanggal</span>
                <span>Jam Presensi</span>
                <span>Status</span>
                <span>Progress / Catatan Kerja</span>
                <span>Detail</span>
            </div>

            <ul class="hist-rows" id="historyRows"></ul>

            <p class="hist-empty" id="historyEmpty" hidden>Tidak ada riwayat yang cocok.</p>

            <div class="hist-foot">
                <p class="hist-foot__info" id="historyInfo"></p>
                <nav class="hist-pager" id="historyPager" aria-label="Halaman riwayat"></nav>
            </div>

        </section>


        {{-- POP-UP DETAIL (isi dibuat oleh history.js) --}}
        <dialog class="hist-dialog" id="detailDialog" aria-labelledby="detailTitle">
            <div class="hist-dialog__panel">

                <div class="hist-dialog__bar">
                    <button type="button" class="hist-dialog__back" data-close aria-label="Kembali">
                        <svg class="hist-ico" aria-hidden="true"><use href="#i-chevron-left"/></svg>
                    </button>

                    <h2 id="detailTitle">Detail Presensi</h2>

                    <button type="button" class="hist-dialog__x" data-close aria-label="Tutup">
                        <svg class="hist-ico" aria-hidden="true"><use href="#i-x"/></svg>
                    </button>
                </div>

                <div class="hist-dialog__body" id="detailBody"></div>

            </div>
        </dialog>

    </div>

@endsection


@push('scripts')
    <script src="{{ asset('js/history.js') }}"></script>
@endpush
