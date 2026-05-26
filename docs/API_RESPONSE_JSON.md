# Inventaris dan Standar Response JSON

Dokumen ini menjelaskan kontrak response JSON Neuroom yang berlaku sekarang.

## Standar Global

### Success

```json
{
  "success": true,
  "reason": "Operasi berhasil.",
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

## Aturan Field

- Response sukses hanya wajib punya `success`, `reason`, dan `data`.
- Response error wajib punya `success`, `reason`, dan `errors`.
- `reason` adalah alasan atau pesan utama yang aman dipakai UI.
- `data` adalah payload bisnis utama.
- `errors` hanya muncul saat error.

## Aturan Data

- Resource tunggal langsung masuk ke `data`, tidak dibungkus `user`, `note`, `summary`, atau nama resource lain.
- List langsung masuk ke `data` sebagai array item, tidak dibungkus `users` atau `sessions`.
- Informasi tambahan seperti fallback masuk ke `data`, bukan `meta`.
- Endpoint auth tidak mengirim redirect; frontend menentukan navigasi sendiri dari state dan role user.

## Contoh Resource Tunggal

```json
{
  "success": true,
  "reason": "Login berhasil.",
  "data": {
    "id": 1,
    "username": "budi",
    "email": "budi@example.com",
    "is_admin": 0,
    "display_name": "Budi",
    "profile_picture": null,
    "profile_picture_url": null
  }
}
```

## Contoh List

```json
{
  "success": true,
  "reason": "Daftar user berhasil diambil.",
  "data": [
    {
      "id": 1,
      "username": "budi",
      "email": "budi@example.com"
    }
  ]
}
```

## Contoh Fallback

```json
{
  "success": true,
  "reason": "Dokumen berhasil diupload, tetapi integrasi AI belum dikonfigurasi.",
  "data": {
    "status": "fallback",
    "fallback": true,
    "output": ["Nama file: materi.pdf"]
  }
}
```
