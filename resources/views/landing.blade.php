<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Neuroom — Learning Platform</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <div id="page-feedback" style="display:none; max-width: 1100px; margin: 16px auto 0; padding: 12px 16px; border-radius: 12px; font-weight: 600;"></div>

<nav class="navbar navbar-landing">
  <div class="container nav-content">

    <div class="logo">Neuroom</div>

    <button class="hamburger" id="landingHamburgerBtn" type="button" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>

    <ul class="nav-menu" id="landingNavMenu">
      <li><a href="/">Beranda</a></li>
      <li><a href="#login-popup" data-target="/belajar">Belajar</a></li>
      <li><a href="#login-popup" data-target="/fokus">Fokus</a></li>
      <li><a href="#login-popup" data-target="/catatan">Catatan</a></li>
    </ul>

    <a href="#login-popup" class="btn-primary">Mulai Belajar</a>

  </div>
</nav>

    <!-- HERO -->
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <h1>Ubah Materi Jadi Lebih Mudah Dipahami</h1>
                <p>
                    Upload materi belajar dan dapatkan rangkuman,
                    catatan otomatis, serta mode fokus
                    dalam satu platform pintar.
                </p>
                <a href="#login-popup" class="btn-primary">Mulai Belajar</a>
            </div>

            <video class="hero-video" autoplay muted loop playsinline>
                <source src="{{ asset('video/video-ukl.mp4') }}" type="video/mp4">
            </video>
        </div>
    </section>

    <!-- CARA KERJA -->
    <section class="section">
        <div class="container">
            <h2>Cara Kerja Neuroom</h2>

            <div class="workflow">
                <div class="workflow-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Upload Materi</h3>
                        <p>Unggah file PDF, TXT, atau gambar yang ingin kamu pelajari.</p>
                    </div>
                </div>

                <div class="workflow-line"></div>

                <div class="workflow-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>AI Memproses Materi</h3>
                        <p>Sistem AI akan membuat rangkuman, catatan otomatis.</p>
                    </div>
                </div>

                <div class="workflow-line"></div>

                <div class="workflow-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Belajar Lebih Fokus</h3>
                        <p>Gunakan mode fokus untuk belajar lebih terarah dan efisien.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR -->
    <section class="section light">
        <div class="container">
            <h2>Fitur Utama</h2>

            <div class="features">
                <div class="card">AI Rangkuman Materi</div>
                <div class="card">Catatan Otomatis & Manual</div>
                <div class="card">Catatan & Ringkasan</div>
                <div class="card">Mode Fokus (Pomodoro)</div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <h3>Neuroom</h3>
                <p>
                    Platform pembelajaran berbasis AI untuk membantu
                    pelajar belajar lebih fokus, efektif, dan terarah.
                </p>
            </div>

            <div class="footer-links">
                <h4>Menu</h4>
                <ul>
                    <li><a href="/">Beranda</a></li>
                    <li><a href="/belajar">Belajar</a></li>
                    <li><a href="/fokus">Fokus</a></li>
                    <li><a href="/catatan">Catatan</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Produk</h4>
                <ul>
                    <li><a href="/belajar">AI Rangkuman</a></li>
                    <li><a href="/belajar">Catatan Otomatis</a></li>
                    <li><a href="/fokus">Mode Fokus</a></li>
                    <li><a href="/belajar">Latihan IT</a></li>
                </ul>
            </div>

            <div class="footer-cta">
                <h4>Mulai Sekarang</h4>
                <p>Tingkatkan cara belajarmu bersama Neuroom.</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2026 Neuroom. All rights reserved.</p>
        </div>
    </footer>

    <!-- POPUP LOGIN -->
    <div id="login-popup" class="popup-overlay">
        <div class="popup-box">
            <a href="#" class="popup-close">&times;</a>

            <h2>Masuk</h2>
            <p class="popup-desc">Masuk untuk mulai belajar lebih fokus</p>
            <div id="login-feedback" style="display:none; margin-bottom: 12px; padding: 10px 12px; border-radius: 10px; background: #fee2e2; color: #991b1b; font-size: 14px;"></div>

            <form method="POST" action="/api/v1/auth/login" id="login-form">
                @csrf
                <input type="email" placeholder="Email" name="email" autocomplete="email" required>
                <div class="password-field">
                    <input type="password" placeholder="Password" name="password" id="login-password" autocomplete="current-password" required>
                    <span class="toggle-pw" onclick="togglePassword('login-password', this)">Lihat</span>
                </div>
                <div style="text-align: right; margin: -8px 0 14px; font-size: 13px;">
                    <a href="#lupapassword-popup" style="color: #5FA8A3; text-decoration: none;">Lupa Password?</a>
                </div>
                <button type="submit" class="btn-primary full" id="login-submit">Login</button>
            </form>

            <p class="popup-footer">
                Belum punya akun?
                <a href="#register-popup">Daftar</a>
            </p>
        </div>
    </div>

