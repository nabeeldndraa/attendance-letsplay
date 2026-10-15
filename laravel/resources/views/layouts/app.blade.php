@php
    $user = auth()->user();

    /*
     * TODO (backend): $notif masih DUMMY dan hanya ditulis di SATU tempat ini.
     * Nanti ganti dengan data asli (mis. lewat View Composer) supaya semua
     * halaman otomatis mendapat jumlah notifikasi yang sama.
     */
    $notif = $notif ?? 3;
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

    @include('partials.sidebar')

    <div class="main">

        @include('partials.header')

        <main class="content">
            @yield('content')
        </main>

    </div>

    {{-- JS khusus halaman --}}
    @stack('scripts')

</body>
</html>
