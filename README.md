# Neuroom - README DevOps

Panduan ini fokus ke kebutuhan DevOps untuk menjalankan Neuroom di environment lokal, staging, dan production.

Dokumentasi teknis:
- API: `docs/API.md`
- Panduan frontend agar lolos validasi backend: `docs/PANDUAN_FRONTEND_VALIDATION.md`

## 1. Ringkasan Arsitektur

- Backend: Laravel 12 (PHP 8.2+)
- Frontend: Blade + Vite + TailwindCSS
- Database utama: MySQL / MariaDB
- Queue worker: Laravel queue (`php artisan queue:listen`)
- Process manager production: `systemd` (direkomendasikan)
- Web server: Nginx + `php-fpm` (direkomendasikan), Apache juga bisa

Catatan produk:
- Login dan register ada di satu halaman frontend, digabung dengan landing page.

## 2. Dependency Sistem

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
- `mysql` (atau MariaDB kompatibel)
- `nodejs` 18+ dan `npm`
- `git`

### Untuk server production (direkomendasikan)

- `nginx`
- `php-fpm` (versi PHP yang sama dengan CLI, minimal 8.2)
- `supervisor` atau `systemd` untuk jaga queue worker tetap hidup

## 3. Struktur Environment

File penting:
- `.env`
- `storage/`
- `bootstrap/cache/`

Pastikan permission write untuk user service web (`www-data` atau setara) pada:
- `storage`
- `bootstrap/cache`

## 4. Setup Pertama Kali

1. Clone dan install dependency backend:

```bash
composer install
```

2. Siapkan environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Atur koneksi DB di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neuroom
DB_USERNAME=neuroom_user
DB_PASSWORD=your_password
```

4. Migrasi database:

```bash
php artisan migrate
```

5. Install dependency frontend:

```bash
npm install
```

6. Build asset (untuk production) atau jalankan dev asset server:

```bash
npm run build
# atau
npm run dev
```

## 5. Menjalankan Aplikasi

### Mode development cepat

Jalankan semua proses dev sekaligus (server + queue + log + vite):

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
php artisan queue:listen --tries=1
```

Terminal 3:

```bash
npm run dev
```

## 6. Checklist Deploy Production

1. Pull source code terbaru.
2. Install dependency tanpa dev package:

```bash
composer install --no-dev --optimize-autoloader
```

3. Siapkan `.env` production (APP_ENV, APP_DEBUG=false, DB, CACHE, QUEUE, MAIL).
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

7. Restart service terkait (`php-fpm`, queue worker).

## 7. Service yang Perlu Dimonitor

- `nginx` / `apache2`
- `php-fpm`
- `mysql`
- queue worker (systemd/supervisor)

Contoh health check dasar:
- HTTP app return `200`
- koneksi DB sukses
- queue worker aktif
- tidak ada error fatal di log Laravel

## 8. Logging dan Debugging

- Log aplikasi: `storage/logs/laravel.log`
- Cek route:

```bash
php artisan route:list
```

- Jalankan test:

```bash
php artisan test
```

## 9. Catatan Operasional

- Jika deploy pakai Nginx, arahkan `root` ke folder `public/`.
- Jangan commit `.env` ke repository.
- Sinkronkan versi PHP CLI dan `php-fpm` agar behavior konsisten.
- Untuk scale worker, gunakan beberapa instance queue worker via `systemd` atau `supervisor`.
