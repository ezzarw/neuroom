@php
    $isHome = request()->is('/') || request()->is('utama');
@endphp

<nav class="navbar">
    <div class="container nav-content">
        <div class="logo">Neuroom</div>

        <ul class="nav-menu">

            <!-- BERANDA (SELALU MUNCUL) -->
            <li>
                <a href="{{ route('utama') }}" class="{{ $isHome ? 'active' : '' }}">
                    Beranda
                </a>
            </li>

            <li>
                <a href="/belajar" class="{{ request()->is('belajar') ? 'active' : '' }}">
                    Belajar
                </a>
            </li>

            <li>
                <a href="/fokus" class="{{ request()->is('fokus') ? 'active' : '' }}">
                    Fokus
                </a>
            </li>

            <li>
                <a href="/catatan" class="{{ request()->is('catatan') ? 'active' : '' }}">
                    Catatan
                </a>
            </li>

        </ul>

        <!-- USER (hanya di halaman utama, optional) -->
        @if($isHome)
            <a href="{{ route('profile') }}" class="user-link">
                Halo, <strong>{{ auth()->user()->username }}</strong>
            </a>
        @endif

    </div>
</nav>