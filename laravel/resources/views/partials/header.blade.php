{{-- ===== HEADER ===== --}}
<header class="topbar">

    <img src="{{ asset('images/logo_letsplay_mobile.png') }}" alt="Let's Play Indonesia" class="topbar-logo">

    <div class="topbar-right">

        <button type="button" class="bell-btn" aria-label="Notifikasi">
            <svg aria-hidden="true"><use href="#i-bell"/></svg>
            @if ($notif > 0)
                <b class="bell-badge">{{ $notif }}</b>
            @endif
        </button>

        <span class="topbar-divider"></span>

        <details class="user-menu">
            <summary>
                <span class="avatar">
                    <svg aria-hidden="true"><use href="#i-user"/></svg>
                </span>

                <span class="user-info">
                    <span class="user-name">{{ $user->nama }}</span>
                    <span class="user-role">{{ $user->bidang_magang }}</span>
                </span>
            </summary>

            <div class="user-dropdown">
                <form action="{{ url('/logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg aria-hidden="true"><use href="#i-logout"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </details>

    </div>
</header>
