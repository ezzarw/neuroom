# Dokumentasi Endpoint Neuroom

Dokumen ini merangkum route aktif aplikasi setelah semua alur dipusatkan ke `routes/web.php`.

## Ringkasan

- Tidak ada `routes/api.php`
- Semua endpoint aplikasi ada di `routes/web.php`
- Autentikasi memakai session guard `web`
- Tidak ada bearer token dan tidak ada helper response JSON kustom
- Halaman admin users, summary, profile, dan pomodoro berjalan dengan flow Laravel web biasa

## Route Publik

### `GET /`

- Menampilkan landing page

### `POST /auth/register`

Middleware:
- `guest`
- `throttle:5,1`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `username` | string | Ya | `required|string|max:100` |
| `email` | string | Ya | `required|string|email|max:100|unique:authentications,email` |
| `password` | string | Ya | `required|string|min:8` |

Hasil:
- sukses: login otomatis lalu redirect ke `GET /utama`
- gagal: redirect back dengan error validasi atau error login/register

### `POST /auth/login`

Middleware:
- `guest`
- `throttle:10,1`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `email` | string | Ya | `required|string|email|max:100` |
| `password` | string | Ya | `required|string` |

Hasil:
- sukses user biasa: redirect ke `GET /utama`
- sukses admin: redirect ke `GET /admin`
- gagal: redirect back dengan flash error

## Route Private

Semua route di bawah ini memakai middleware `auth`.

### `POST /auth/logout`

Hasil:
- logout session
- invalidate session
- regenerate CSRF token
- redirect ke `/`

### `GET /me`

Hasil:
- menampilkan halaman profil user di Blade

### `POST /summary`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `document` | file | Ya | `required|file|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,txt,csv,rtf,odt,ods,odp|max:20480` |
| `bahasa` | string | Ya | `required|string` |

Nilai `bahasa` yang dipakai:
- `indonesia`
- `english`

Hasil:
- sukses: redirect back dengan session `success` dan `summary_result`
- gagal validasi: redirect back dengan error Laravel
- gagal akses backend AI: redirect back dengan error

Catatan:
- kalau `AI_BACKEND_URL` kosong, controller tetap redirect back sukses dengan payload session generik

### `GET /fokus`

Hasil:
- redirect ke `GET /pomodoro`

### `GET /catatan`

Hasil:
- redirect ke `GET /utama`

### `GET /belajar`

Hasil:
- menampilkan halaman pilihan belajar

### `GET /pomodoro`

Hasil:
- menampilkan halaman timer pomodoro
- frontend memanggil backend web route test pomodoro yang aktif untuk kebutuhan tracking

### `GET /utama`

Nama route:
- `utama`

Hasil:
- menampilkan dashboard utama

## Method Backend Yang Belum Dipublish Ke Frontend

Method ini masih ada di controller, tetapi route publiknya belum dipasang lagi:

- `UserController::edit_profile()`
- `PomodoroController::post_to_pomodoro_history()`
- `PomodoroController::get_to_pomodoro_history()`

Alasan:
- kontrak request/response dengan frontend belum final
- backend sengaja disimpan dulu agar logic tidak hilang
- saat nanti dipasang lagi, flow yang disarankan tetap `back()` + flash/session atau pola lain yang disepakati

## Route Admin

Semua route admin memakai middleware:
- `auth`
- `admin.validate`

### `GET /admin`

Nama route:
- `admin.dashboard`

Hasil:
- menampilkan dashboard admin

### `GET /admin/pomodoro`

Nama route:
- `admin.pomodoro`

Hasil:
- menampilkan halaman pomodoro admin

### `GET /admin/users`

Nama route:
- `admin.users`

Hasil:
- menampilkan tabel user yang dirender langsung oleh Blade

### `POST /admin/users`

Nama route:
- `admin.users.store`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `username` | string | Ya | `required|string|max:100` |
| `email` | string | Ya | `required|string|email|max:100|unique:authentications,email` |
| `password` | string | Ya | `required|string|min:8` |

Hasil:
- sukses: redirect ke `GET /admin/users` dengan flash `success`
- gagal: redirect ke `GET /admin/users`, buka modal create, dan tampilkan error

### `PUT /admin/users/{user}`

Nama route:
- `admin.users.update`

Field:

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `displayName` | string | Ya | `required|string|max:100` |
| `email` | string | Ya | `required|string|email|max:100|unique:authentications,email,{id}` |
| `isAdmin` | integer | Ya | `required|integer|in:0,1` |
| `password` | string | Tidak | `nullable|string|min:8` |

Hasil:
- sukses: redirect ke `GET /admin/users` dengan flash `success`
- gagal: redirect ke `GET /admin/users`, buka modal edit, dan tampilkan error

### `DELETE /admin/users/{user}`

Nama route:
- `admin.users.delete`

Hasil:
- hapus user auth target
- redirect ke `GET /admin/users` dengan flash `success`

### `GET /dashboard`

Nama route:
- `dashboard`

Hasil:
- redirect ke `GET /admin`

## Pola Response

Pola response yang dipakai sekarang:

- halaman: `return view(...)`
- sukses submit form: `redirect(...)->with('success', '...')`
- validasi gagal: `$request->validate(...)` atau `withErrors(...)`
- akses ditolak: `abort(403)` atau redirect ke halaman lain

Tidak dipakai lagi:

- `routes/api.php`
- bearer token auth
- `success_format(...)`
- `error_format(...)`
- response JSON helper kustom

## Error Umum

- `302` ke halaman sebelumnya: submit form web biasa
- `401/403`: belum login atau bukan admin
- `422`: validasi gagal
- `429`: throttle pada login/register
- `500`: proses internal gagal, misalnya binary Go, DB, atau backend AI bermasalah
