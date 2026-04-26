# Neuroom - README DevOps

Panduan ini fokus ke setup dan operasional Neuroom di environment lokal, staging, dan production.

Dokumentasi teknis:

- Endpoint: `docs/ENDPOINT.md`
- Backend: `docs/PANDUAN_BACKEND.md`
- Frontend: `docs/PANDUAN_FRONTEND_VALIDATION.md`
- Standar JSON API: `docs/API_RESPONSE_JSON.md`
- Catatan keamanan operasional: `docs/TUGAS_BIAR_AMAN.md`

## Ringkasan Arsitektur

- Backend: Laravel 12
- Frontend: Blade + JavaScript fetch helper
- Database utama: MySQL / MariaDB
- Session dan cache: Redis
- API dinamis: `/api/v1/...`
- Autentikasi: session guard `web`
- Queue worker: Laravel queue
- Web server: Nginx + `php-fpm` direkomendasikan

Catatan produk:

- landing page memuat login dan register
- halaman utama tetap dirender Blade
- data interaktif frontend mengambil JSON dari `/api/v1`

## Dependency Sistem

### Wajib

- `php` 8.2+ beserta extension:
  - `bcmath`
  - `ctype`
  - `fileinfo`
  - `json`
  - `mbstring`
  - `openssl`
  - `pdo`
  - `pdo_mysql`
  - `tokenizer`
  - `xml`
- `composer`
- `mysql` atau MariaDB kompatibel
- `redis` server
- `nodejs` 18+ dan `npm`
- `git`

Untuk menjalankan test bawaan yang memakai SQLite in-memory, extension `pdo_sqlite` juga dibutuhkan.

## Setup Pertama Kali

1. Install dependency backend:

```bash
composer install
```

2. Siapkan environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Atur DB di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neuroom
DB_USERNAME=neuroom_user
DB_PASSWORD=your_password
```

4. Jika ingin memakai fitur summary AI, isi:

```env
GEMINI_API_KEY=your_key
```

5. Session dan cache sekarang default ke Redis. Pastikan Redis aktif, lalu set env berikut:

```env
SESSION_DRIVER=redis
SESSION_CONNECTION=default
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

6. Jalankan migration:

```bash
php artisan migrate
```

7. Install dependency frontend:

```bash
npm install
```

8. Jalankan build atau dev server asset:

```bash
npm run build
# atau
npm run dev
```

## Menjalankan Aplikasi

### Mode cepat

```bash
composer run dev
```

### Mode manual

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Terminal 3 bila queue dipakai:

```bash
php artisan queue:listen --tries=1
```

## Debugging dan Verifikasi

Cek route:

```bash
php artisan route:list
php artisan route:list --path=api/v1
```

Jalankan syntax check cepat:

```bash
php -l routes/api.php
php -l bootstrap/app.php
```

Jalankan test:

```bash
php artisan test
```

Catatan:

- test API saat ini butuh driver SQLite untuk mode in-memory, kecuali konfigurasi test DB diubah

## Deploy Production

1. Pull source code terbaru.
2. Install dependency backend tanpa package dev:

```bash
composer install --no-dev --optimize-autoloader
```

3. Siapkan `.env` production.
4. Jalankan migration:

```bash
php artisan migrate --force
```

5. Build asset:

```bash
npm ci
npm run build
```

6. Optimasi cache Laravel:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

7. Restart service terkait.

## Catatan Operasional

- arahkan docroot ke folder `public/`
- jangan commit `.env`
- samakan versi PHP CLI dan web server
- monitor `storage/logs/laravel.log`
- kalau route atau kontrak API berubah, sinkronkan frontend dengan `/api/v1`
