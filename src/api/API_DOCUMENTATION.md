# PawHaven API (Quick Reference)

All authenticated requests require an `Authorization: Bearer <session_token>` header. Legacy clients may pass `session_token` as a query/body field, but the header is preferred.

JSON responses follow `{ status, success, ... }`; failures carry `error` (and optional `details`) and use appropriate HTTP status codes (400 validation, 401 auth, 404 missing data, 500 server error).

---

## Authentication

| Method | Endpoint | Notes |
| --- | --- | --- |
| `POST` | `/api/auth/register` | JSON or multipart payload with `nama_lengkap`, `email`, `password`, `telepon`, `alamat`; optional `foto` file stored as profile picture. |
| `POST` | `/api/auth/login` | Body `{ email, password }`; returns `{ token, expires_at }` on success. |
| `GET` | `/api/auth/me` | Returns current user metadata without the photo. Payload includes `has_foto_profil` to signal photo availability. |
| `GET` | `/api/auth/me/photo` | Returns `{ has_foto_profil, foto_profil }`. `foto_profil` is a base64 JPEG/PNG string when present. Fetch separately to avoid blocking UI on large images. |

---

## User

| Method | Endpoint | Notes |
| --- | --- | --- |
| `POST` | `/api/user/update` | Update logged-in user. JSON fields are optional: `nama_lengkap`, `no_telp`, `alamat`, `role` (`user`/`admin`). Response echoes updated metadata; photo stays separate. |
| `DELETE` | `/api/user/delete` | Permanently removes the current account (cascades to related data). |
| `GET` | `/api/user/{id}` | Public info for a specific user; includes base64 profile photo when stored. |

---

## Hewan (Pets)

| Method | Endpoint | Notes |
| --- | --- | --- |
| `GET` | `/api/hewan` | List pets for the authenticated user. |
| `GET` | `/api/hewan/{id}` | Retrieve a single pet (must belong to the user). |
| `POST` | `/api/hewan/tambah` | Create a pet. Payload: `nama_pet`, `jenis_pet`; optional `ras`, `umur`, `jenis_kelamin`, `warna`, `alergi`, `catatan_medis`. |
| `POST` | `/api/hewan/update/{id}` | Update the specified pet using the same fields. |
| `POST` | `/api/hewan/delete/{id}` | Delete a pet. |

---

## Penitipan (Boarding)

| Method | Endpoint | Notes |
| --- | --- | --- |
| `GET` | `/api/penitipan` | Full list of bookings; each item includes `nama_paket`, `layanan` (JSON), `durasi`, `total_biaya`, status, etc. |
| `GET` | `/api/penitipan/{id}` | Details for one booking. |
| `GET` | `/api/penitipan/aktif` | Active bookings only (status-driven filter). |
| `GET` | `/api/penitipan/jumlah` | Summary counts/statistics for dashboard cards. |
| `POST` | `/api/penitipan/tambah` | Create booking. Include either `id_pet` or `pet_data` (same structure as pet creation). Required fields: `tgl_checkin`, `tgl_checkout`, `nama_paket`, `layanan` (array), `durasi`, `total_biaya`. |
| `POST` | `/api/penitipan/update/{id}` | Update booking using the same schema. |
| `POST` | `/api/penitipan/delete/{id}` | Remove a booking. |

---

## Miscellaneous

- `GET /api` is a simple health check returning `{ halo: true, status: true }`.
- Routes live under `src/api/`; see `backend.php` for the router map and handler wiring.
- Session tokens are stored in `User_Session` and validated by `src/api/auth/get_me.php`.

