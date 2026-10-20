@extends('layouts.app')
@section('title', 'Leaderboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/leaderboard.css') }}">
@endpush

@section('content')
@php
    $now = \Carbon\Carbon::now('Asia/Jakarta');

    // Bulan yang dipilih (query ?bulan=YYYY-MM). Default: bulan ini.
    $selected = $now->copy()->startOfMonth();
    $bulanReq = (string) request('bulan');
    if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanReq)) {
        $selected = \Carbon\Carbon::createFromFormat('!Y-m', $bulanReq, 'Asia/Jakarta');
        if ($selected->gt($now)) {
            $selected = $now->copy()->startOfMonth();
        }
    }
    $monthName  = $selected->format('F');       // mis. "March"
    $monthLabel = $selected->format('F Y');     // mis. "March 2026"
    $monthValue = $selected->format('Y-m');
    $monthMax   = $now->format('Y-m');

    /*
     * TODO (backend): semua data di bawah ini masih DUMMY untuk keperluan tampilan.
     * Ganti dengan data asli dari controller (peringkat sesuai bulan yang dipilih).
     */
    $resetDays = $now->daysInMonth - $now->day + 1;

    $top = [
        1 => ['nama' => 'Princess Ember Suyani', 'bidang' => '3D Illustration', 'poin' => 2005],
        2 => ['nama' => 'Donnalls Duck',         'bidang' => 'Programming',     'poin' => 1980],
        3 => ['nama' => 'Mikcy',                 'bidang' => 'Public Relation', 'poin' => 1930],
    ];

    $ranking = [
        ['nama' => 'Princess Ember Suyani', 'poin' => 2005],
        ['nama' => 'Donnalls Duck',         'poin' => 1980],
        ['nama' => 'Mikcy',                 'poin' => 1930],
        ['nama' => 'Nama Intern 4',         'poin' => 1875],
        ['nama' => 'Nama Intern 5',         'poin' => 1860],
        ['nama' => 'Nama Intern 6',         'poin' => 1810],
        ['nama' => 'Nama Intern 7',         'poin' => 1780],
        ['nama' => 'Nama Intern 8',         'poin' => 1760],
    ];

    $fmt = fn ($n) => number_format($n, 0, ',', '.');
@endphp

{{-- Ikon khusus halaman Leaderboard (prefix p-lb-) --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    {{-- lencana peringkat: pita merah + bintang putih --}}
    <symbol id="p-lb-badge" viewBox="0 0 56 75">
        <path d="M6 0H50Q56 0 56 6V50Q56 66 28 75Q0 66 0 50V6Q0 0 6 0Z" fill="#f23a42"/>
        <polygon points="28,13 31.53,22.15 41.32,22.67 33.71,28.85 36.23,38.33 28,33 19.77,38.33 22.29,28.85 14.68,22.67 24.47,22.15"
                 fill="#fff" stroke="#fff" stroke-width="2" stroke-linejoin="round"/>
    </symbol>

    {{-- medali juara 1 --}}
    <symbol id="p-lb-medal" viewBox="0 0 80 84">
        <path d="M20 46 38 52 28 76 21 67 8 70Z" fill="#e0242b"/>
        <path d="M60 46 42 52 52 76 59 67 72 70Z" fill="#e0242b"/>
        <circle cx="40" cy="28" r="25" fill="#c8161c"/>
        <polygon points="40,14 43.4,23.5 53.5,23.9 45.6,30.2 48.3,39.9 40,34.3 31.7,39.9 34.4,30.2 26.5,23.9 36.6,23.5"
                 fill="#fff" stroke="#fff" stroke-width="2" stroke-linejoin="round"/>
    </symbol>
</svg>

<div class="lb-page">

    {{-- ===== PILIH BULAN + POINT RESET (di atas hero, semua ukuran layar) ===== --}}
    <form class="lb-controls" method="GET" action="{{ url()->current() }}">

        <div class="lb-controls-left">
            <span class="lb-controls-label">Pilih Bulan</span>

            <div class="lb-select-wrap" data-max="{{ $monthMax }}">
                <input type="hidden" name="bulan" value="{{ $monthValue }}">

                <button type="button" class="lb-select" aria-haspopup="dialog" aria-expanded="false">
                    <svg class="lb-select-ico" aria-hidden="true"><use href="#i-calendar"/></svg>
                    <span class="lb-select-text">{{ $monthLabel }}</span>
                    <svg class="lb-select-arrow" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                </button>

                <div class="lb-mp" role="dialog" aria-label="Pilih bulan" hidden></div>
            </div>
        </div>

        <div class="lb-reset">
            <svg aria-hidden="true"><use href="#i-clock"/></svg>
            <span>Point Reset in <b>{{ $resetDays }} {{ $resetDays > 1 ? 'Days' : 'Day' }}</b></span>
        </div>

    </form>


    {{-- ===== HERO ===== --}}
    <section class="lb-hero">

        <div class="lb-hero-text">
            <h1>Check Your <span>{{ $monthName }}</span> Leader Board!</h1>
        </div>

        <div class="lb-hero-art">
            <img src="{{ asset('images/user/bubble_leaderboard.png') }}" alt="Mau jadi yang paling atas? Absen yang Rajin" class="lb-hero-bubble">
            <img src="{{ asset('images/kucing_elso2.png') }}" alt="" class="lb-hero-cat">
        </div>

    </section>


    {{-- ===== PAPAN PERINGKAT ===== --}}
    <section class="lb-board" aria-label="Leaderboard {{ $monthLabel }}">

        {{-- Podium Top 3 (urutan tampil: 2 - 1 - 3) --}}
        <div class="lb-podium">

            @foreach ([2, 1, 3] as $rank)
                <article class="lb-card lb-card--{{ $rank }}">
                    <span class="lb-tag">{{ $rank }}</span>

                    @if ($rank === 1)
                        <svg class="lb-medal" aria-hidden="true"><use href="#p-lb-medal"/></svg>
                    @endif

                    <div class="lb-card-info">
                        <h3 class="lb-card-name">{{ $top[$rank]['nama'] }}</h3>
                        <p class="lb-card-role">{{ $top[$rank]['bidang'] }}</p>
                    </div>

                    <span class="lb-pts">{{ $fmt($top[$rank]['poin']) }} pts</span>
                </article>
            @endforeach

        </div>

        {{-- Panel merah: tabel peringkat --}}
        <div class="lb-panel">

            <h2 class="lb-panel-title">Top 5<br>Position Chart</h2>
            <div class="lb-line"></div>

            <div class="lb-thead" aria-hidden="true">
                <span>Position</span>
                <span>Name</span>
                <span>Points</span>
            </div>

            <ol class="lb-rows">
                @foreach ($ranking as $i => $row)
                    <li class="lb-row">
                        <span class="lb-cell lb-cell--pos">{{ $i + 1 }}</span>

                        <span class="lb-cell lb-cell--name">
                            <svg class="lb-rank-badge" aria-hidden="true"><use href="#p-lb-badge"/></svg>
                            <span class="lb-name-text">{{ \Illuminate\Support\Str::title($row['nama']) }}</span>
                        </span>

                        <span class="lb-cell lb-cell--pts">{{ $fmt($row['poin']) }}</span>
                    </li>
                @endforeach
            </ol>

        </div>

    </section>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/leaderboard.js') }}"></script>
@endpush