# Tugas Biar Aman

Dokumen ini merangkum hal yang sudah diamankan dan hal yang masih perlu dijaga setelah Neuroom kembali ke flow web Laravel biasa.

## Yang Sudah Aman Dibanding Sebelumnya

Perubahan yang sudah selesai:

- route aplikasi tidak lagi bergantung pada `routes/api.php`
- autentikasi memakai session web, bukan bearer token
- helper response JSON kustom sudah dihapus
- admin users sudah pindah ke Blade + form submit biasa
- summary sudah submit ke route web biasa
- route Sanctum tambahan untuk flow lama sudah dimatikan

Implikasinya:

- browser tidak perlu menyimpan token auth mentah
- backend tetap jadi sumber kebenaran untuk auth dan role
- proteksi admin tetap dicek di server

## Risiko Yang Harus Tetap Dijaga

### 1. Jangan percaya state auth di frontend

Yang tidak boleh dijadikan sumber kebenaran:

- `auth_id`
- `auth_email`
- `auth_username`
- `auth_is_admin`
- token login apa pun di `localStorage`

Yang benar:

- status login ditentukan oleh session backend
- akses admin ditentukan oleh middleware `admin.validate`
- halaman private dijaga middleware `auth`

### 2. Jangan hidupkan lagi pola route API tanpa kebutuhan jelas

Kalau nanti ada request fitur baru, default yang dipakai harus:

- route di `web.php`
- form Blade atau redirect flow
- CSRF standar Laravel

Jangan langsung kembali ke:

- `/api/...`
- helper JSON kustom
- token auth di frontend

### 3. Runtime server harus benar

Verifikasi manual terakhir menunjukkan aplikasi bisa sehat, tetapi environment server tetap bisa bikin aplikasi terlihat rusak kalau:

- docroot tidak diarahkan ke `public/`
- versi PHP web server berbeda dari PHP CLI

Ini penting karena masalah seperti itu terlihat seperti bug aplikasi padahal akar masalahnya di server.

## Flow Aman Yang Dipertahankan

1. User login ke route web biasa
2. Backend validasi kredensial
3. Backend menyimpan auth di session
4. Browser membawa cookie session secara otomatis
5. Route private dicek dengan middleware `auth`
6. Route admin dicek dengan `admin.validate`
7. Logout dilakukan lewat backend

## Checklist Yang Sudah Selesai

### Backend

- [x] Hapus `routes/api.php`
- [x] Pusatkan route aplikasi ke `routes/web.php`
- [x] Hapus helper response JSON kustom
- [x] Gunakan redirect, flash session, dan Blade
- [x] Matikan flow Sanctum yang dipakai untuk pola lama
- [x] Pastikan admin route tetap dijaga middleware

### Frontend

- [x] Admin users tidak lagi bergantung pada endpoint API terpisah
- [x] Summary tidak lagi bergantung pada AJAX API lama
- [x] Navigasi utama tidak lagi mengarah ke file `.html` lama yang sudah rusak
- [x] Browser smoke test dasar sudah dilakukan untuk halaman utama

## Checklist Yang Masih Perlu Dilanjutkan

### Backend

- [ ] Tambah feature test untuk auth, admin users, summary, user profile, dan pomodoro
- [ ] Evaluasi apakah `laravel/sanctum` masih perlu dihapus dari dependency
- [x] Hapus route test `_tmp` yang tidak dipakai
- [ ] Pasang route final untuk user profile dan pomodoro setelah kontrak frontend jelas

### Frontend

- [ ] Hindari menyimpan data auth sensitif di storage browser
- [ ] Rapikan warning minor browser seperti `autocomplete` form
- [ ] Lakukan smoke test lagi setiap ada perubahan route atau JavaScript halaman
