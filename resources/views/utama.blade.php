<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Neuroom — Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* ===== Dashboard extra styles (boleh pindah ke style.css) ===== */

        .dashboard-hero {
            padding: 110px 0 40px;
            background: radial-gradient(800px 400px at 20% 20%, rgba(111, 66, 193, .18), transparent 60%),
                radial-gradient(900px 500px at 80% 30%, rgba(0, 255, 209, .10), transparent 60%);
        }

        .welcome-card {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 24px;
            align-items: center;
            padding: 26px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.10);
        }

        .welcome-card h1 {
            margin: 0 0 8px;
            font-size: 34px;
            line-height: 1.2;
        }

        .welcome-card p {
            margin: 0 0 18px;
            opacity: .9;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-top: 18px;
        }

        .qa {
            padding: 14px 14px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(0, 0, 0, 0.18);
        }

        .qa h4 {
            margin: 0 0 6px;
            font-size: 14px;
            letter-spacing: .2px;
        }

        .qa p {
            margin: 0;
            font-size: 13px;
            opacity: .85;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-top: 22px;
        }

        .stat-card {
            padding: 16px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.06);
        }

        .stat-card .label {
            font-size: 12px;
            opacity: .8;
        }

        .stat-card .value {
            font-size: 20px;
            margin-top: 6px;
            font-weight: 700;
        }

        .section-title-row {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 12px;
        }

        .activity {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .panel {
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(255, 255, 255, 0.05);
            padding: 18px;
        }

        .list {
            display: grid;
            gap: 10px;
            margin-top: 10px;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(0, 0, 0, 0.15);
            font-size: 14px;
        }

        .badge {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            opacity: .9;
            white-space: nowrap;
        }

        /* ===== Login success toast ===== */
        .toast {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 9999;
            width: min(360px, calc(100% - 36px));
            padding: 14px 14px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(20, 20, 20, 0.88);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
            display: none;
        }

        .toast.show {
            display: block;
            animation: pop .22s ease-out;
        }

        @keyframes pop {
            from {
                transform: translateY(-8px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .toast .row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .toast strong {
            display: block;
            margin-bottom: 4px;
        }

        .toast p {
            margin: 0;
            font-size: 13px;
            opacity: .9;
        }

        .toast button {
            border: none;
            background: transparent;
            color: inherit;
            font-size: 18px;
            cursor: pointer;
            line-height: 1;
            opacity: .8;
        }

        @media (max-width: 900px) {
            .welcome-card {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .activity {
                grid-template-columns: 1fr;
            }
        }
    </style>
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

            <div style="display:flex; gap:10px; align-items:center;">
                <span style="opacity:.9; font-size:14px;">
                    Halo, <strong>{{ auth()->user()->username ?? (auth()->user()->name ?? 'User') }}</strong>
                </span>
                <form action="/auth/logout" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- TOAST LOGIN SUCCESS -->
    <div id="loginToast" class="toast" role="status" aria-live="polite">
        <div class="row">
            <div>
                <strong>Login berhasil ✅</strong>
                <p>Selamat datang di Neuroom. Yuk lanjut belajar!</p>
            </div>
            <button type="button" id="toastClose" aria-label="Tutup">&times;</button>
        </div>
    </div>

    <!-- DASHBOARD HERO -->
    <section class="dashboard-hero">
        <div class="container">
            <div class="welcome-card">
                <div>
                    <h1>Selamat datang kembali 👋</h1>
                    <p>
                        Mulai dari upload materi, bikin rangkuman, sampai mode fokus — semuanya dari sini.
                    </p>

                    <div class="quick-actions">
                        <a class="qa" href="/belajar">
                            <h4>📄 Upload Materi</h4>
                            <p>Unggah PDF/DOCX/PPT untuk diproses AI</p>
                        </a>
                        <a class="qa" href="/catatan">
                            <h4>📝 Catatan</h4>
                            <p>Lihat catatan otomatis & manual kamu</p>
                        </a>
                        <a class="qa" href="/fokus">
                            <h4>⏱️ Mode Fokus</h4>
                            <p>Pomodoro + tracking sesi belajar</p>
                        </a>
                    </div>

                    <div class="stats">
                        <div class="stat-card">
                            <div class="label">Materi dipelajari</div>
                            <div class="value">12</div>
                        </div>
                        <div class="stat-card">
                            <div class="label">Kuis selesai</div>
                            <div class="value">7</div>
                        </div>
                        <div class="stat-card">
                            <div class="label">Streak belajar</div>
                            <div class="value">4 hari</div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <h3 style="margin:0 0 6px;">Rekomendasi hari ini</h3>
                    <p style="margin:0; opacity:.9;">
                        Lanjutkan materi terakhir atau mulai sesi fokus 25 menit.
                    </p>

                    <div class="list">
                        <div class="list-item">
                            <div>
                                <strong>Materi: Sistem Saraf</strong><br>
                                <span style="opacity:.8; font-size:13px;">Rangkuman sudah siap</span>
                            </div>
                            <span class="badge">Lanjut</span>
                        </div>

                        <div class="list-item">
                            <div>
                                <strong>Kuis: Evaluasi Bab 1</strong><br>
                                <span style="opacity:.8; font-size:13px;">10 soal • 8 menit</span>
                            </div>
                            <span class="badge">Mulai</span>
                        </div>

                        <div class="list-item">
                            <div>
                                <strong>Mode Fokus</strong><br>
                                <span style="opacity:.8; font-size:13px;">25 menit Pomodoro</span>
                            </div>
                            <span class="badge">Start</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top:24px;" class="activity">
                <div class="panel">
                    <div class="section-title-row">
                        <h3 style="margin:0;">Aktivitas Terakhir</h3>
                        <a href="/belajar" style="font-size:14px;">Lihat semua</a>
                    </div>
                    <div class="list">
                        <div class="list-item">
                            <div>Upload “Biologi Dasar.pdf”</div>
                            <span class="badge">Selesai</span>
                        </div>
                        <div class="list-item">
                            <div>Catatan otomatis dibuat</div>
                            <span class="badge">AI</span>
                        </div>
                        <div class="list-item">
                            <div>Kuis “Bab 1” dikerjakan</div>
                            <span class="badge">80%</span>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="section-title-row">
                        <h3 style="margin:0;">Target Mingguan</h3>
                        <a href="/fokus" style="font-size:14px;">Atur target</a>
                    </div>
                    <div class="list">
                        <div class="list-item">
                            <div>3 sesi fokus</div>
                            <span class="badge">1/3</span>
                        </div>
                        <div class="list-item">
                            <div>2 kuis evaluasi</div>
                            <span class="badge">0/2</span>
                        </div>
                        <div class="list-item">
                            <div>1 upload materi</div>
                            <span class="badge">1/1</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <script>
        // Tampilkan toast jika URL punya ?login=success
        const params = new URLSearchParams(window.location.search);
        const toast = document.getElementById('loginToast');
        const closeBtn = document.getElementById('toastClose');

        function showToast() {
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
        }

        if (params.get('login') === 'success') {
            showToast();
            // bersihin query biar ga muncul lagi saat refresh
            const url = new URL(window.location.href);
            url.searchParams.delete('login');
            window.history.replaceState({}, '', url.toString());
        }

        closeBtn.addEventListener('click', () => toast.classList.remove('show'));
    </script>

</body>

</html>
