@php
    $isHome = request()->is('/') || request()->is('utama');
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<nav class="navbar">
    <div class="container nav-content">
        <div class="logo">Neuroom</div>

        <!-- HAMBURGER -->
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-menu" id="navMenu">

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

        <!-- USER (semua halaman) + LOGOUT -->
        <div class="nav-right">
            <a href="{{ route('profile') }}" class="user-link">
                Halo, <strong>{{ auth()->user()->username ?? '' }}</strong>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;margin:0;padding:0;" id="logout-form">
                @csrf
                <button type="submit" class="btn-logout" title="Keluar" style="background:none;border:none;cursor:pointer;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>

    </div>
</nav>

<script>
document.getElementById('hamburgerBtn')?.addEventListener('click', function() {
    document.getElementById('navMenu')?.classList.toggle('show');
});
</script>