# Tugas Biar Aman

Dokumen ini merangkum apa yang sudah diamankan di Neuroom dan apa yang masih perlu dijaga setelah backend dan frontend disinkronkan ke kontrak API terbaru.

## Yang Sudah Aman

- autentikasi tetap berbasis session backend
- proteksi admin tetap dicek di server oleh `admin.validate`
- frontend tidak perlu memegang bearer token
- route API resmi sudah dipusatkan ke `/api/v1/...`
- response JSON backend sudah seragam
- frontend fetch utama sudah diarahkan ke kontrak JSON baru

## Yang Baru Selesai Diupdate

- route `api/v1` untuk auth, profile, summary, pomodoro, dan admin aktif kembali
- helper response JSON standar sudah dipasang di base controller
- validasi dan auth error JSON sudah konsisten dari layer exception
- frontend sekarang membaca:
  - `response.data`
  - `response.errors`
- dokumentasi kontrak API sekarang ada di `docs/API_RESPONSE_JSON.md`

## Risiko Yang Harus Tetap Dijaga

### 1. Jangan percaya auth state lokal

Yang tidak boleh jadi sumber kebenaran:

- `auth_id`
- `auth_email`
- `auth_username`
- `auth_is_admin`
- flag login apa pun di `localStorage`

Yang benar:

- status login ditentukan backend
- akses admin ditentukan middleware server
- cookie session browser yang membawa konteks login

### 2. Jangan kembali ke endpoint lama

Yang harus dipakai:

- `/api/v1/...`

Yang harus dihindari:

- `/api/auth/...`
- `/api/admin/...`
- `/api/me`
- `/api/summary`
- `/api/pomodoro/history`

### 3. Jangan biarkan shape JSON liar

Yang harus dipakai:

- `success`
- `reason`
- `data`
- `errors`

Yang harus dihindari:

- top-level `user`, `users`, `sessions`, `stats`, `redirect_to` tanpa pembungkus

### 4. Runtime server tetap penting

Masalah yang masih bisa bikin aplikasi terlihat rusak padahal bukan bug aplikasi:

- docroot tidak ke `public/`
- versi PHP CLI beda dengan web server
- extension DB/test tidak lengkap

## Flow Aman Yang Dipertahankan

1. User login lewat endpoint auth berbasis session.
2. Backend memvalidasi kredensial.
3. Backend menyimpan session.
4. Browser otomatis membawa cookie session.
5. Frontend fetch data ke `/api/v1/...` dengan CSRF token.
6. Route admin tetap dijaga server.
7. Logout tetap dilakukan dari backend.

## Checklist Saat Ini

### Backend

- [x] Route API resmi ada di `routes/api.php`
- [x] Prefix API dipusatkan ke `/api/v1`
- [x] Response JSON distandardkan
- [x] Error validasi JSON distandardkan
- [x] Error auth/admin JSON distandardkan
- [ ] Jalankan ulang feature test setelah driver DB test tersedia

### Frontend

- [x] Helper fetch memakai cookie session + CSRF
- [x] Endpoint utama diarahkan ke `/api/v1`
- [x] Payload dibaca dari `data`
- [ ] Smoke test ulang semua halaman setelah perubahan kontrak
- [ ] Cari sisa file JS yang masih memanggil endpoint lama

## Langkah Lanjut Yang Disarankan

1. Aktifkan `pdo_sqlite` atau siapkan DB test khusus.
2. Jalankan ulang test feature API.
3. Lakukan smoke test login, profile, summary, pomodoro, admin dashboard, admin users, dan admin pomodoro.
4. Hapus referensi dokumentasi atau kode yang masih menyebut flow lama.
