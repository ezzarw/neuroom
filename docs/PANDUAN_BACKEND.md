# Panduan Backend Neuroom

Dokumen ini mencatat kondisi backend Neuroom setelah flow aplikasi dikembalikan ke pola Laravel web biasa.

## Tujuan

Backend sekarang diarahkan ke pola bawaan Laravel:

- route aplikasi dipusatkan di `routes/web.php`
- autentikasi memakai session guard `web`
- form submit memakai redirect + flash session
- validasi memakai validator Laravel
- halaman dirender Blade
- tidak ada helper response JSON kustom

## Perubahan Yang Sudah Selesai

### 1. Route API dipensiunkan

Status:
- `routes/api.php` sudah dihapus
- bootstrap route API sudah tidak dipakai lagi
- route aktif aplikasi sekarang ada di `routes/web.php`

Implikasi:
- tidak ada lagi pemisahan web route vs API route untuk fitur utama
- flow auth, admin users, summary, dan pomodoro mengikuti session web biasa

### 2. Helper JSON kustom dihapus

Status:
- `app/Helpers/Helper.php` sudah dihapus
- autoload `files` untuk helper lama sudah dibersihkan
- controller dan middleware tidak lagi memakai helper JSON lama

Pola yang dipakai sekarang:
- `return view(...)`
- `return redirect(...)->with(...)`
- `return back()->withErrors(...)`
- `abort(...)`

### 3. Controller utama sudah mengikuti flow web

#### `AuthController`

Status:
- register login otomatis lalu redirect ke `utama`
- login redirect ke dashboard admin atau `utama`
- logout invalidate session lalu redirect ke `/`
- `/me` sekarang render view profil, bukan JSON

#### `AdminController`

Status:
- admin users full Blade + form
- create update delete user lewat route web biasa
- error create dan edit membuka modal yang sesuai lewat flash session

#### `SummaryController`

Status:
- upload dokumen validasi lewat Laravel
- hasil sukses disimpan ke session dan dirender di halaman test summary
- jika `AI_BACKEND_URL` kosong, controller tetap mengembalikan payload sukses untuk uji integrasi dasar

#### `UserController`

Status:
- logic edit profile masih disimpan di controller
- route publiknya belum dipasang lagi sambil menunggu kesepakatan frontend
- kalau nanti dipakai lagi, return saat ini sudah netral dengan pola `back()` + flash

#### `PomodoroController`

Status:
- logic post/get sesi pomodoro masih disimpan di controller
- route test-nya sudah dihapus
- method disimpan dulu untuk integrasi frontend berikutnya

### 4. Middleware admin disederhanakan

Status:
- middleware `admin.validate` menjaga akses admin
- user non-admin menerima `403`
- user yang belum login ditangani oleh middleware auth/web flow

## Pola Pengembangan Backend Yang Harus Dipertahankan

Kalau menambah fitur baru, ikuti pola ini:

1. Tambah route di `routes/web.php`
2. Lindungi dengan middleware `auth` atau `admin.validate` bila perlu
3. Render halaman lewat Blade atau redirect
4. Gunakan flash session untuk status sukses
5. Gunakan validasi Laravel untuk error input
6. Hindari menambah helper response kustom
7. Jangan tambahkan route API baru kecuali memang ada kebutuhan terpisah yang jelas

## Catatan Operasional

### Runtime PHP harus konsisten

Hasil verifikasi lokal menunjukkan:
- aplikasi berjalan baik lewat `php artisan serve`
- server web terpisah bisa gagal kalau versi PHP berbeda dengan CLI

Praktik yang wajib:
- samakan versi PHP CLI dan web server
- arahkan docroot web server ke folder `public/`
- setelah ubah route atau Blade, refresh cache dengan:

```bash
php artisan optimize:clear
php artisan route:cache
php artisan view:cache
```

### Binary Go masih dipakai

Backend saat ini masih bergantung pada binary lokal untuk:
- hashing password
- pembuatan suffix username unik

Implikasi:
- file binary harus ada dan executable di environment target
- error binary akan muncul sebagai flash error atau log aplikasi

## Verifikasi Manual Terakhir

Pengujian browser terakhir yang sudah lolos:

- login admin
- admin dashboard
- admin users
- admin pomodoro
- halaman `/me`
- halaman `/belajar`
- halaman `/pomodoro`
- halaman `/utama`
- submit `POST /summary`

Skenario yang sudah diuji:
- create user admin
- edit user admin
- delete user admin
- upload summary

## Risiko Yang Masih Tersisa

- belum ada feature test otomatis untuk flow web utama
- `laravel/sanctum` masih ada di dependency walau route Sanctum sudah dimatikan
- beberapa halaman frontend masih bergantung pada struktur statis lama, jadi setiap perubahan navigasi perlu smoke test browser

## Langkah Lanjut Yang Disarankan

1. Tambah feature test untuk auth, admin users, summary, dan pomodoro
2. Evaluasi apakah dependency `laravel/sanctum` masih perlu dipertahankan
3. Pasang route final untuk edit profile dan pomodoro setelah kontrak frontend jelas
4. Lanjutkan smoke test browser setiap ada perubahan navigasi atau asset JS
