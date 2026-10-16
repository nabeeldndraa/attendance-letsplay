{{--
    Judul halaman + tombol kembali (pita merah di mobile, teks biasa di desktop).

    Pemakaian:
        <x-page-hero title="Riwayat Presensi" subtitle="..." :back="url('/dashboard-test')">
            (isi opsional, tampil di kanan judul, hanya desktop)
        </x-page-hero>

    Catatan: jangan menulis komentar Blade di dalam komentar Blade.
--}}
@props([
    'title',
    'subtitle' => null,
    'back'     => null,
])

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/page-hero.css') }}">
    @endpush
@endonce

<header class="page-hero">

    <a href="{{ $back ?? url('/dashboard-test') }}" class="page-hero__back" aria-label="Kembali">
        <svg class="page-hero__ico" aria-hidden="true"><use href="#i-chevron-left"/></svg>
    </a>

    <div class="page-hero__text">
        <h1 class="page-hero__title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-hero__sub">{{ $subtitle }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="page-hero__aside">{{ $slot }}</div>
    @endif

</header>