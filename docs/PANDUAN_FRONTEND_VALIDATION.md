# Panduan Frontend Agar Tidak Error Validate Backend

## Tujuan

Agar request dari frontend lolos `validate()` di backend Laravel.

## Endpoint

1. `POST /api/auth/register`
2. `POST /api/auth/login`
3. `GET /api/admin/user-view`
4. `POST /api/admin/user-add`
5. `PUT /api/admin/user-edit`
6. `DELETE /api/admin/user-delete`

## Header Wajib

- Public (`/api/auth/*`):
  - `Content-Type: application/json`
  - `Accept: application/json`
- Admin (`/api/admin/*`):
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `Authorization: Bearer <token>`

## Payload Register

Gunakan key persis ini:

```json
{
  "username": "string",
  "email": "user@mail.com",
  "password": "minimal8karakter"
}
```

### Rule Register

- `username`: wajib, string, max 100 karakter
- `email`: wajib, format email valid, max 100, unik (belum terdaftar)
- `password`: wajib, string, min 8 karakter

## Payload Login

```json
{
  "email": "user@mail.com",
  "password": "string"
}
```

### Rule Login

- `email`: wajib, format email valid, max 100
- `password`: wajib, string

## Payload Admin User Add

```json
{
  "username": "string",
  "email": "user@mail.com",
  "password": "minimal8karakter"
}
```

### Rule Admin User Add

- `username`: wajib, string, max 100
- `email`: wajib, email valid, max 100, unik
- `password`: wajib, string, min 8

## Payload Admin User Edit

```json
{
  "id": 1,
  "displayName": "Nama Baru",
  "email": "baru@mail.com",
  "isAdmin": 0,
  "password": "opsional_min_8"
}
```

### Rule Admin User Edit

- `id`: wajib, integer, harus ada di `authentications.id`
- `displayName`: wajib, string, max 100
- `email`: wajib, email valid, max 100, unik (kecuali data dirinya sendiri)
- `isAdmin`: wajib, integer, hanya `0` atau `1`
- `password`: opsional, kalau dikirim harus min 8

## Payload Admin User Delete

```json
{
  "id": 1
}
```

### Rule Admin User Delete

- `id`: wajib, integer, harus ada di `authentications.id`

## Validasi di Frontend Sebelum Submit

- `trim` untuk `username` dan `email`
- jangan submit jika ada field kosong
- jika form pakai `fullname`, map ke `username` sebelum kirim
- jangan kirim field yang backend tidak pakai
- untuk `PUT /admin/user-edit`, kalau tidak ganti password: jangan kirim `password` atau kirim `null`
- untuk `PUT` dan `DELETE`, kirim payload di JSON body (bukan query params URL)

## Handling Response

- `422`: validasi gagal
- `401`: kredensial salah
- `403`: login valid tapi bukan admin
- `429`: terlalu banyak request
- `500`: error internal server

## Setelah Sukses Register/Login

- simpan token dari response
- kirim header `Authorization: Bearer <token>` untuk endpoint private
