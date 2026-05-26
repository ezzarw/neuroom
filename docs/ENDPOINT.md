# Dokumentasi Endpoint Neuroom

Dokumen ini merangkum route aktif Neuroom per April 2026. Kondisi terbaru aplikasi memakai dua jalur:

- `routes/web.php` untuk halaman Blade
- `routes/api.php` untuk JSON API di prefix `/api/v1`

## Ringkasan

- Autentikasi tetap memakai session guard `web`
- Frontend AJAX/fetch memakai cookie session browser, bukan bearer token
- Semua response API JSON sekarang mengikuti kontrak standar:
  - `success`
  - `reason`
  - `data`
  - `errors`
- Route API aktif ada di `/api/v1/...`

## Route Web

### Publik

#### `GET /`

- Menampilkan landing page

### Private `auth`

#### `GET /me`

- redirect ke `GET /profile`

#### `GET /fokus`

- redirect ke `GET /pomodoro`

#### `GET /catatan`

- menampilkan halaman `catatan`

#### `GET /belajar`

- menampilkan halaman belajar

#### `GET /pomodoro`

- menampilkan halaman pomodoro

#### `GET /utama`

- menampilkan halaman utama

#### `GET /profile`

- menampilkan halaman profil

### Private Admin `auth + admin.validate`

#### `GET /admin`

- menampilkan dashboard admin

#### `GET /admin/users`

- menampilkan halaman users admin

#### `GET /admin/pomodoro`

- menampilkan halaman pomodoro admin

#### `GET /dashboard`

- redirect ke `GET /admin`

## Route API `/api/v1`

### Auth

#### `POST /api/v1/auth/register`

Middleware:
- `throttle:5,1`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `username` | string | Ya | `required|string|max:100` |
| `email` | string | Ya | `required|string|email|max:100|unique:auths,email` |
| `password` | string | Ya | `required|string|min:8` plus rules `Password::min(8)->letters()->mixedCase()->numbers()` |

Success:
- status `201`
- login otomatis
- tidak mengirim redirect; frontend menentukan navigasi sendiri

#### `POST /api/v1/auth/login`

Middleware:
- `throttle:5,1`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `email` | string | Ya | `required|string|email|max:100` |
| `password` | string | Ya | `required|string` |

Success:
- status `200`
- tidak mengirim redirect; frontend menentukan navigasi sendiri

#### `POST /api/v1/auth/logout`

Middleware:
- `auth:sanctum`

Success:
- status `200`
- logout session
- invalidate session
- regenerate CSRF token
- tidak mengirim redirect; frontend menentukan navigasi sendiri

#### `GET /api/v1/me`

Middleware:
- `auth:sanctum`

Success:
- status `200`
- data profil user aktif

### Profile

#### `PATCH /api/v1/me`

Middleware:
- `auth:sanctum`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `display_name` | string | Tidak | `nullable|string|max:100` |
| `email` | string | Tidak | `nullable|string|email|max:100` |
| `profile_picture` | file image | Tidak | `nullable|image|mimes:jpeg,png,jpg|max:10000` |

Success:
- status `200`
- data user terbaru langsung di `data`

### Summary

#### `POST /api/v1/summary`

Middleware:
- `auth:sanctum`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `document` | file | Ya | `required|file|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,txt,csv,rtf,odt,ods,odp|max:20480` |
| `bahasa` | string | Ya | `required|string|in:indonesia,english` |

Success:
- status `200`
- hasil ringkasan langsung di `data`

Fallback:
- kalau `GEMINI_API_KEY` kosong, API tetap `200`
- `data.status = fallback`
- `data.fallback = true`

### Pomodoro

#### `GET /api/v1/pomodoro/history`

Middleware:
- `auth:sanctum`

Success:
- status `200`
- 20 sesi terakhir user aktif langsung di `data`

#### `POST /api/v1/pomodoro/history`

Middleware:
- `auth:sanctum`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `duration_seconds` | integer | Ya | `required|integer|min:1` |

Success:
- status `201`
- sesi baru di `data.session`

### Admin

Semua route admin API memakai middleware:
- `auth:sanctum`
- `admin.validate`

#### `GET /api/v1/admin/dashboard`

Success:
- status `200`
- statistik di `data.stats`
- sesi terbaru di `data.latest_sessions`

#### `GET /api/v1/admin/pomodoro`

Success:
- status `200`
- daftar sesi pomodoro langsung di `data`

#### `GET /api/v1/admin/users`

Success:
- status `200`
- daftar user langsung di `data`

#### `POST /api/v1/admin/users`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `username` | string | Ya | `required|string|max:100` |
| `email` | string | Ya | `required|string|email|max:100|unique:auths,email` |
| `password` | string | Ya | `required|string|min:8` |

Success:
- status `201`
- user baru langsung di `data`

#### `PUT /api/v1/admin/users/{user}`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `display_name` atau `displayName` | string | Ya | `required|string|max:100` |
| `email` | string | Ya | `required|string|email|max:100|unique:auths,email,{id}` |
| `is_admin` atau `isAdmin` | integer | Ya | `required|integer|in:0,1` |
| `password` | string | Tidak | `nullable|string|min:8` |

Success:
- status `200`
- user terbaru langsung di `data`

#### `DELETE /api/v1/admin/users/{user}`

Success:
- status `200`
- tidak ada payload bisnis, hanya reason sukses

## Pola Response API

Semua endpoint JSON mengikuti bentuk ini:

### Success

```json
{
  "success": true,
  "reason": "Login berhasil.",
  "data": {}
}
```

### Error

```json
{
  "success": false,
  "reason": "Validasi gagal.",
  "errors": {
    "email": ["Email sudah digunakan."]
  }
}
```

## Error Umum

- `401`: belum login
- `403`: bukan admin
- `409`: session aktif sudah ada
- `422`: validasi gagal atau kredensial salah
- `429`: throttle auth
- `500`: proses internal gagal
- `502`: layanan AI gagal atau respons AI tidak valid
