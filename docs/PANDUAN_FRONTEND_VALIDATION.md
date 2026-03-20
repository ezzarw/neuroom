# Panduan Frontend Untuk Flow Web Neuroom

Dokumen ini menjelaskan cara frontend harus berinteraksi dengan backend Neuroom setelah alurnya dipindahkan ke web route biasa.

## Yang Perlu Frontend Tambahkan

Bagian ini sengaja ditaruh di atas supaya tim frontend bisa langsung lihat pekerjaan yang perlu dikerjakan.

### Sudah Siap Dikerjakan Frontend

#### 1. Auth Web Flow

Frontend perlu memastikan UI login, register, dan logout mengikuti route ini:

- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout`

Yang perlu frontend tambahkan atau pastikan:

- form register kirim `username`, `email`, `password`
- form login kirim `email`, `password`
- setelah login, hormati redirect backend ke `/admin` atau `/utama`
- logout dilakukan lewat form `POST`, bukan hanya clear state lokal

#### 2. Halaman Profil User

Backend sudah menyediakan:

- `GET /me`

Yang perlu frontend tambahkan atau pastikan:

- halaman profil baca data dari page render `/me`
- jangan ambil status auth dari `localStorage`
- kalau butuh edit profil, tunggu route final karena endpoint submit-nya belum dipasang lagi

#### 3. Fitur Summary

Backend sudah menyediakan:

- `POST /summary`

Yang perlu frontend tambahkan atau pastikan:

- form upload kirim `document` dan `bahasa`
- pakai `multipart/form-data`
- setelah submit, frontend baca hasil dari flash session `summary_result`
- UI hasil ringkasan sebaiknya siap menampilkan:
  - `summary_result.message`
  - `summary_result.status`
  - `summary_result.output`

Catatan:

- backend saat ini `redirect back()`
- artinya frontend perlu punya halaman sendiri yang memang berisi form summary + area hasil summary

#### 4. Admin Users CRUD

Backend sudah menyediakan:

- `GET /admin/users`
- `POST /admin/users`
- `PUT /admin/users/{user}`
- `DELETE /admin/users/{user}`

Yang perlu frontend tambahkan atau pastikan:

- halaman daftar user admin
- modal atau form create user
- modal atau form edit user
- tombol delete user dengan form `DELETE`
- tampilan flash message sukses atau error setelah redirect

#### 5. Navigasi Halaman Utama

Backend sudah menyediakan halaman:

- `GET /belajar`
- `GET /pomodoro`
- `GET /utama`
- `GET /fokus` yang redirect ke `/pomodoro`
- `GET /catatan` yang redirect ke `/utama`

Yang perlu frontend tambahkan atau pastikan:

- semua link menu pakai route Laravel yang benar
- jangan pakai file statis `.html`
- UI harus siap menerima redirect dari `/fokus` dan `/catatan`

### Belum Siap Final, Jangan Dianggap Kontrak Tetap

#### 1. Edit Profile Submit

Status backend:

- logic backend ada di `UserController::edit_profile()`
- route publiknya belum dipasang

Artinya untuk frontend:

- jangan implement submit final dulu
- kalau mau mulai desain UI, batasi di layout saja
- field yang kemungkinan dipakai nanti:
  - `display_name`
  - `email`
  - `profile_picture`

#### 2. Pomodoro Backend Submit/Get

Status backend:

- logic backend ada di `PomodoroController`
- route final untuk post/get belum dipasang

Artinya untuk frontend:

- boleh lanjut desain UI pomodoro
- jangan anggap tracking session ke backend sudah final
- perlu keputusan dulu apakah nanti pakai form biasa atau AJAX kecil dengan session web

### Urutan Kerja Frontend Yang Disarankan

1. Finalkan auth form dan redirect handling
2. Finalkan halaman summary dengan submit form + render `summary_result`
3. Finalkan admin users CRUD
4. Rapikan seluruh navigasi agar tidak ada link statis lama
5. Setelah itu baru sepakati kontrak final untuk edit profile dan pomodoro

### Hal Yang Jangan Dilakukan Frontend

- jangan panggil `/api/...`
- jangan pakai bearer token
- jangan pakai `sanctum/csrf-cookie`
- jangan simpan auth sensitif di `localStorage`
- jangan menganggap edit profile dan pomodoro submit sudah final sebelum route-nya dipasang

## Prinsip Dasar

Frontend sekarang harus menganggap backend sebagai aplikasi Laravel web biasa:

- submit form ke route web
- kirim CSRF token standar Laravel
- biarkan browser membawa cookie session
- baca hasil dari redirect, flash message, dan Blade render

Jangan pakai pola lama:

- jangan panggil `/api/...`
- jangan tunggu response JSON sebagai alur utama
- jangan pakai bearer token
- jangan pakai `sanctum/csrf-cookie`

## Route Yang Dipakai Frontend

Route utama:

- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout`
- `GET /me`
- `GET /belajar`
- `GET /pomodoro`
- `GET /utama`
- `POST /summary`
- `GET /admin/users`
- `POST /admin/users`
- `PUT /admin/users/{user}`
- `DELETE /admin/users/{user}`