<!-- LUPA PASSWORD POPUP -->
<div id="lupapassword-popup" class="popup-overlay">
    <div class="popup-card">
        <a href="#" class="popup-close">&times;</a>
        <h2>Lupa Password</h2>
        <p class="popup-desc">Fitur reset password belum aktif di aplikasi ini.</p>
        <form>
            <input type="email" placeholder="Email" name="email" autocomplete="email" required>
            <button type="button" class="btn-primary full">Kirim Tautan Reset</button>
        </form>
        <p class="popup-footer">
            Ingat password? <a href="#login-popup">Login</a>
        </p>
    </div>
</div>

    <!-- POPUP REGISTER -->
    <div id="register-popup" class="popup-overlay">
        <div class="popup-box">
            <a href="#" class="popup-close">&times;</a>

            <h2>Daftar</h2>
            <p class="popup-desc">Buat akun untuk mulai belajar</p>
            <div id="register-feedback" style="display:none; margin-bottom: 12px; padding: 10px 12px; border-radius: 10px; background: #fee2e2; color: #991b1b; font-size: 14px;"></div>

            <form method="POST" action="/api/v1/auth/register" id="register-form">
                @csrf
                <input type="text" placeholder="Nama Lengkap" name="username" autocomplete="name" required>
                <input type="email" placeholder="Email" name="email" autocomplete="email" required>
                <div class="password-field">
                    <input type="password" placeholder="Password" name="password" id="reg-password" autocomplete="new-password" required>
                    <span class="toggle-pw" onclick="togglePassword('reg-password', this)">Lihat</span>
                </div>
                <div class="password-field">
                    <input type="password" placeholder="Konfirmasi Password" name="password_confirmation" id="reg-password-confirm" autocomplete="new-password" required>
                    <span class="toggle-pw" onclick="togglePassword('reg-password-confirm', this)">Lihat</span>
                </div>
                <button type="submit" class="btn-primary full" id="register-submit">Daftar</button>
            </form>

            <p class="popup-footer">
                Sudah punya akun?
                <a href="#login-popup">Login</a>
            </p>
        </div>
    </div>

    <!-- SUCCESS ALERT MODAL -->
    <div id="success-alert" class="success-alert-overlay">
        <div class="success-alert-box">
            <div class="success-checkmark">
                <svg viewBox="0 0 52 52" class="checkmark-circle">
                    <circle cx="26" cy="26" r="25" fill="none" stroke-width="2"/>
                    <path d="M14.1 27.2l7.1 7.2 16.7-16.8" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
            </div>
            <h2 id="success-title">Berhasil</h2>
            <p id="success-message"></p>
            <button id="success-btn" class="btn-primary" style="margin-top: 20px; width: 100%;">Lanjut</button>
        </div>
    </div>

    <!-- SCRIPT -->
    <script src="{{ asset('js/stateful-api.js') }}" defer></script>
    <script src="{{ asset('js/landing-auth.js') }}" defer></script>
    <script>
        function togglePassword(fieldId, el) {
            var input = document.getElementById(fieldId);
            if (input.type === "password") {
                input.type = "text";
                el.textContent = "Tutup";
            } else {
                input.type = "password";
                el.textContent = "Lihat";
            }
        }

        document.getElementById('landingHamburgerBtn')?.addEventListener('click', function () {
            document.getElementById('landingNavMenu')?.classList.toggle('show');
        });
    </script>
</body>

</html>
