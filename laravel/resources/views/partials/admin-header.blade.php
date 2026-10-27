{{-- ===== HEADER ADMIN =====
     Tampilan sama dengan header user (class dari dashboard.css), tanpa lonceng
     notifikasi karena admin tidak memakai halaman notifikasi. --}}
@php
    $adminUser = auth()->user();
@endphp

<header class="topbar">

    <img src="{{ asset('images/logo_letsplay_mobile.png') }}" alt="Let's Play Indonesia" class="topbar-logo">

    <div class="topbar-right">

        <details class="user-menu">
            <summary>
                <span class="avatar">
                    <svg aria-hidden="true"><use href="#i-user"/></svg>
                </span>

                <span class="user-info">
                    <span class="user-name">{{ $adminUser->nama ?? 'Administrator' }}</span>
                    <span class="user-role">Administrator</span>
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
