# Panduan Frontend Neuroom

Dokumen ini adalah kontrak frontend terbaru untuk Neuroom.

## Prinsip Dasar

Frontend Neuroom sekarang memakai pola berikut:

- halaman dirender dari route web Laravel
- data dinamis diambil lewat fetch ke `/api/v1/...`
- autentikasi tetap berbasis cookie session browser
- semua request API harus kirim CSRF token
- semua response API dibaca dari format standar `success/message/data/errors/meta`

Jangan pakai pola ini:

- jangan panggil `/api/...` lama tanpa `/v1`
- jangan pakai bearer token
- jangan pakai `localStorage` sebagai sumber kebenaran auth
- jangan mengandalkan payload top-level lama seperti `response.user` atau `response.redirect_to`

## Yang Baru Diperbarui

Perubahan terbaru yang harus diikuti frontend:

1. Endpoint fetch resmi pindah ke `/api/v1/...`.
2. Response JSON sekarang selalu dibungkus di `data` dan `meta`.
3. Redirect hasil login/register/logout dibaca dari `response.meta.redirect_to`.
4. Hasil resource utama dibaca dari `response.data`.
5. Error validasi dibaca dari `response.errors`.

## Format Response Yang Harus Dipakai Frontend

### Success

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {}
  },
  "meta": {
    "redirect_to": "/utama"
  }
}
```

### Error

```json
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {
    "email": ["Email sudah digunakan."]
  },
  "meta": {}
}
```

Contoh baca di frontend:

```js
const response = await window.NeuroomApi.request('/api/v1/me');
const user = response.data?.user;
const redirectTo = response.meta?.redirect_to;
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

- baca `response.message`
- redirect ke `response.meta.redirect_to`
- data user ada di `response.data.user`

### Profile

Field:

- `display_name`
- `email`
- `profile_picture`

Setelah sukses:

- update UI dari `response.data.user`

### Summary

Field:

- `document`
- `bahasa`

Nilai `bahasa`:

- `indonesia`
- `english`

Setelah sukses:

- hasil ada di `response.data.summary`
- status ringkasan ada di `response.data.summary.status`
- poin ringkasan ada di `response.data.summary.output`
- kalau fallback, cek `response.meta.fallback === true`

### Pomodoro

Store:

- kirim `duration_seconds`

Read history:

- baca array dari `response.data.sessions`

### Admin Users

List:

- baca array dari `response.data.users`

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

- cukup baca `response.message`

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
- [x] Baca redirect dari `response.meta.redirect_to`
- [x] Baca validasi dari `response.errors`
- [ ] Lakukan smoke test semua halaman setelah update kontrak JSON
- [ ] Pastikan tidak ada lagi file JS yang memanggil endpoint `/api/...` lama
