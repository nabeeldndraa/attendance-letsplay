{{-- ===== SIDEBAR ADMIN (desktop) / BOTTOM BAR (mobile) =====
     Tampilan sama dengan sidebar user (class dari dashboard.css).
     Untuk menambah / mengubah menu admin, cukup edit array $adminMenu di bawah. --}}
@php
    /*
     * label : teks menu
     * route : NAMA route (route name) halaman tujuan
     * icon  : id ikon di partials/icons.blade.php
     * Jika route belum ada, menu tetap tampil dengan link "#" (tidak error).
     */
    $adminMenu = [
        ['label' => 'Dashboard',        'route' => 'admin.dashboard',     'icon' => '#i-home'],
        ['label' => 'Data Intern',      'route' => 'admin.data-user',     'icon' => '#i-users'],
        ['label' => 'History Presensi', 'route' => 'admin.data-presensi', 'icon' => '#i-history'],
        ['label' => 'Leaderboard',      'route' => 'admin.leaderboard',   'icon' => '#i-trophy'],
    ];
@endphp

<aside class="sidebar">

    <img src="{{ asset('images/login/logo_letsplay.png') }}" alt="Let's Play Indonesia" class="sidebar-logo">

    <nav class="menu" aria-label="Menu admin">

        @foreach ($adminMenu as $menu)
            @php
                $ada   = \Illuminate\Support\Facades\Route::has($menu['route']);
                $aktif = $ada && request()->routeIs($menu['route'] . '*');
            @endphp

            <a href="{{ $ada ? route($menu['route']) : '#' }}"
               class="{{ $aktif ? 'active' : '' }}"
               @if ($aktif) aria-current="page" @endif>
                <svg class="ico" aria-hidden="true"><use href="{{ $menu['icon'] }}"/></svg>
                <span>{{ $menu['label'] }}</span>
            </a>
        @endforeach

    </nav>

</aside>