# AGENTS.md (Pedoman AI Assistant)

Pedoman ini **wajib dibaca** oleh setiap agen AI (termasuk Gemini atau sub-agent lainnya) yang akan membantu dalam pengembangan proyek **Neuroom**. Instruksi ini berfungsi sebagai *source of truth* agar AI memahami arsitektur, konvensi, dan pola komunikasi di repositori ini.

---

## 🤖 1. Komunikasi Awal
- Di awal setiap percakapan, pastikan Anda menanyakan "Apakah fokus saat ini di **Frontend** atau **Backend**?" untuk menentukan cakupan tugas Anda.
- Selalu tinjau `README.md` dan file di dalam folder `docs/` sebelum mengubah arsitektur inti.

## 🏗️ 2. Standar Arsitektur (Backend)
Neuroom menggunakan arsitektur **Laravel Layered (Service Pattern)**:
- **Controllers**: HARUS tetap ramping ("Thin Controllers"). Controller HANYA boleh bertanggung jawab untuk:
  - Menerima dan memvalidasi HTTP Request.
  - Memanggil metode yang relevan dari *Service Layer*.
  - Mengembalikan HTTP Response (menggunakan method `apiSuccess` atau `apiError` bawaan `Controller.php`).
- **Services**: Logika bisnis yang kompleks (seperti kalkulasi Pomodoro, pengelolaan file User, Auth, integrasi Gemini AI) **wajib** diletakkan di dalam `app/Services/`.
- **Activity Logging**: Segala aktivitas pengguna yang penting (Login, CRUD Catatan, mulai Pomodoro, dll) harus dicatat memanggil metode `ActivityLogger::log()`.
- **In-Memory State**: Data yang cepat berubah seperti *timer* Pomodoro yang sedang berjalan wajib disimpan di Redis, BUKAN di database MySQL. Gunakan MySQL hanya untuk riwayat yang sudah selesai (`pomodoro_histories`).

## 🎨 3. Standar UI/Frontend
Neuroom tidak menggunakan framework SPA seperti React atau Vue, melainkan pendekatan *hybrid*:
- **Blade Templating**: Digunakan untuk *routing* halaman dan merender struktur skeleton HTML awal.
- **Vanilla JavaScript**: Digunakan untuk manipulasi DOM dan logika di sisi klien (`public/js/`).
- **Stateful API & Fetch**: Komunikasi dengan backend menggunakan `window.NeuroomApi.request()` yang otomatis menyertakan perlindungan CSRF dan cookie Sanctum. JANGAN menggunakan header `Bearer Token`.
- **Styling**: Tampilan, terutama *Admin Dashboard* (`/admin`), dipertahankan agar terang, bersih, modern, dan sejalan dengan `admin.css`. JANGAN menambahkan tema radikal (seperti mode *hacker* gelap/neon) tanpa izin eksplisit.

## 💾 4. Konvensi Code & Penulisan
- Gunakan fitur dan penulisan PHP 8.2+ modern (seperti *named arguments*, *constructor property promotion*).
- Hindari *absolute namespace* (misal: `\App\Services\ActivityLogger::log()`). Selalu deklarasikan dependensi dengan keyword `use` di bagian atas file (`use App\Services\ActivityLogger;`).
- Setiap kali menambahkan fitur baru yang melibatkan database, buatkan *migration*-nya dan jalankan `php artisan test` untuk memastikan tidak ada fitur lama yang rusak.

## 📝 5. Pedoman Tambahan
Jika Anda bingung tentang respon standar API atau aturan keamanan operasional, silakan rujuk dokumen berikut:
- Struktur Respon API: `docs/API_RESPONSE_JSON.md`
- Panduan Backend: `docs/PANDUAN_BACKEND.md`
- Panduan Frontend: `docs/PANDUAN_FRONTEND_VALIDATION.md`
- Keamanan: `docs/TUGAS_BIAR_AMAN.md`
