# Panduan Frontend Neuroom

Dokumen ini adalah kontrak frontend terbaru untuk Neuroom.

## Prinsip Dasar

Frontend Neuroom sekarang memakai pola berikut:

- halaman dirender dari route web Laravel
- data dinamis diambil lewat fetch ke `/api/v1/...`
- autentikasi tetap berbasis cookie session browser
- semua request API harus kirim CSRF token
- response sukses dibaca dari format standar `success/reason/data`
- response error dibaca dari format standar `success/reason/errors`

Jangan pakai pola ini:

- jangan panggil `/api/...` lama tanpa `/v1`
- jangan pakai bearer token
- jangan pakai `localStorage` sebagai sumber kebenaran auth
- jangan mengandalkan payload top-level lama seperti `response.user` atau `response.redirect_to`

## Yang Baru Diperbarui

Perubahan terbaru yang harus diikuti frontend:

1. Endpoint fetch resmi pindah ke `/api/v1/...`.
2. Response sukses tidak membawa `errors`.
3. Hasil resource utama dibaca langsung dari `response.data`.
4. Error validasi dibaca dari `response.errors`.
5. Auth tidak mengirim redirect; frontend menentukan navigasi sendiri.

## Format Response Yang Harus Dipakai Frontend

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

Contoh baca di frontend:

```js
const response = await window.NeuroomApi.request('/api/v1/me');
const user = response.data;
const fieldErrors = response.errors || {};
```

## Route Web Yang Dipakai UI

- `GET /`
- `GET /belajar`
- `GET /pomodoro`
- `GET /utama`
- `GET /profile`
- `GET /admin`
- `GET /admin/users`
- `GET /admin/pomodoro`

## Route API Yang Dipakai Frontend

### Auth

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/me`

### Profile

- `PATCH /api/v1/me`

### Summary

- `POST /api/v1/summary`

### Pomodoro

- `GET /api/v1/pomodoro/history`
- `POST /api/v1/pomodoro/history`

### Admin

- `GET /api/v1/admin/dashboard`
- `GET /api/v1/admin/pomodoro`
- `GET /api/v1/admin/users`
- `POST /api/v1/admin/users`
- `PUT /api/v1/admin/users/{user}`
- `DELETE /api/v1/admin/users/{user}`

## Aturan Per Fitur

### Auth

Field register:

- `username`
- `email`
- `password`

Field login:

- `email`
- `password`

Setelah sukses:

- baca `response.reason`
- data user ada langsung di `response.data`

### Profile

Field:

- `display_name`
- `email`
- `profile_picture`

Setelah sukses:

- update UI dari `response.data`

### Summary

Field:

- `document`
- `bahasa`

Nilai `bahasa`:

- `indonesia`
- `english`

Setelah sukses:

- hasil ada langsung di `response.data`
- status ringkasan ada di `response.data.status`
- poin ringkasan ada di `response.data.output`
- kalau fallback, cek `response.data.fallback === true`

### Pomodoro

Store:

- kirim `duration_seconds`

Read history:

- baca array langsung dari `response.data`

### Admin Users

List:

- baca array langsung dari `response.data`

Create:

- `username`
- `email`
- `password`

Update:

- `display_name` atau `displayName`
- `email`
- `is_admin` atau `isAdmin`
- `password` opsional

Delete:

- cukup baca `response.reason`

## CSRF dan Session

Yang wajib:

- sertakan meta `csrf-token` di page
- kirim header `X-CSRF-TOKEN`
- gunakan `credentials: 'include'`
- jangan simpan token auth sendiri di browser

## Validasi Frontend Yang Aman

- trim input teks
- validasi email dasar di browser
- jangan submit field wajib kosong
- untuk file summary, batasi tipe file sesuai backend
- untuk password update user admin, kalau kosong jangan kirim nilai palsu

## Checklist Frontend

- [x] Pakai `/api/v1/...`
- [x] Baca payload dari `response.data`
- [x] Baca alasan response dari `response.reason`
- [x] Baca validasi dari `response.errors`
- [ ] Lakukan smoke test semua halaman setelah update kontrak JSON
- [ ] Pastikan tidak ada lagi file JS yang memanggil endpoint `/api/...` lama