## CSRF dan Session

Yang wajib untuk form Blade:

- selalu sertakan `@csrf`
- untuk method `PUT` atau `DELETE`, sertakan `@method('PUT')` atau `@method('DELETE')`
- biarkan browser mengirim cookie session standar Laravel

Kalau ada AJAX kecil di halaman Blade:

- ambil token dari meta `csrf-token`
- kirim header `X-CSRF-TOKEN`
- tetap gunakan cookie session browser

## Aturan Form Auth

### Register

Action:
- `POST /auth/register`

Field:
- `username`
- `email`
- `password`

Hasil sukses:
- redirect ke `/utama`

### Login

Action:
- `POST /auth/login`

Field:
- `email`
- `password`

Hasil sukses:
- admin ke `/admin`
- user biasa ke `/utama`

### Logout

Action:
- `POST /auth/logout`

Hasil sukses:
- redirect ke `/`

## Aturan Form Summary

Action:
- `POST /summary`

Encoding:
- `multipart/form-data`

Field:
- `document`
- `bahasa`

Nilai `bahasa` yang valid:
- `indonesia`
- `english`

Hasil sukses:
- redirect back ke halaman yang mengirim form
- hasil ringkasan dibaca dari flash session `summary_result`

## Aturan Form Admin Users

### Create User

Action:
- `POST /admin/users`

Field:
- `username`
- `email`
- `password`

Hasil:
- sukses redirect ke `/admin/users`
- gagal redirect ke `/admin/users` dan modal create dibuka lagi

### Edit User

Action:
- `PUT /admin/users/{user}`

Field:
- `displayName`
- `email`
- `isAdmin`
- `password` opsional

Hasil:
- sukses redirect ke `/admin/users`
- gagal redirect ke `/admin/users` dan modal edit dibuka lagi

### Delete User

Action:
- `DELETE /admin/users/{user}`

Hasil:
- sukses redirect ke `/admin/users`

## Edit Profile dan Pomodoro

Status saat ini:

- route final untuk edit profile belum dipasang
- route final untuk post/get data pomodoro juga belum dipasang

Catatan:

- logic backend-nya masih ada
- frontend perlu sepakati dulu bentuk route, payload, dan cara menampilkan hasil

## Validasi Frontend Sebelum Submit

Yang aman dilakukan di frontend:

- `trim` input teks
- jangan submit field wajib kosong
- untuk email, validasi format dasar di browser
- untuk file summary, batasi jenis file sesuai accept list
- untuk password edit admin, kalau kosong jangan kirim nilai palsu

Yang jangan dilakukan:

- jangan kirim field ekstra yang backend tidak pakai
- jangan mengandalkan local state sebagai sumber otorisasi
- jangan menyimpan token auth di `localStorage`

## Pola Menampilkan Status Ke User

Karena flow backend berbasis redirect:

- sukses dibaca dari flash session seperti `success`
- error validasi dibaca dari `$errors`
- beberapa halaman juga memakai flash `error`

Frontend tidak perlu memaksa semua hasil jadi toast berbasis JSON.

## Navigasi Yang Sudah Diperbaiki

Beberapa link lama berbasis file statis sudah diganti ke route web:

- link `Catatan` sekarang menuju `/catatan`
- `GET /catatan` saat ini redirect ke `/utama`
- card `Pelajaran Umum` sekarang menuju `/utama`

Kalau menambah menu baru:

- gunakan path route Laravel
- jangan link ke file `.html` statis

## Checklist Frontend

- [x] Tidak memakai `/api/...`
- [x] Tidak memakai flow `sanctum/csrf-cookie`
- [x] Admin users submit lewat form web
- [x] Summary submit lewat form web
- [x] Navigasi utama tidak lagi link ke file `.html`
- [ ] Tambahkan `autocomplete` yang sesuai di form-form admin untuk mengurangi warning browser
