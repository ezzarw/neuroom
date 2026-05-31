<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Neuroom — Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>

<body>

<!-- NAVBAR -->
<x-navbar />

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

<!-- DASHBOARD -->
<section class="dashboard">
    <div class="container">

        <!-- WELCOME -->
        <div class="card welcome">

            <div class="welcome-left">
                <h1>
                    Selamat datang kembali,
                    <strong id="username">
                        {{ auth()->user()->username }}
                    </strong>
                </h1>

                <p>
                    Lanjutkan progres belajarmu hari ini bersama Neuroom.
                </p>

                <div class="stats">

                    <div class="stat">
                        <span>Total Materi</span>
                        <strong id="total-materi">12</strong>
                    </div>

                    <div class="stat">
                        <span>Quiz Selesai</span>
                        <strong id="quiz-selesai">7</strong>
                    </div>

                    <div class="stat">
                        <span>Catatan</span>
                        <strong id="total-catatan">15</strong>
                    </div>

                </div>
            </div>

            <!-- REKOMENDASI -->
            <div class="card small recommend-card">

                <div class="title-row">
                    <h3>Rekomendasi Untukmu</h3>
                </div>

                <div class="list">

                    <!-- QUIZ -->
                    <a href="/belajar" class="item clickable">
                        <div>
                            <strong>Belajar & Quiz</strong>
                            <span>Belajar dan Kerjakan quiz terakhir kamu</span>
                        </div>

                        <span class="badge">Quiz</span>
                    </a>

                    <!-- CATATAN -->
                    <a href="/catatan" class="item clickable">
                        <div>
                            <strong>Buka Catatan</strong>
                            <span>Lihat catatan yang pernah dibuat</span>
                        </div>

                        <span class="badge">Catatan</span>
                    </a>

                    <!-- FOKUS -->
                    <a href="/fokus" class="item clickable">
                        <div>
                            <strong>Mode Fokus</strong>
                            <span>Mulai sesi pomodoro belajar</span>
                        </div>

                        <span class="badge">Fokus</span>
                    </a>

                </div>
            </div>

        </div>

        <!-- AKTIVITAS -->
        <div class="card activity-card">

            <div class="title-row">
                <h3>Aktivitas Terbaru</h3>
                <a href="/history">Lihat Semua</a>
            </div>

            <div class="list" id="activity-list">

                {{-- fallback awal --}}
                @forelse ($activities ?? [] as $activity)

                    <div class="item">

                        <div class="activity-info">
                            <strong>{{ $activity['title'] }}</strong>
                            <span>{{ $activity['time'] }}</span>
                        </div>

                        <span class="badge">
                            {{ $activity['status'] }}
                        </span>

                    </div>

                @empty

                    <div class="empty-state">
                        <p>Belum ada aktivitas terbaru.</p>
                    </div>

                @endforelse

            </div>

        </div>

    </div>
</section>

<!-- API -->
<script src="{{ asset('js/stateful-api.js') }}"></script>

<script>

// ======================
// TOAST LOGIN
// ======================

const params = new URLSearchParams(window.location.search);
const toast = document.getElementById('loginToast');

if (params.get('login') === 'success') {

    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);

    const url = new URL(window.location.href);
    url.searchParams.delete('login');

    window.history.replaceState({}, '', url);
}

document
    .getElementById('toastClose')
    .addEventListener('click', () => {
        toast.classList.remove('show');
    });


// ======================
// DASHBOARD API
// ======================

async function loadDashboard() {

    try {

        const response = await window.NeuroomApi.request(
            '/api/v1/auth/me'
        );

        // USERNAME
        if (response.data) {
            document.getElementById('username').textContent =
                response.data.username;

            // STATS
            const stats = response.data.stats;
            if (stats) {
                document.getElementById('total-materi').textContent =
                    stats.total_materi ?? 0;

                document.getElementById('quiz-selesai').textContent =
                    stats.quiz_selesai ?? 0;

                document.getElementById('total-catatan').textContent =
                    stats.total_catatan ?? 0;
            }

            // ACTIVITIES
            const activities = response.data.activities;
            if (Array.isArray(activities) && activities.length > 0) {
                const activityList = document.getElementById('activity-list');
                activityList.innerHTML = '';

                activities.forEach(activity => {
                    activityList.innerHTML += `
                        <div class="item">
                            <div class="activity-info">
                                <strong>${activity.title}</strong>
                                <span>${activity.time}</span>
                            </div>
                            <span class="badge">
                                ${activity.status}
                            </span>
                        </div>
                    `;
                });
            }
        }

    } catch (error) {

        console.error('Dashboard API Error:', error);

    }
}

loadDashboard();

</script>

</body>
</html>