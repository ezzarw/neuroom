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
                <source src="{{ asset('video/video-ukl.mp4') }}" type="video/mp4">
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

            <form id="register-form">
                <input type="text" placeholder="Nama Lengkap" name="fullname" required>
                <input type="email" placeholder="Email" name="email" required>
                <input type="password" placeholder="Password" name="password" required>
                <input type="password" placeholder="Konfirmasi Password" name="password_confirmation" required>
                <button class="btn-primary full">Daftar</button>
            </form>
            <div id="register-result"></div>

            <p class="popup-footer">
                Sudah punya akun?
                <a href="#login-popup">Login</a>
            </p>
        </div>
    </div>

<script>
  const registerForm = document.getElementById('register-form');
  const registerResult = document.getElementById('register-result');

  const loginForm = document.querySelector('#login-popup form');
  const loginResult = document.getElementById('login-result') || (() => {
    const el = document.createElement('div');
    el.id = 'login-result';
    el.style.marginTop = '10px';
    el.style.fontSize = '14px';
    loginForm.parentElement.insertBefore(el, loginForm.nextSibling);
    return el;
  })();

  async function requestJson(url, method, payload = null, token = null) {
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token) headers['Authorization'] = `Bearer ${token}`;

    const res = await fetch(url, {
      method,
      headers,
      body: payload ? JSON.stringify(payload) : null,
    });

    let data = null;
    try { data = await res.json(); } catch (e) {}

    return { ok: res.ok, status: res.status, data };
  }

  function firstValidationError(data) {
    if (!data?.errors) return null;
    const flat = Object.values(data.errors).flat();
    return flat?.[0] || null;
  }

  // -------- REGISTER (POST /api/auth/register) --------
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    registerResult.textContent = 'Loading...';

    if (registerForm.password.value !== registerForm.password_confirmation.value) {
      registerResult.textContent = '';
      alert("Password dan konfirmasi tidak sama");
      return;
    }

    // sesuai dokumentasi: username, email, password
    const payload = {
      username: registerForm.fullname.value.trim(), // map fullname -> username ✅
      email: registerForm.email.value.trim(),
      password: registerForm.password.value
    };

    const { ok, status, data } = await requestJson('/api/auth/register', 'POST', payload);

    if (ok && data?.status === true) {
      // response sukses: { status:true, data:{...}, token:"...", token_type:"Bearer" }
      if (data?.token) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('token_type', data.token_type || 'Bearer');
      }

      registerResult.textContent = 'Register berhasil ✅ Silakan login.';
      window.location.hash = '#login-popup';
      registerForm.reset();
      return;
    }

    if (status === 422) {
      registerResult.textContent = firstValidationError(data) || data?.message || 'Validasi gagal (422)';
      return;
    }
    if (status === 429) {
      registerResult.textContent = 'Terlalu banyak request. Coba lagi sebentar ya (429).';
      return;
    }

    registerResult.textContent = data?.message || `Register gagal (HTTP ${status})`;
  });

  // -------- LOGIN (POST /api/auth/login) --------
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    loginResult.textContent = 'Loading...';

    const emailEl = loginForm.querySelector('input[type="email"]');
    const passEl = loginForm.querySelector('input[type="password"]');

    const payload = {
      email: (emailEl?.value || '').trim(),
      password: passEl?.value || ''
    };

    const { ok, status, data } = await requestJson('/api/auth/login', 'POST', payload);

    if (ok && data?.status === true) {
      // response sukses: { status:true, data:{...}, token:"...", token_type:"Bearer" }
      localStorage.setItem('token', data.token);
      localStorage.setItem('token_type', data.token_type || 'Bearer');

      loginResult.textContent = 'Login berhasil ✅ Token tersimpan.';
      window.location.hash = '#'; // tutup popup

      // optional redirect
      // window.location.href = '/belajar';
      return;
    }

    if (status === 401) {
      loginResult.textContent = data?.message || 'Email / password salah (401).';
      return;
    }
    if (status === 422) {
      loginResult.textContent = firstValidationError(data) || data?.message || 'Validasi gagal (422)';
      return;
    }
    if (status === 429) {
      loginResult.textContent = 'Terlalu banyak percobaan login. Coba lagi sebentar ya (429).';
      return;
    }

    loginResult.textContent = data?.message || `Login gagal (HTTP ${status})`;
  });

  // -------- TEST admin endpoint (GET /api/admin/user-view) --------
  // panggil di console: testAdminUserView()
  window.testAdminUserView = async function () {
    const token = localStorage.getItem('token');
    const tokenType = localStorage.getItem('token_type') || 'Bearer';
    if (!token) {
      console.warn('Token belum ada. Login dulu ya.');
      return { ok: false, status: 0, data: { message: 'Token belum ada' } };
    }
    const res = await requestJson('/api/admin/user-view', 'GET', null, token);
    console.log('Admin user-view response:', res);
    return res;
  };
</script>

</body>

</html>
