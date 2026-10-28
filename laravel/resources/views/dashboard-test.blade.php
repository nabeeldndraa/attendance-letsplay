@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $user = auth()->user();

    // Sapaan & tanggal otomatis (zona waktu WIB)
    $now       = \Carbon\Carbon::now('Asia/Jakarta')->locale('id');
    $greeting  = $now->hour < 12 ? 'Good Morning' : ($now->hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $firstName = \Illuminate\Support\Str::of($user->nama)->trim()->before(' ');
    $tanggal   = $now->translatedFormat('l, j F Y');

    $persenHadir = fn ($n) => round($n / max($stat['hadir'], 1) * 100);
    $persenHariKerja = fn ($n) => round($n / max($stat['hari_kerja'], 1) * 100);
    $lastLate = $last && $last['status'] !== 'Tepat Waktu';
@endphp


@section('content')

    {{-- HERO --}}
    <section class="hero">

        <div class="hero-text">
            <h1>{{ $greeting }}, {{ $firstName }}!</h1>
            <p class="hero-sub">Siap selesaikan quest hari ini?</p>
            <p class="hero-date">
                <svg aria-hidden="true"><use href="#i-calendar"/></svg>
                {{ $tanggal }}
            </p>
        </div>

        <div class="hero-art">
            <img src="{{ asset('images/user/bubble_janganlupa.png') }}" alt="Jangan lupa absen sebelum jam 10.00 WIB ya!" class="hero-bubble">
            <img src="{{ asset('images/kucing_elso.png') }}" alt="" class="hero-cat">
        </div>

    </section>


    {{-- STATISTIK --}}
    <section class="stats">

        <article class="stat stat--poin stat--top">
            <span class="stat-icon bg-pink"><svg aria-hidden="true"><use href="#s-star"/></svg></span>
            <div class="stat-body">
                <p class="stat-label">Total Poin</p>
                <p class="stat-value">{{ $stat['poin'] }}</p>
                <p class="stat-sub"><span class="star">★</span> +{{ $stat['poin_kemarin'] }} poin dari kemarin</p>
            </div>
        </article>

        <article class="stat stat--hadir stat--top">
            <span class="stat-icon bg-green"><svg aria-hidden="true"><use href="#s-cal"/></svg></span>
            <div class="stat-body">
                <p class="stat-label">Total Hadir</p>
                <p class="stat-value">{{ $stat['hadir'] }}</p>
                <p class="stat-sub">dari {{ $stat['hari_kerja'] }} hari kerja</p>
            </div>
        </article>

        <article class="stat stat--tepat stat--top">
            <span class="stat-icon bg-blue"><svg aria-hidden="true"><use href="#s-clock"/></svg></span>
            <div class="stat-body">
                <p class="stat-label">Tepat Waktu</p>
                <p class="stat-value">{{ $stat['tepat'] }}</p>
                <p class="stat-sub">{{ $persenHadir($stat['tepat']) }}% dari total hadir</p>
            </div>
        </article>

        <article class="stat">
            <span class="stat-icon bg-orange"><svg aria-hidden="true"><use href="#s-alert"/></svg></span>
            <div class="stat-body">
                <p class="stat-label">Terlambat</p>
                <p class="stat-value">{{ $stat['terlambat'] }}</p>
                <p class="stat-sub">{{ $persenHadir($stat['terlambat']) }}% dari total hadir</p>
            </div>
        </article>

        <article class="stat">
            <span class="stat-icon bg-red"><svg aria-hidden="true"><use href="#s-sad"/></svg></span>
            <div class="stat-body">
                <p class="stat-label">Alpha</p>
                <p class="stat-value">{{ $stat['alpha'] }}</p>
                <p class="stat-sub">{{ $persenHariKerja($stat['alpha']) }}% dari hari kerja</p>
            </div>
        </article>

    </section>


    {{-- PRESENSI TERAKHIR + PET --}}
    <section class="bottom-row">

        <article class="last-card">

            <div class="last-head">
                <h2>Presensi Terakhir</h2>
                @if ($last)
                    <span class="badge {{ $lastLate ? 'badge--late' : 'badge--ontime' }}">{{ $last['status'] }}</span>
                @endif
            </div>

            <div class="last-body">

                @if ($last)
                    <div class="date-box">
                        <span class="date-day">{{ $last['hari'] }}</span>
                        <span class="date-num">{{ $last['tanggal'] }}</span>
                        <span class="date-month">{{ $last['bulan'] }}</span>
                    </div>

                    <ul class="last-details">
                        <li><svg aria-hidden="true"><use href="#i-pin"/></svg>{{ $last['lokasi'] }}</li>
                        <li><svg aria-hidden="true"><use href="#i-clock"/></svg>{{ $last['jam'] }}</li>
                        <li><svg aria-hidden="true"><use href="#i-note"/></svg>{{ $last['kegiatan'] }}</li>
                        <li class="points"><span class="star">★</span>+{{ $last['poin'] }} Poin</li>
                    </ul>
                @else
                    <p>Belum ada presensi tercatat.</p>
                @endif

                <a href="{{ url('/history-test') }}" class="btn-outline">Lihat History</a>

            </div>
        </article>


        {{-- Kartu pet (sementara memakai kucing_login.png) --}}
        <article class="pet-card">
            <div class="pet-text">
                <h2>Your Pet Awaits!</h2>
                <p>Give them some love</p>
                <a href="#" class="btn-pet">Buka Pet</a>
            </div>
            <img src="{{ asset('images/login/kucing_login.png') }}" alt="" class="pet-img">
        </article>

    </section>

@endsection


@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush