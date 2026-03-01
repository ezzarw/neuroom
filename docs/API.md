# Dokumentasi API Neuroom

Dokumen ini mengikuti implementasi route dan controller terbaru.

## Ringkasan

- Base path API: `/api`
- Format request/response: JSON
- Auth private endpoint: Laravel Sanctum Bearer Token
- Endpoint aktif:
  - `POST /api/auth/register`
  - `POST /api/auth/login`
  - `GET /api/admin/user-view`
  - `POST /api/admin/user-add`
  - `PUT /api/admin/user-edit`
  - `DELETE /api/admin/user-delete`
- Middleware admin endpoint:
  - `auth:sanctum`
  - `admin.validate`
- Rate limit:
  - Register: `5 request / menit`
  - Login: `10 request / menit`

## Header Request

### Endpoint Public (`/api/auth/*`)

```http
Content-Type: application/json
Accept: application/json
```

### Endpoint Admin (`/api/admin/*`)

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <token>
```

## 1) Register

- Method: `POST`
- URL: `/api/auth/register`
- Middleware: `throttle:5,1`

### Request Body

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `username` | string | Ya | `required`, `string`, `max:100` |
| `email` | string | Ya | `required`, `string`, `email`, `max:100`, `unique:authentications,email` |
| `password` | string | Ya | `required`, `string`, `min:8` |

### Response Sukses

Status code: `201 Created`

```json
{
  "status": true,
  "data": {
    "email": "budi@example.com",
    "username": "budi_abc123"
  },
  "token": "1|xxxxx",
  "token_type": "Bearer"
}
```

### Response Error

- `422 Unprocessable Entity`: validasi gagal.
- `429 Too Many Requests`: terkena rate limit.
- `500 Internal Server Error`: proses binary gagal.

## 2) Login

- Method: `POST`
- URL: `/api/auth/login`
- Middleware: `throttle:10,1`

### Request Body

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `email` | string | Ya | `required`, `string`, `email`, `max:100` |
| `password` | string | Ya | `required`, `string` |

### Response Sukses

Status code: `200 OK`

```json
{
  "status": true,
  "data": {
    "email": "budi@example.com",
    "username": "budi_abc123"
  },
  "token": "2|yyyyy",
  "token_type": "Bearer"
}
```

### Response Error

- `401 Unauthorized`: kredensial tidak valid.
- `422 Unprocessable Entity`: validasi gagal.
- `429 Too Many Requests`: terkena rate limit.
- `500 Internal Server Error`: proses verifikasi password gagal.

## 3) Admin - User View

- Method: `GET`
- URL: `/api/admin/user-view`
- Middleware: `auth:sanctum`, `admin.validate`

### Response Sukses

Status code: `200 OK`

```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "username": "budi_abc123",
      "display_name": "budi",
      "email": "budi@example.com",
      "is_admin": 0
    }
  ]
}
```

## 4) Admin - User Add

- Method: `POST`
- URL: `/api/admin/user-add`
- Middleware: `auth:sanctum`, `admin.validate`

### Request Body

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `username` | string | Ya | `required`, `string`, `max:100` |
| `email` | string | Ya | `required`, `string`, `email`, `max:100`, `unique:authentications,email` |
| `password` | string | Ya | `required`, `string`, `min:8` |

### Response Sukses

Status code: `201 Created`

```json
{
  "status": true,
  "data": {
    "email": "budi@example.com",
    "username": "budi_abc123"
  },
  "token": "3|zzzzz",
  "token_type": "Bearer"
}
```

## 5) Admin - User Edit

- Method: `PUT`
- URL: `/api/admin/user-edit`
- Middleware: `auth:sanctum`, `admin.validate`

### Request Body

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `id` | integer | Ya | `required`, `integer`, `exists:authentications,id` |
| `displayName` | string | Ya | `required`, `string`, `max:100` |
| `email` | string | Ya | `required`, `string`, `email`, `max:100`, `unique (ignore id sendiri)` |
| `isAdmin` | integer | Ya | `required`, `integer`, `in:0,1` |
| `password` | string/null | Tidak | `nullable`, `string`, `min:8` |

### Response Sukses

Status code: `200 OK`

```json
{
  "status": true,
  "data": {
    "id": 1,
    "username": "budi_abc123",
    "email": "budi@example.com",
    "is_admin": 1
  }
}
```

Catatan:
- Jika `password` diubah, token lama user akan dihapus (revoke).

## 6) Admin - User Delete

- Method: `DELETE`
- URL: `/api/admin/user-delete`
- Middleware: `auth:sanctum`, `admin.validate`

### Request Body

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `id` | integer | Ya | `required`, `integer`, `exists:authentications,id` |

Catatan:
- Karena FK memakai `cascadeOnDelete`, data terkait di tabel `users` akan ikut terhapus.

## Error Umum Endpoint Admin

- `401 Unauthorized`: token tidak ada/tidak valid.
- `403 Forbidden`: user login tapi bukan admin (`is_admin != 1`).
- `422 Unprocessable Entity`: payload tidak lolos validasi.
