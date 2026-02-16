<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Auth Test</title>
  <style>
    :root { color-scheme: light; }
    body { font-family: Arial, sans-serif; margin: 24px; background: #f7f7f7; }
    .wrap { max-width: 720px; margin: 0 auto; }
    h1 { margin-bottom: 8px; }
    .card { background: #fff; padding: 16px; border: 1px solid #e5e5e5; border-radius: 8px; margin-bottom: 16px; }
    label { display: block; margin: 8px 0 4px; }
    input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; }
    button { margin-top: 12px; padding: 8px 12px; border: 0; background: #111; color: #fff; border-radius: 6px; cursor: pointer; }
    .result { margin-top: 8px; font-size: 14px; color: #333; }
    .muted { color: #666; font-size: 13px; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Auth Test</h1>
    <p class="muted">Halaman sederhana untuk tes API register dan login.</p>

    <div class="card">
      <h2>Register</h2>
      <form id="register-form">
        <label>Nama Lengkap</label>
        <input type="text" name="display_name" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirmation" required>

        <button type="submit">Register</button>
      </form>
      <div id="register-result" class="result"></div>
    </div>

    <div class="card">
      <h2>Login</h2>
      <form id="login-form">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
      </form>
      <div id="login-result" class="result"></div>
    </div>
  </div>

  <script>
    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login-form');
    const registerResult = document.getElementById('register-result');
    const loginResult = document.getElementById('login-result');

    async function postJson(url, payload) {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      let data = null;
      try { data = await res.json(); } catch (e) {}
      return { ok: res.ok, status: res.status, data };
    }

    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      registerResult.textContent = 'Loading...';

      if (registerForm.password.value !== registerForm.password_confirmation.value) {
        registerResult.textContent = 'Password dan konfirmasi tidak sama.';
        return;
      }

      const payload = {
        display_name: registerForm.display_name.value.trim(),
        username: registerForm.username.value.trim(),
        email: registerForm.email.value.trim(),
        password: registerForm.password.value,
      };

      const { ok, status, data } = await postJson('/api/auth/register', payload);
      registerResult.textContent = ok
        ? 'Register berhasil'
        : (data?.message || `Register gagal (HTTP ${status})`);
    });

    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      loginResult.textContent = 'Loading...';

      const payload = {
        email: loginForm.email.value.trim(),
        password: loginForm.password.value,
      };

      const { ok, status, data } = await postJson('/api/auth/login', payload);
      loginResult.textContent = ok
        ? 'Login berhasil'
        : (data?.message || `Login gagal (HTTP ${status})`);
    });
  </script>
</body>
</html>
