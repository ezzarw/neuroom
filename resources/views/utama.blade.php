<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Neuroom - Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
<x-navbar />

<div id="loginToast" class="toast">
    <div class="toast-content">
        <div>
            <strong>Login berhasil</strong>
            <p>Selamat datang di Neuroom</p>
        </div>
        <button id="toastClose" type="button" aria-label="Tutup notifikasi">&times;</button>
    </div>
</div>

<main class="dashboard">
    <div class="container">
        <section class="card welcome">
            <div class="welcome-left">
                <h1>
                    Selamat datang kembali,
                    <strong id="username">{{ auth()->user()->username }}</strong>
                </h1>

                <p>Lanjutkan progres belajarmu hari ini bersama Neuroom.</p>

                <div class="stats">
                    <div class="stat">
                        <span>Total Materi</span>
                        <strong id="total-materi">0</strong>
                    </div>

                    <div class="stat">
                        <span>Catatan</span>
                        <strong id="total-catatan">0</strong>
                    </div>
                </div>
            </div>

            <aside class="card recommend-card">
                <div class="title-row">
                    <h3>Rekomendasi Untukmu</h3>
                </div>

                <div class="list">
                    <a href="/belajar" class="item clickable">
                        <div>
                            <strong>Ringkas Materi</strong>
                            <span>Upload dokumen dan buat ringkasan</span>
                        </div>
                        <span class="badge">AI</span>
                    </a>

                    <a href="/catatan" class="item clickable">
                        <div>
                            <strong>Buka Catatan</strong>
                            <span>Lihat catatan yang pernah dibuat</span>
                        </div>
                        <span class="badge">Catatan</span>
                    </a>

                    <a href="/fokus" class="item clickable">
                        <div>
                            <strong>Mode Fokus</strong>
                            <span>Mulai sesi pomodoro belajar</span>
                        </div>
                        <span class="badge">Fokus</span>
                    </a>
                </div>
            </aside>
        </section>

        <section class="card activity-card">
            <div class="title-row">
                <h3>Aktivitas Terbaru</h3>
            </div>

            <div class="list" id="activity-list">
                @forelse ($activities ?? [] as $activity)
                    <div class="item">
                        <div class="activity-info">
                            <strong>{{ $activity['title'] }}</strong>
                            <span>{{ $activity['time'] }}</span>
                        </div>

                        <span class="badge">{{ $activity['status'] }}</span>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>Belum ada aktivitas terbaru.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</main>

<script src="{{ asset('js/stateful-api.js') }}"></script>
<script>
const params = new URLSearchParams(window.location.search);
const toast = document.getElementById('loginToast');
const toastClose = document.getElementById('toastClose');

if (params.get('login') === 'success' && toast) {
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);

    const url = new URL(window.location.href);
    url.searchParams.delete('login');
    window.history.replaceState({}, '', url);
}

toastClose?.addEventListener('click', () => {
    toast?.classList.remove('show');
});

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

async function loadDashboard() {
    try {
        const response = await window.NeuroomApi.request('/api/v1/auth/me');

        if (!response.data) return;

        document.getElementById('username').textContent = response.data.username ?? '-';

        const stats = response.data.stats;
        if (stats) {
            document.getElementById('total-materi').textContent = stats.total_materi ?? 0;
            document.getElementById('total-catatan').textContent = stats.total_catatan ?? 0;
        }

        const activities = response.data.activities;
        if (Array.isArray(activities) && activities.length > 0) {
            const activityList = document.getElementById('activity-list');
            activityList.innerHTML = activities.map((activity) => `
                <div class="item">
                    <div class="activity-info">
                        <strong>${escapeHtml(activity.title)}</strong>
                        <span>${escapeHtml(activity.time)}</span>
                    </div>
                    <span class="badge">${escapeHtml(activity.status)}</span>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Dashboard API Error:', error);
    }
}

loadDashboard();
</script>
</body>
</html>
