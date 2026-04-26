# Inventaris dan Standar Response JSON

Dokumen ini menjelaskan kontrak response JSON Neuroom yang berlaku sekarang.

## Standar Global

Semua endpoint API `/api/v1/...` wajib mengembalikan bentuk berikut.

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

## Arti Tiap Field

- `success`: status boolean response
- `message`: pesan utama yang aman dipakai UI
- `data`: payload bisnis utama
- `errors`: detail error field atau error tambahan
- `meta`: konteks tambahan seperti `redirect_to`, `fallback`, pagination, atau metadata lain

## Kenapa Ada `meta`

`meta` dipakai untuk informasi tambahan yang bukan data utama.

Contoh isi `meta` di project ini:

- `redirect_to`
- `fallback`

Contoh umum lain kalau nanti dibutuhkan:

- `page`
- `per_page`
- `total`

Tujuannya supaya payload bisnis tetap rapi di `data`, dan informasi pendukung tidak bercampur dengan isi utama.

## Route API Aktif

Hasil `php artisan route:list --path=api/v1`:

| Method | Endpoint |
|---|---|
| `POST` | `/api/v1/auth/register` |
| `POST` | `/api/v1/auth/login` |
| `POST` | `/api/v1/auth/logout` |
| `GET` | `/api/v1/me` |
| `PATCH` | `/api/v1/me` |
| `POST` | `/api/v1/summary` |
| `GET` | `/api/v1/pomodoro/history` |
| `POST` | `/api/v1/pomodoro/history` |
| `GET` | `/api/v1/admin/dashboard` |
| `GET` | `/api/v1/admin/pomodoro` |
| `GET` | `/api/v1/admin/users` |
| `POST` | `/api/v1/admin/users` |
| `PUT` | `/api/v1/admin/users/{user}` |
| `DELETE` | `/api/v1/admin/users/{user}` |

## Contoh Response Per Endpoint

### Auth Register

```json
{
  "success": true,
  "message": "Register berhasil. Selamat datang.",
  "data": {
    "user": {
      "id": 1,
      "username": "budi",
      "email": "budi@example.com",
      "is_admin": 0,
      "display_name": "Budi",
      "profile_picture": null,
      "profile_picture_url": null
    }
  },
  "meta": {
    "redirect_to": "/utama"
  }
}
```

### Auth Login

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 1,
      "username": "budi",
      "email": "budi@example.com",
      "is_admin": 0,
      "display_name": "Budi",
      "profile_picture": null,
      "profile_picture_url": null
    }
  },
  "meta": {
    "redirect_to": "/utama"
  }
}
```

### Auth Logout

```json
{
  "success": true,
  "message": "Logout berhasil.",
  "data": {},
  "meta": {
    "redirect_to": "/"
  }
}
```

### Me

```json
{
  "success": true,
  "message": "Profil berhasil diambil.",
  "data": {
    "user": {
      "id": 1,
      "username": "budi",
      "email": "budi@example.com",
      "is_admin": 0,
      "display_name": "Budi",
      "profile_picture": null,
      "profile_picture_url": null
    }
  },
  "meta": {}
}
```

### Profile Update

```json
{
  "success": true,
  "message": "Profil berhasil diupdate.",
  "data": {
    "user": {
      "id": 1,
      "username": "budi",
      "email": "budi@example.com",
      "is_admin": 0,
      "display_name": "Budi Update",
      "profile_picture": "avatar.jpg",
      "profile_picture_url": "/storage/profile_picture/avatar.jpg"
    }
  },
  "meta": {}
}
```

### Summary Success

```json
{
  "success": true,
  "message": "Ringkasan berhasil dibuat.",
  "data": {
    "summary": {
      "status": "success",
      "output": ["- poin pertama", "- poin kedua"],
      "document": {
        "name": "materi.pdf",
        "path": "document_for_summaries/file.pdf",
        "mime_type": "application/pdf"
      }
    }
  },
  "meta": {}
}
```

### Summary Fallback

```json
{
  "success": true,
  "message": "Dokumen berhasil diupload, tetapi integrasi AI belum dikonfigurasi.",
  "data": {
    "summary": {
      "status": "fallback",
      "output": [
        "Nama file: materi.pdf",
        "Bahasa ringkasan: indonesia",
        "Ukuran file: 102400 byte"
      ]
    }
  },
  "meta": {
    "fallback": true
  }
}
```

### Pomodoro Store

```json
{
  "success": true,
  "message": "Data pomodoro berhasil ditambahkan.",
  "data": {
    "session": {
      "id": 1,
      "session": 1,
      "date": "2026-04-25",
      "duration_seconds": 1500,
      "duration": "00:25:00",
      "created_at": "2026-04-25T10:00:00.000000Z"
    }
  },
  "meta": {}
}
```

### Pomodoro History

```json
{
  "success": true,
  "message": "Riwayat pomodoro berhasil diambil.",
  "data": {
    "sessions": []
  },
  "meta": {}
}
```

### Admin Users Index

```json
{
  "success": true,
  "message": "Daftar user berhasil diambil.",
  "data": {
    "users": []
  },
  "meta": {}
}
```

### Validation Error

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

### Auth Error

```json
{
  "success": false,
  "message": "Unauthenticated.",
  "errors": {},
  "meta": {}
}
```

### Forbidden Error

```json
{
  "success": false,
  "message": "Forbidden.",
  "errors": {},
  "meta": {}
}
```
