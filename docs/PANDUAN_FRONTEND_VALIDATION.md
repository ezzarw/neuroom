# Panduan Frontend Agar Tidak Error Validate Backend

## Tujuan

Agar request dari frontend lolos `validate()` di backend Laravel.

## Endpoint

1. `POST /api/auth/register`
2. `POST /api/auth/login`

## Header Wajib

- `Content-Type: application/json`
- `Accept: application/json`

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

## Validasi di Frontend Sebelum Submit

- `trim` untuk `username` dan `email`
- jangan submit jika ada field kosong
- jika form pakai `fullname`, map ke `username` sebelum kirim
- jangan kirim field yang backend tidak pakai

## Handling Response

- `422`: validasi gagal
- `409`: email sudah terdaftar
- `401`: kredensial salah
- `429`: terlalu banyak request

## Setelah Sukses Register/Login

- simpan token dari response
- kirim header `Authorization: Bearer <token>` untuk endpoint private
