@php
    $user = auth()->user();
    $notif = $user?->notifikasiSaya()->whereNull('dibaca_pada')->count() ?? 0;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Let's Play Indonesia</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- CSS bersama: sidebar, bottom bar, header --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    {{-- CSS khusus halaman --}}
    @stack('styles')
</head>

<body>

    @include('partials.icons')

    {{-- Sidebar: default versi user. Layout lain (mis. layouts.admin) bisa menggantinya
         dengan @section('sidebar') --}}
    @hasSection('sidebar')
        @yield('sidebar')
    @else
        @include('partials.sidebar')
    @endif

    <div class="main">

        {{-- Header: default versi user, bisa diganti dengan @section('header') --}}
        @hasSection('header')
            @yield('header')
        @else
            @include('partials.header')
        @endif

        <main class="content">
            @yield('content')
        </main>

    </div>

    {{-- JS khusus halaman --}}
    @stack('scripts')
    <script src="{{ asset('js/notification-badge.js') }}"></script>

</body>
</html>