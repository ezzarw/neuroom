# Panduan Backend Neuroom

Dokumen ini menjelaskan kondisi backend Neuroom yang berlaku sekarang.

## Kondisi Saat Ini

Backend sekarang memakai pola campuran yang jelas:

- halaman aplikasi tetap lewat `routes/web.php` dan Blade
- data dinamis untuk frontend memakai JSON API di `routes/api.php` dengan prefix `/api/v1`
- autentikasi tetap session-based dengan guard `web`
- session dan cache default sekarang disimpan di Redis
- request API tidak memakai bearer token
- response JSON sudah distandardkan

## Yang Baru Diperbarui

Perubahan terbaru yang sudah masuk ke code:

1. Route API `/api/v1` dihidupkan kembali dan sekarang aktif untuk auth, profile, summary, pomodoro, dan admin.
2. Semua response JSON backend diseragamkan ke format:
   - `success`
   - `reason`
   - `data`
   - `errors`
 3. Exception JSON untuk validasi dan auth dirender konsisten dari `bootstrap/app.php`.
4. Frontend fetch diarahkan ke `/api/v1/...` dan membaca payload baru dari `data`.
5. Dokumentasi lama yang menyebut “web-only tanpa API” sudah tidak lagi akurat.

## Struktur Route

### Web

Dipakai untuk render halaman:

- `/`
- `/belajar`
- `/pomodoro`
- `/utama`
- `/profile`
- `/admin`
- `/admin/users`
- `/admin/pomodoro`

### API

Dipakai untuk data dan aksi via fetch:

- `/api/v1/auth/register`
- `/api/v1/auth/login`
- `/api/v1/auth/logout`
- `/api/v1/me`
- `PATCH /api/v1/me`
- `/api/v1/summary`
- `/api/v1/pomodoro/history`
- `/api/v1/admin/dashboard`
- `/api/v1/admin/pomodoro`
- `/api/v1/admin/users`

## Standar Response JSON

### Success

```json
{
  "success": true,
  "reason": "OK",
  "data": {}
}
```

### Error

```json
{
  "success": false,
  "reason": "Terjadi error.",
  "errors": {}
}
```

Catatan:

- `data` adalah payload bisnis utama
- validasi field masuk ke `errors`

## Middleware dan Auth

- API tetap mengandalkan session browser
- `statefulApi()` aktif di bootstrap
- `admin.validate` mengembalikan JSON `401/403` kalau request mengharapkan JSON
- logout tetap invalidate session dan regenerate CSRF token

## Pedoman Kalau Menambah Fitur Backend

1. Tentukan dulu fiturnya termasuk web page atau JSON API.
2. Kalau render halaman, tambahkan route di `web.php`.
3. Kalau dipakai fetch frontend, tambahkan route di `api.php` dengan prefix `/api/v1`.
4. Untuk endpoint JSON, selalu pakai helper response standar dari base `Controller`.
5. Jangan bikin bentuk payload bebas per-controller.
6. Jangan tambahkan auth token frontend kalau session masih cukup.

## Catatan Implementasi Penting

- Model auth utama sekarang `App\Models\Auth`
- tabel auth utama sekarang `auths`
- relasi user profile memakai `users.auth_id`
- password sekarang di-hash dengan `Hash::make`, bukan binary Go lama
- username unik dibuat dari helper PHP `generateUniqueUsername()`
- summary memakai Gemini API langsung lewat `GEMINI_API_KEY`

## Operasional

### Environment penting

- `DB_*`
- `SESSION_DRIVER=redis`
- `CACHE_STORE=redis`
- `REDIS_*`
- `APP_URL`
- `GEMINI_API_KEY`

### Cek route

```bash
php artisan route:list
php artisan route:list --path=api/v1
```

### Cache refresh

```bash
php artisan optimize:clear
php artisan route:cache
php artisan view:cache
```

## Verifikasi Terakhir

Yang sudah diverifikasi:

- route `/api/v1` terdaftar lengkap
- syntax check PHP untuk controller, bootstrap, dan route file lolos

Yang belum bisa diverifikasi penuh di environment ini:

- feature test API gagal dijalankan karena driver `pdo_sqlite` belum tersedia

## Risiko Yang Masih Perlu Dijaga

- dokumentasi lain di luar folder docs bisa saja masih menyebut flow lama
- test otomatis belum hijau karena environment test DB belum siap
- frontend dan backend harus tetap disiplin memakai `/api/v1`, jangan kembali ke `/api/...` lama
