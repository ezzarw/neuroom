# Neuroom

Neuroom adalah platform manajemen waktu belajar yang mengintegrasikan teknik Pomodoro, pencatatan (Notes), dan peringkasan materi berbasis Artificial Intelligence (Gemini AI). Dibangun dengan arsitektur modern menggunakan Laravel 12 (Stateful API), Redis untuk performa realtime, dan Reverb untuk WebSockets.

## 🌟 Fitur Utama

- **Pomodoro Timer**: Manajemen waktu fokus dengan notifikasi realtime (WebSockets).
- **AI Summarization**: Ringkas dokumen belajar secara otomatis menggunakan Google Gemini AI.
- **Smart Notes**: Catat hasil belajar dengan dukungan pencarian cepat (Laravel Scout).
- **Live Monitoring Dashboard**: Panel admin bergaya modern untuk melacak aktivitas pengguna secara realtime (`/admin`).
- **Stateful API**: Interaksi halus dan aman ala Single Page Application (SPA) tanpa JWT overhead, mengandalkan proteksi CSRF & Cookie bawaan Sanctum.

---

## 🏗️ Arsitektur Sistem

- **Backend Framework**: Laravel 12
- **Frontend**: Blade + Vanilla JS (Fetch API Helper)
- **Database Utama**: MySQL / MariaDB (Persistensi)
- **In-Memory Store**: Redis (Sesi, Cache, Pomodoro State Buffer)
- **WebSockets**: Laravel Reverb (Real-time Broadcast)
- **Autentikasi**: Laravel Sanctum (Stateful Cookie-based)

### 📂 Struktur Direktori Penting
- `app/Services/`: Berisi logika bisnis utama (`PomodoroService`, `AuthService`, `UserService`, `ActivityLogger`) yang dipisahkan dari Controller (Clean Code).
- `public/js/stateful-api.js`: Helper JavaScript utama yang menangani pengiriman request API yang aman berserta penyisipan CSRF Token.
- `docs/`: Dokumentasi internal mendetail terkait API.

---

## 🚀 Panduan Setup Lokal

### 1. Kebutuhan Sistem (Prerequisites)
Pastikan lingkungan pengembangan Anda telah memiliki:
- PHP 8.2+ (ext: bcmath, ctype, fileinfo, json, mbstring, openssl, pdo_mysql, tokenizer, xml)
- Composer
- Node.js 18+ & NPM
- MySQL / MariaDB Server
- Redis Server (Wajib berjalan di background)

### 2. Instalasi Proyek
Klon repositori dan install dependensi:
```bash
git clone <repo-url>
cd neuroom
composer install
npm install
```

### 3. Konfigurasi Lingkungan
Duplikat file konfigurasi dan atur kredensial Anda:
```bash
cp .env.example .env
php artisan key:generate
```
Edit file `.env` yang baru dibuat:
```env
# Konfigurasi Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neuroom
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi Redis (WAJIB)
SESSION_DRIVER=redis
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Integrasi AI (Wajib untuk fitur Summarize)
GEMINI_API_KEY=your_gemini_key
```

### 4. Migrasi & Seeding
Bangun skema database (termasuk tabel `activity_logs` untuk monitoring admin):
```bash
php artisan migrate
```
*(Opsional)* Jalankan seeder jika ada untuk data dummy.

### 5. Menjalankan Aplikasi
Buka beberapa terminal untuk menjalankan service yang berbeda:

**Terminal 1 (PHP Server):**
```bash
php artisan serve
```
**Terminal 2 (Vite Asset Bundler):**
```bash
npm run dev
```
**Terminal 3 (Opsional - Queue Worker):**
```bash
php artisan queue:listen
```

Aplikasi kini dapat diakses melalui `http://127.0.0.1:8000`.

---

## 🛡️ Standar Pengujian (Testing)
Proyek ini dilengkapi dengan Feature dan Unit Testing. Karena menggunakan SQLite in-memory untuk testing, pastikan ekstensi `pdo_sqlite` aktif di `php.ini`.

Jalankan test suite dengan:
```bash
php artisan test
```

---

## 🚢 Panduan Deployment (Production)

1. Pastikan server produksi memiliki PHP, Nginx, MySQL, dan Redis.
2. Ambil kode terbaru dan instal dependensi produksi:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```
3. Sesuaikan file `.env` untuk production (`APP_ENV=production`, `APP_DEBUG=false`).
4. Eksekusi migrasi secara aman:
   ```bash
   php artisan migrate --force
   ```
5. Optimasi cache Laravel untuk performa maksimal:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
6. Pastikan Redis berjalan dengan stabil dan arahkan Document Root web server ke folder `/public`.

---

## 👨‍💻 Admin Panel
Admin dapat memantau log aktivitas secara *real-time* dengan mengakses rute `/admin` setelah login dengan kredensial ber-hak akses `is_admin = 1`. Panel ini mengusung desain yang bersih, minimalis, dan informatif.