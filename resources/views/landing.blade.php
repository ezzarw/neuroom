<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Neuroom — Learning Platform</title>

  <!-- CSS -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="container nav-content">
    <div class="logo">Neuroom</div>

    <ul class="nav-menu">
      <li><a href="/">Beranda</a></li>
      <li><a href="/belajar">Belajar</a></li>
      <li><a href="/fokus">Fokus</a></li>
      <li><a href="/catatan">Catatan</a></li>
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
        catatan otomatis, kuis evaluasi, serta mode fokus
        dalam satu platform pintar.
      </p>
      <a href="#login-popup" class="btn-primary">Mulai Belajar</a>
    </div>

    <video class="hero-video" autoplay muted loop playsinline>
  <source src="{{ asset('storage/video-ukl.mp4') }}" type="video/mp4">
</video>
    </div>
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
          <p>Unggah file PDF, DOCX, atau PPT yang ingin kamu pelajari.</p>
        </div>
      </div>

      <div class="workflow-line"></div>

      <div class="workflow-step">
        <div class="step-number">2</div>
        <div class="step-content">
          <h3>AI Memproses Materi</h3>
          <p>Sistem AI akan membuat rangkuman, catatan otomatis, dan kuis.</p>
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
      <div class="card">Kuis Otomatis & Evaluasi</div>
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
        <li><a href="#">AI Rangkuman</a></li>
        <li><a href="#">Kuis Otomatis</a></li>
        <li><a href="#">Mode Fokus</a></li>
        <li><a href="#">Latihan IT</a></li>
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

    <form>
      <input type="email" placeholder="Email" required>
      <input type="password" placeholder="Password" required>
      <button class="btn-primary full">Login</button>
    </form>

    <p class="popup-footer">
      Belum punya akun?
      <a href="#register-popup">Daftar</a>
    </p>
  </div>
</div>

<!-- POPUP REGISTER -->
<div id="register-popup" class="popup-overlay">
  <div class="popup-box">
    <a href="#" class="popup-close">&times;</a>

    <h2>Daftar</h2>
    <p class="popup-desc">Buat akun untuk mulai belajar</p>

    <form>
      <input type="text" placeholder="Nama Lengkap" required>
      <input type="email" placeholder="Email" required>
      <input type="password" placeholder="Password" required>
      <input type="password" placeholder="Konfirmasi Password" required>
      <button class="btn-primary full">Daftar</button>
    </form>

    <p class="popup-footer">
      Sudah punya akun?
      <a href="#login-popup">Login</a>
    </p>
  </div>
</div>

</body>
</html>
