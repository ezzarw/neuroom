<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neuroom — Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container nav-content">
        <div class="logo">Neuroom</div>

        <ul class="nav-menu">
            <li><a href="/belajar">Belajar</a></li>
            <li><a href="/fokus">Fokus</a></li>
            <li><a href="/catatan">Catatan</a></li>
        </ul>

        <a href="{{ route('profile') }}" class="user-link">
            Halo, <strong>{{ auth()->user()->username }}</strong>
        </a>
    </div>
</nav>

<!-- TOAST -->
<div id="loginToast" class="toast">
    <div class="toast-content">
        <div>
            <strong>Login berhasil ✅</strong>
            <p>Selamat datang di Neuroom</p>
        </div>
        <button id="toastClose">&times;</button>
    </div>
</div>

<!-- HERO -->
<section class="dashboard">
    <div class="container">

        <!-- WELCOME -->
        <div class="card welcome">
            <div>
                <h1>Selamat datang kembali, <strong>{{ auth()->user()->username }}</strong> </h1>
                <p>Mulai belajar atau lanjutkan progresmu.</p>

                <div class="stats">
                    <div class="stat">
                        <span>Materi</span>
                        <strong>12</strong>
                    </div>
                    <div class="stat">
                        <span>Kuis</span>
                        <strong>7</strong>
                    </div>
                </div>
            </div>

            <div class="card small">
                <h3>Rekomendasi</h3>

                <div class="list">
                    <div class="item">
                        <div>
                            <strong>Sistem Saraf</strong>
                            <span>Rangkuman siap</span>
                        </div>
                        <span class="badge">Lanjut</span>
                    </div>

                    <div class="item">
                        <div>
                            <strong>Kuis Bab 1</strong>
                            <span>10 soal</span>
                        </div>
                        <span class="badge">Mulai</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTIVITY -->
        <div class="card">
            <div class="title-row">
                <h3>Aktivitas</h3>
                <a href="/belajar">Lihat</a>
            </div>

            <div class="list">
                <div class="item">
                    <span>Upload file</span>
                    <span class="badge">Selesai</span>
                </div>

                <div class="item">
                    <span>Catatan dibuat</span>
                    <span class="badge">AI</span>
                </div>

                <div class="item">
                    <span>Kuis dikerjakan</span>
                    <span class="badge">80%</span>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
const params = new URLSearchParams(window.location.search);
const toast = document.getElementById('loginToast');

if (params.get('login') === 'success') {
    toast.classList.add('show');

    setTimeout(() => toast.classList.remove('show'), 3000);

    const url = new URL(window.location.href);
    url.searchParams.delete('login');
    window.history.replaceState({}, '', url);
}

document.getElementById('toastClose')
    .addEventListener('click', () => toast.classList.remove('show'));
</script>

</body>
</html>