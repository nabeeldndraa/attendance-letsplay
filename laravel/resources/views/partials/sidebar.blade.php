{{-- ===== SIDEBAR (desktop) / BOTTOM BAR (mobile) ===== --}}
<aside class="sidebar">

    <img src="{{ asset('images/login/logo_letsplay.png') }}" alt="Let's Play Indonesia" class="sidebar-logo">

    <nav class="menu" aria-label="Menu utama">

        <a href="{{ url('/dashboard-test') }}" class="{{ request()->is('dashboard*') ? 'active' : '' }}">
            <svg class="ico" aria-hidden="true"><use href="#i-home"/></svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ url('/presensi-test') }}" class="{{ request()->is('presensi*') ? 'active' : '' }}">
            <svg class="ico" aria-hidden="true"><use href="#i-check"/></svg>
            <span>Presensi</span>
        </a>

        <a href="{{ url('/history-test') }}" class="{{ request()->is('history*') ? 'active' : '' }}">
            <svg class="ico" aria-hidden="true"><use href="#i-history"/></svg>
            <span>History</span>
        </a>

        <a href="{{ url('/leaderboard-test') }}" class="{{ request()->is('leaderboard*') ? 'active' : '' }}">
            <svg class="ico" aria-hidden="true"><use href="#i-trophy"/></svg>
            <span>Leaderboard</span>
        </a>

        <a href="{{ url('/notifikasi-test') }}" class="{{ request()->is('notifikasi*') ? 'active' : '' }}">
            <svg class="ico" aria-hidden="true"><use href="#i-bell"/></svg>
            <span>Notifikasi</span>
            @if ($notif > 0)
                <b class="nav-badge">{{ $notif }}</b>
            @endif
        </a>

        <a href="{{ url('/profil-test') }}" class="{{ request()->is('profil*') ? 'active' : '' }}">
            <svg class="ico" aria-hidden="true"><use href="#i-user"/></svg>
            <span>Profil Saya</span>
        </a>

    </nav>

</aside>