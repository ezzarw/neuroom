# Dokumentasi API Neuroom

Dokumen ini disusun dari implementasi source code terbaru.

## Ringkasan

- Base path API: `/api`
- Format request/response: JSON
- Auth: Laravel Sanctum Bearer Token
- Endpoint aktif:
  - `POST /api/auth/register`
  - `POST /api/auth/login`
- Rate limit:
  - Register: `5 request / menit`
  - Login: `10 request / menit`

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

Contoh request:

```json
{
  "username": "budi",
  "email": "budi@example.com",
  "password": "rahasia123"
}
```

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

Catatan:
- Nilai `username` di response adalah username unik hasil proses binary `go/bin/suffix_username`.
- Password tidak pernah dikembalikan dalam response.

### Response Error

- `422 Unprocessable Entity`: validasi gagal.
- `429 Too Many Requests`: terkena rate limit.
- `500 Internal Server Error`: proses binary gagal (`go/bin/suffix_username` atau `go/bin/hashingbcry`).

## 2) Login

- Method: `POST`
- URL: `/api/auth/login`
- Middleware: `throttle:10,1`

### Request Body

| Field | Tipe | Wajib | Rule |
|---|---|---|---|
| `email` | string | Ya | `required`, `string`, `email`, `max:100` |
| `password` | string | Ya | `required`, `string` |

Contoh request:

```json
{
  "email": "budi@example.com",
  "password": "rahasia123"
}
```

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

- `401 Unauthorized`: kredensial tidak valid (`Invalid credentials`).
- `422 Unprocessable Entity`: validasi gagal.
- `429 Too Many Requests`: terkena rate limit.
- `500 Internal Server Error`: proses verifikasi password via binary gagal.

## Format Header Untuk Endpoint Private

Jika nanti ada endpoint private dengan middleware `auth:sanctum`, kirim header:

```http
Authorization: Bearer <token>
Accept: application/json
```

## Contoh cURL

Register:

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"budi","email":"budi@example.com","password":"rahasia123"}'
```

Login:

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"budi@example.com","password":"rahasia123"}'
```
