# Dokumentasi API Neuroom

Dokumen ini menjelaskan API Neuroom dengan bahasa yang sengaja dibuat mudah dibaca. Targetnya: developer frontend bisa langsung pakai endpoint, developer backend bisa menjaga kontrak response, dan reviewer bisa cepat melihat aturan autentikasi, validasi, serta bentuk data.

Base URL lokal yang umum dipakai:

```text
http://127.0.0.1:8000/api/v1
```

Kalau aplikasi jalan lewat XAMPP atau domain lokal lain, bagian host bisa berubah. Prefix API tetap:

```text
/api/v1
```

## Daftar Isi

- [Istilah Teknis](#istilah-teknis)
- [Aturan Umum API](#aturan-umum-api)
- [Autentikasi Stateful Sanctum](#autentikasi-stateful-sanctum)
- [Format Response JSON](#format-response-json)
- [Kode Status HTTP](#kode-status-http)
- [Endpoint Auth](#endpoint-auth)
- [Endpoint Profile](#endpoint-profile)
- [Endpoint Summary AI](#endpoint-summary-ai)
- [Endpoint Notes](#endpoint-notes)
- [Endpoint Pomodoro](#endpoint-pomodoro)
- [Endpoint Admin](#endpoint-admin)
- [Contoh Axios Frontend](#contoh-axios-frontend)
- [Catatan Implementasi Saat Ini](#catatan-implementasi-saat-ini)

---

## Istilah Teknis

Bagian ini penting supaya istilah yang sering muncul di dokumentasi tidak terasa abstrak.

| Istilah | Maksud sederhananya |
| --- | --- |
| API | Jalur komunikasi antara frontend dan backend. Frontend mengirim request, backend membalas data JSON. |
| Endpoint | Alamat spesifik API, misalnya `POST /api/v1/auth/login`. |
| Method | Jenis aksi HTTP. `GET` mengambil data, `POST` membuat data/menjalankan aksi, `PATCH` mengubah sebagian data, `PUT` mengubah data, `DELETE` menghapus data. |
| Request | Data yang dikirim client/frontend ke backend. Bisa berupa JSON, form, query string, atau file upload. |
| Response | Balasan dari backend. Di project ini response API berbentuk JSON. |
| JSON | Format data berbasis key-value. Mudah dibaca JavaScript dan backend. |
| Stateful | Login disimpan lewat session cookie. Backend mengenali user dari cookie session, bukan dari bearer token di header. |
| Cookie | Data kecil yang disimpan browser dan otomatis ikut dikirim ke server pada request berikutnya. |
| Session | Data login di sisi server. Cookie browser hanya membawa penanda session. |
| CSRF | Proteksi agar request penting tidak bisa dipalsukan dari website lain. Untuk request seperti `POST`, `PATCH`, `PUT`, `DELETE`, frontend perlu mengirim token CSRF. |
| Sanctum | Package Laravel untuk autentikasi API. Di project ini dipakai sebagai stateful auth untuk frontend first-party. |
| Middleware | Lapisan pengecekan sebelum request sampai ke controller. Contoh: cek login, cek admin, throttle. |
| Throttle | Pembatas jumlah request dalam waktu tertentu. Dipakai di login/register supaya tidak gampang di-bruteforce. |
| Multipart Form | Format request untuk upload file. Dipakai saat mengirim dokumen summary atau foto profil. |
| Query Parameter | Parameter di URL setelah tanda `?`, misalnya `/notes?s=matematika&all=1`. |
| Pagination | Data dibagi per halaman supaya response tidak terlalu besar. |
| Validation Error | Error karena input tidak memenuhi aturan, misalnya email tidak valid atau password terlalu pendek. |

---

## Aturan Umum API

### Header yang disarankan

Untuk request JSON:

```http
Accept: application/json
Content-Type: application/json
X-Requested-With: XMLHttpRequest
```

Untuk request upload file, jangan set `Content-Type` manual. Biarkan browser/Axios membuat `multipart/form-data` beserta boundary-nya.

### Format waktu

Waktu dari API memakai format:

```text
YYYY-MM-DD HH:mm:ss
```

Contoh:

```text
2026-05-27 14:30:00
```

### Format durasi Pomodoro

Durasi manusiawi memakai format:

```text
HH:MM:SS
```

Contoh:

```text
00:25:00
```

---

## Autentikasi Stateful Sanctum

API Neuroom memakai Laravel Sanctum secara stateful.

Artinya:

- Login disimpan lewat cookie session.
- Frontend harus mengirim cookie di setiap request API.
- Request yang mengubah data harus membawa CSRF token.
- Route backend tetap memakai middleware `auth:sanctum`.
- Ini bukan bearer token stateless.

### Setup frontend yang dibutuhkan

Di frontend, Axios perlu mengirim cookie:

```js
axios.defaults.withCredentials = true;
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

Project ini sudah menyiapkan hal itu di:

```text
resources/js/bootstrap.js
```

CSRF token diambil dari meta tag:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

Lalu dikirim sebagai header:

```http
X-CSRF-TOKEN: <token>
```

### Request yang butuh login

Semua endpoint berikut butuh user sudah login:

- `POST /auth/logout`
- `GET /auth/me`
- `PATCH /auth/me`
- `POST /summary`
- `POST /summary-to-notes`
- semua endpoint `/notes`
- semua endpoint `/pomodoro`
- semua endpoint `/admin`

### Request yang tidak butuh login

Endpoint berikut bisa dipanggil tanpa login:

- `POST /auth/login`
- `POST /auth/register`

Namun kalau user sudah login lalu memanggil login/register lagi, API akan membalas `409 Conflict`.

---

## Format Response JSON

### Response sukses

Semua response sukses memakai pola:

```json
{
  "success": true,
  "reason": "Pesan singkat untuk manusia.",
  "data": {}
}
```

Arti field:

| Field | Tipe | Arti |
| --- | --- | --- |
| `success` | boolean | `true` kalau request berhasil. |
| `reason` | string | Pesan ringkas yang bisa ditampilkan ke user atau dipakai debugging. |
| `data` | object/array | Isi data utama. Bentuknya tergantung endpoint. |

### Response error

Semua response error memakai pola:

```json
{
  "success": false,
  "reason": "Pesan error.",
  "errors": {}
}
```

Arti field:

| Field | Tipe | Arti |
| --- | --- | --- |
| `success` | boolean | Selalu `false` untuk error. |
| `reason` | string | Pesan error utama. |
| `errors` | object | Detail error per field, biasanya muncul saat validasi gagal. |

Contoh validation error:

```json
{
  "success": false,
  "reason": "Validasi gagal.",
  "errors": {
    "email": [
      "The email field must be a valid email address."
    ]
  }
}
```

---

## Kode Status HTTP

| Status | Nama | Kapan biasanya muncul |
| --- | --- | --- |
| `200` | OK | Request berhasil. |
| `201` | Created | Data baru berhasil dibuat. |
| `401` | Unauthorized | User belum login atau session tidak valid. |
| `403` | Forbidden | User login, tapi tidak punya akses. Contoh: bukan admin. |
| `404` | Not Found | Data atau endpoint tidak ditemukan. |
| `409` | Conflict | Kondisi request konflik. Contoh: login lagi saat session masih aktif. |
| `419` | CSRF Token Mismatch | CSRF token tidak ada/tidak cocok pada request stateful. |
| `422` | Unprocessable Entity | Input tidak valid. |
| `429` | Too Many Requests | Request terlalu sering, terkena throttle. |
| `500` | Internal Server Error | Error tak terduga di server. |
| `502` | Bad Gateway | Backend gagal menerima response valid dari layanan eksternal, misalnya Gemini. |

---

## Endpoint Auth

Base path:

```text
/api/v1/auth
```

### Register

Membuat akun baru dan langsung login.

```http
POST /api/v1/auth/register
```

Middleware:

- `throttle:5,1`

Artinya maksimal 5 request per 1 menit untuk mengurangi percobaan spam.

Body JSON:

```json
{
  "username": "Budi Santoso",
  "email": "budi@example.com",
  "password": "Password123"
}
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `username` | Ya | string, maksimal 100 karakter |
| `email` | Ya | string, format email, maksimal 100 karakter, unik di tabel users |
| `password` | Ya | string, minimal 8 karakter, harus ada huruf, huruf besar/kecil, dan angka |

Catatan username:

- `username` akan dibuat unik otomatis.
- Jika input `Budi Santoso`, username bisa menjadi `budisantoso`.
- Jika sudah ada, sistem bisa membuat `budisantoso2`, dan seterusnya.
- `display_name` disimpan dari input awal `username`.

Response sukses `201`:

```json
{
  "success": true,
  "reason": "Register berhasil. Selamat datang.",
  "data": {
    "id": 1,
    "username": "budisantoso",
    "email": "budi@example.com",
    "is_admin": 0,
    "display_name": "Budi Santoso",
    "profile_picture": null,
    "profile_picture_url": null
  }
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `409` | Session aktif sudah ada. |
| `422` | Input tidak valid atau email sudah dipakai. |
| `429` | Terlalu banyak request register. |

---

### Login

Masuk ke akun dan membuat session aktif.

```http
POST /api/v1/auth/login
```

Middleware:

- `throttle:5,1`

Body JSON:

```json
{
  "email": "budi@example.com",
  "password": "Password123"
}
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `email` | Ya | string, format email, maksimal 100 karakter |
| `password` | Ya | string |

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Login berhasil.",
  "data": {
    "id": 1,
    "username": "budisantoso",
    "email": "budi@example.com",
    "is_admin": 0,
    "display_name": "Budi Santoso",
    "profile_picture": null,
    "profile_picture_url": null
  }
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `409` | Session aktif sudah ada. |
| `422` | Email atau password salah. |
| `429` | Terlalu banyak request login. |

---

### Logout

Menghapus session login dan membersihkan cache summary user di Redis.

```http
POST /api/v1/auth/logout
```

Butuh login:

```text
auth:sanctum
```

Body:

Tidak perlu body.

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Logout berhasil.",
  "data": []
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `401` | Belum login atau session sudah habis. |
| `419` | CSRF token tidak valid. |

---

### Me

Mengambil profil user yang sedang login.

```http
GET /api/v1/auth/me
```

Butuh login:

```text
auth:sanctum
```

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Profil berhasil diambil.",
  "data": {
    "id": 1,
    "username": "budisantoso",
    "email": "budi@example.com",
    "is_admin": 0,
    "display_name": "Budi Santoso",
    "profile_picture": null,
    "profile_picture_url": null
  }
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `401` | Belum login atau session sudah habis. |

---

## Endpoint Profile

### Update Profil Saya

Mengubah profil user yang sedang login. Bisa mengubah nama tampilan, email, dan foto profil.

```http
PATCH /api/v1/auth/me
```

Butuh login:

```text
auth:sanctum
```

Format request:

- Gunakan JSON kalau hanya update `display_name` atau `email`.
- Gunakan `multipart/form-data` kalau upload `profile_picture`.

Body JSON:

```json
{
  "display_name": "Budi S.",
  "email": "budi.baru@example.com"
}
```

Body multipart:

```text
display_name: Budi S.
email: budi.baru@example.com
profile_picture: <file jpeg/png/jpg>
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `display_name` | Tidak | string, maksimal 100 karakter |
| `email` | Tidak | string, format email, maksimal 100 karakter, harus unik jika diganti |
| `profile_picture` | Tidak | image, ekstensi `jpeg`, `png`, atau `jpg`, maksimal 10000 KB |

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Profil berhasil diupdate.",
  "data": {
    "id": 1,
    "username": "budisantoso",
    "email": "budi.baru@example.com",
    "is_admin": 0,
    "display_name": "Budi S.",
    "profile_picture": "nama_file.jpg",
    "profile_picture_url": "http://127.0.0.1:8000/storage/profile_picture/nama_file.jpg",
    "created_at": "2026-05-27 10:00:00",
    "updated_at": "2026-05-27 10:15:00"
  }
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `401` | Belum login. |
| `422` | Input tidak valid atau email sudah dipakai. |
| `500` | Gagal menyimpan profil/foto. |

---

## Endpoint Summary AI

Base path:

```text
/api/v1
```

Summary AI memakai Gemini. File dikirim ke Gemini, lalu hasil ringkasan disimpan sementara di Redis dengan key per user.

### Buat Ringkasan

Mengunggah dokumen/gambar lalu membuat ringkasan.

```http
POST /api/v1/summary
```

Butuh login:

```text
auth:sanctum
```

Format request:

```text
multipart/form-data
```

Body:

```text
document: <file>
bahasa: indonesia
```

Atau:

```text
document: <file>
bahasa: english
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `document` | Ya | file dengan tipe `pdf`, `txt`, `png`, `jpg`, `jpeg`, `webp`, `heic`, atau `heif`; maksimal global 50 MB |
| `bahasa` | Ya | string, hanya `indonesia` atau `english` |

Aturan tambahan gambar:

- Jika file adalah gambar, maksimal 7 MB.
- Struktur gambar divalidasi agar file yang menyamar sebagai gambar bisa ditolak.
- `heic` dan `heif` dikecualikan dari pengecekan `getimagesize`.

Response sukses `201`:

```json
{
  "success": true,
  "reason": "Ringkasan berhasil dibuat.",
  "data": {
    "status": "success",
    "output": "- Poin pertama\n- Poin kedua",
    "document": {
      "name": "materi.pdf",
      "mime_type": "application/pdf"
    }
  }
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `401` | Belum login. |
| `419` | CSRF token tidak valid. |
| `422` | File/bahasa tidak valid. |
| `502` | Gemini gagal atau response Gemini tidak valid. |

---

### Simpan Ringkasan ke Catatan

Mengambil hasil ringkasan terakhir dari Redis, lalu membuat catatan baru.

```http
POST /api/v1/summary-to-notes
```

Butuh login:

```text
auth:sanctum
```

Body:

Tidak perlu body.

Data catatan yang dibuat:

```json
{
  "title": "Catatan dari Rangkuman",
  "content": "<hasil ringkasan terakhir>"
}
```

Response sukses yang diharapkan `201`:

```json
{
  "success": true,
  "reason": "Rangkuman berhasil diupload ke catatan.",
  "data": {
    "id": 10,
    "user_id": 1,
    "title": "Catatan dari Rangkuman",
    "content": "- Poin pertama\n- Poin kedua",
    "created_at": "2026-05-27 10:30:00",
    "updated_at": "2026-05-27 10:30:00"
  }
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `401` | Belum login. |
| `404` | Hasil ringkasan tidak ditemukan di Redis. Biasanya belum pernah membuat summary atau summary sudah pernah disimpan ke catatan. |
| `419` | CSRF token tidak valid. |

Catatan penting:

Saat dokumen ini ditulis, method `SummaryController::addToNotes()` masih memiliki `dd($note);` setelah catatan dibuat. `dd()` berarti "dump and die": aplikasi mencetak isi variabel lalu berhenti. Jadi endpoint ini berpotensi belum mengembalikan JSON sukses seperti kontrak di atas sampai `dd($note);` dihapus.

---

## Endpoint Notes

Base path:

```text
/api/v1/notes
```

Semua endpoint notes butuh login:

```text
auth:sanctum
```

Data catatan selalu dibatasi ke user yang sedang login. User tidak bisa mengambil, mengubah, atau menghapus catatan milik user lain lewat endpoint ini.

### Ambil Daftar Catatan

```http
GET /api/v1/notes
```

Query parameter:

| Parameter | Wajib | Contoh | Arti |
| --- | --- | --- | --- |
| `s` | Tidak | `?s=matematika` | Cari catatan berdasarkan title/content lewat Scout search. |
| `all` | Tidak | `?all=1` | Jika true, ambil semua catatan tanpa pagination. |

Response default `200` dengan pagination:

```json
{
  "success": true,
  "reason": "Berhasil menampilkan catatan.",
  "data": {
    "items": [
      {
        "id": 1,
        "user_id": 1,
        "title": "Rumus Matematika",
        "content": "Isi catatan...",
        "created_at": "2026-05-27 10:00:00",
        "updated_at": "2026-05-27 10:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 25,
      "last_page": 3
    }
  }
}
```

Response `200` jika `all=1`:

```json
{
  "success": true,
  "reason": "Berhasil menampilkan semua catatan.",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "title": "Rumus Matematika",
      "content": "Isi catatan...",
      "created_at": "2026-05-27 10:00:00",
      "updated_at": "2026-05-27 10:00:00"
    }
  ]
}
```

---

### Ambil Detail Catatan

```http
GET /api/v1/notes/{id}
```

Path parameter:

| Parameter | Arti |
| --- | --- |
| `id` | ID catatan |

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Berhasil menampilkan catatan.",
  "data": {
    "id": 1,
    "user_id": 1,
    "title": "Rumus Matematika",
    "content": "Isi catatan...",
    "created_at": "2026-05-27 10:00:00",
    "updated_at": "2026-05-27 10:00:00"
  }
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `404` | Catatan tidak ditemukan atau bukan milik user login. |

---

### Buat Catatan

```http
POST /api/v1/notes
```

Body JSON:

```json
{
  "title": "Rumus Matematika",
  "content": "Isi catatan..."
}
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `title` | Ya | string, maksimal 200 karakter |
| `content` | Tidak secara eksplisit | string |

Catatan:

- Database `notes.content` tidak nullable, jadi secara praktik frontend sebaiknya tetap mengirim `content`.

Response sukses `201`:

```json
{
  "success": true,
  "reason": "Catatan berhasil dibuat.",
  "data": {
    "id": 1,
    "user_id": 1,
    "title": "Rumus Matematika",
    "content": "Isi catatan...",
    "created_at": "2026-05-27 10:00:00",
    "updated_at": "2026-05-27 10:00:00"
  }
}
```

---

### Update Catatan

```http
PATCH /api/v1/notes/{id}
```

Body JSON:

```json
{
  "title": "Rumus Matematika Update",
  "content": "Isi catatan yang sudah diperbarui..."
}
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `title` | Tidak | string, maksimal 200 karakter |
| `content` | Tidak | string |

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Catatan berhasil diupdate.",
  "data": {
    "id": 1,
    "user_id": 1,
    "title": "Rumus Matematika Update",
    "content": "Isi catatan yang sudah diperbarui...",
    "created_at": "2026-05-27 10:00:00",
    "updated_at": "2026-05-27 10:20:00"
  }
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `404` | Catatan tidak ditemukan atau bukan milik user login. |
| `422` | Input tidak valid. |

---

### Hapus Catatan

```http
DELETE /api/v1/notes/{id}
```

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Catatan berhasil dihapus.",
  "data": []
}
```

Kemungkinan error:

| Status | Penyebab |
| --- | --- |
| `404` | Catatan tidak ditemukan atau bukan milik user login. |

---

## Endpoint Pomodoro

Base path:

```text
/api/v1/pomodoro
```

Semua endpoint pomodoro butuh login:

```text
auth:sanctum
```

### Simpan Riwayat Pomodoro

Menyimpan satu sesi Pomodoro yang sudah selesai.

```http
POST /api/v1/pomodoro/history
```

Body JSON:

```json
{
  "duration_seconds": 1500
}
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `duration_seconds` | Ya | integer, minimal 1 |

Catatan:

- `session` dihitung otomatis per hari.
- Kalau hari ini user belum punya sesi, sesi pertama bernilai `1`.
- Kalau sudah punya 2 sesi hari ini, sesi berikutnya bernilai `3`.

Response sukses `201`:

```json
{
  "success": true,
  "reason": "Data pomodoro berhasil ditambahkan.",
  "data": {
    "id": 1,
    "session": 1,
    "date": "2026-05-27",
    "duration_seconds": 1500,
    "duration": "00:25:00",
    "created_at": "2026-05-27 10:00:00"
  }
}
```

---

### Ambil Riwayat Pomodoro

Mengambil 20 riwayat Pomodoro terbaru milik user login.

```http
GET /api/v1/pomodoro/history
```

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Riwayat pomodoro berhasil diambil.",
  "data": [
    {
      "id": 1,
      "session": 1,
      "date": "2026-05-27",
      "duration_seconds": 1500,
      "duration": "00:25:00",
      "created_at": "2026-05-27 10:00:00"
    }
  ]
}
```

---

## Endpoint Admin

Base path:

```text
/api/v1/admin
```

Semua endpoint admin butuh:

```text
auth:sanctum
admin.validate
```

Artinya:

- User harus login.
- Field `is_admin` user harus bernilai `1`.

Jika user belum login, response `401`. Jika user login tapi bukan admin, response `403`.

### Dashboard Admin

```http
GET /api/v1/admin/dashboard
```

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Dashboard admin berhasil diambil.",
  "data": {
    "stats": {
      "total_users": 10,
      "total_sessions": 125,
      "active_today": 4
    },
    "latest_sessions": [
      {
        "id": 1,
        "username": "budisantoso",
        "session": 1,
        "date": "2026-05-27",
        "duration_seconds": 1500,
        "duration": "00:25:00",
        "created_at": "2026-05-27 10:00:00",
        "updated_at": "2026-05-27 10:00:00"
      }
    ]
  }
}
```

---

### Daftar User

```http
GET /api/v1/admin/users
```

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Daftar user berhasil diambil.",
  "data": [
    {
      "id": 1,
      "username": "budisantoso",
      "display_name": "Budi Santoso",
      "email": "budi@example.com",
      "is_admin": 0,
      "created_at": "2026-05-27 10:00:00",
      "updated_at": "2026-05-27 10:00:00"
    }
  ]
}
```

---

### Tambah User

```http
POST /api/v1/admin/users
```

Body JSON:

```json
{
  "username": "Member Baru",
  "email": "member@example.com",
  "password": "password123"
}
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `username` | Ya | string, maksimal 100 karakter |
| `email` | Ya | string, format email, maksimal 100 karakter, unik |
| `password` | Ya | string, minimal 8 karakter |

Catatan:

- User yang dibuat lewat endpoint ini otomatis `is_admin = 0`.
- `display_name` diambil dari input `username`.
- `username` tetap dibuat unik otomatis.

Response sukses `201`:

```json
{
  "success": true,
  "reason": "User berhasil ditambahkan.",
  "data": {
    "id": 2,
    "username": "memberbaru",
    "display_name": "Member Baru",
    "email": "member@example.com",
    "is_admin": 0,
    "created_at": "2026-05-27 10:00:00",
    "updated_at": "2026-05-27 10:00:00"
  }
}
```

---

### Update User

```http
PUT /api/v1/admin/users/{user}
```

Path parameter:

| Parameter | Arti |
| --- | --- |
| `user` | ID user |

Body JSON:

```json
{
  "display_name": "Member Update",
  "email": "member-update@example.com",
  "password": "passwordbaru123",
  "is_admin": 0
}
```

Validasi:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `display_name` | Ya | string, maksimal 100 karakter |
| `email` | Ya | string, format email, maksimal 100 karakter, unik selain user ini |
| `password` | Tidak | string, minimal 8 karakter jika dikirim |
| `is_admin` | Ya | integer, hanya `0` atau `1` |

Alias input yang diterima:

- `displayName` akan dipetakan ke `display_name`
- `isAdmin` akan dipetakan ke `is_admin`

Response sukses `200`:

```json
{
  "success": true,
  "reason": "User berhasil diupdate.",
  "data": {
    "id": 2,
    "username": "memberbaru",
    "display_name": "Member Update",
    "email": "member-update@example.com",
    "is_admin": 0,
    "created_at": "2026-05-27 10:00:00",
    "updated_at": "2026-05-27 10:20:00"
  }
}
```

---

### Hapus User

```http
DELETE /api/v1/admin/users/{user}
```

Response sukses `200`:

```json
{
  "success": true,
  "reason": "User berhasil dihapus.",
  "data": []
}
```

Catatan:

- Karena relasi pomodoro memakai `cascadeOnDelete`, riwayat Pomodoro user ikut terhapus.
- Catatan user juga memiliki foreign key ke users. Perilaku delete tergantung constraint database yang aktif.

---

### Data Pomodoro Admin

Mengambil 50 sesi Pomodoro terbaru dari semua user.

```http
GET /api/v1/admin/pomodoro
```

Response sukses `200`:

```json
{
  "success": true,
  "reason": "Data pomodoro admin berhasil diambil.",
  "data": [
    {
      "id": 1,
      "username": "budisantoso",
      "session": 1,
      "date": "2026-05-27",
      "duration_seconds": 1500,
      "duration": "00:25:00",
      "created_at": "2026-05-27 10:00:00",
      "updated_at": "2026-05-27 10:00:00"
    }
  ]
}
```

---

## Contoh Axios Frontend

### Login

```js
async function login(email, password) {
  const response = await axios.post('/api/v1/auth/login', {
    email,
    password,
  });

  return response.data;
}
```

### Ambil profil

```js
async function getMe() {
  const response = await axios.get('/api/v1/auth/me');
  return response.data.data;
}
```

### Upload summary

```js
async function createSummary(file, bahasa = 'indonesia') {
  const formData = new FormData();
  formData.append('document', file);
  formData.append('bahasa', bahasa);

  const response = await axios.post('/api/v1/summary', formData);
  return response.data.data;
}
```

### Buat catatan

```js
async function createNote(title, content) {
  const response = await axios.post('/api/v1/notes', {
    title,
    content,
  });

  return response.data.data;
}
```

### Menangani error

```js
try {
  await axios.post('/api/v1/auth/login', {
    email,
    password,
  });
} catch (error) {
  const payload = error.response?.data;

  console.log(payload?.reason);
  console.log(payload?.errors);
}
```

---

## Catatan Implementasi Saat Ini

Bagian ini bukan kontrak ideal, tapi catatan kondisi kode saat dokumentasi ini dibuat.

1. API sudah diarahkan ke Sanctum stateful.
   - Protected route memakai `auth:sanctum`.
   - Login/logout memakai session guard yang dibaca dari konfigurasi Sanctum.
   - Frontend harus membawa cookie dan CSRF token.

2. `POST /api/v1/summary-to-notes` perlu dicek sebelum dipakai production.
   - Di `SummaryController::addToNotes()` masih ada `dd($note);`.
   - Selama itu masih ada, endpoint bisa berhenti sebelum mengembalikan response JSON.

3. `docs/API.md` ini mengikuti response helper di `App\Http\Controllers\Controller`.
   - Sukses: `success`, `reason`, `data`.
   - Error: `success`, `reason`, `errors`.

4. Endpoint notes search memakai Laravel Scout.
   - Kalau driver Scout belum siap, pencarian `?s=` bisa bergantung pada konfigurasi search di environment.

5. Request upload file harus memakai `multipart/form-data`.
   - Jangan mengirim file sebagai JSON/base64 dari frontend, kecuali backend memang diubah untuk menerima format itu.
