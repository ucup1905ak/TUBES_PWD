# PawHaven API Documentation

> Pet Boarding System - Complete REST API Reference

---

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [User Endpoints](#user-endpoints)
4. [Pet Endpoints](#pet-endpoints)
5. [Penitipan (Boarding) Endpoints](#penitipan-boarding-endpoints)
6. [Error Handling](#error-handling)
7. [CRUD Summary](#crud-summary)
8. [Code Examples](#code-examples)

---

## Overview

### Base URL
```
http://localhost:80/api
```

### Content Type
All requests and responses use JSON:
```
Content-Type: application/json
```

### Authentication
Most endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer <session_token>
```

### Database Schema

| Table | Description |
|-------|-------------|
| User | User accounts with profile information |
| Pet | Pets owned by users |
| Penitipan | Pet boarding records |
| Paket_Kamar | Room/boarding packages |
| Layanan | Additional services |
| User_Session | Active user sessions |
| Penitipan_Layanan | Junction table for boarding-services |

### Field Name Mappings

Some API request fields are mapped to different database column names:

| API Request Field | Database Column | Description |
|-------------------|-----------------|-------------|
| `username` | `nama_lengkap` | User's full name |
| `telepon` | `no_telp` | Phone number |
| `foto` | `foto_profil` | User profile photo (MEDIUMBLOB) |

---

## Authentication

### POST /api/auth/register
Register a new user account.

**Request Body:**
```json
{
  "username": "John Doe",
  "email": "john@example.com",
  "password": "hashed_password_sha256",
  "confirmPassword": "hashed_password_sha256",
  "telepon": "081234567890",
  "alamat": "Jl. Example No. 123",
  "role": "user",
  "foto": "base64_encoded_image"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| username | string | Yes | Full name, stored as `nama_lengkap` (max 100 chars) |
| email | string | Yes | Unique email address |
| password | string | Yes | SHA-256 hashed password (64 chars) |
| confirmPassword | string | Yes | Must match password |
| telepon | string | Yes | Phone number, stored as `no_telp` (max 15 chars) |
| alamat | string | Yes | Address |
| role | string | No | `user` or `admin` (default: `user`) |
| foto | string | No | Base64-encoded profile photo (max 16MB, JPG/PNG/GIF) |

> **Note:** The field names `username` and `telepon` are mapped internally to `nama_lengkap` and `no_telp` in the database.

**Success Response (201):**
```json
{
  "status": 201,
  "success": true,
  "message": "User registered successfully.",
  "user_id": 1
}
```

**Error Responses:**
- `400` - Validation failed
- `409` - Email/username already exists
- `500` - Server error

---

### POST /api/auth/login
Authenticate user and get session token.

**Request Body (with email):**
```json
{
  "email": "john@example.com",
  "password": "hashed_password_sha256"
}
```

**Request Body (with username/nama_lengkap):**
```json
{
  "username": "John Doe",
  "password": "hashed_password_sha256"
}
```

> **Note:** You can login using either `email` OR `username` (which matches the `nama_lengkap` field).

**Success Response (200):**
```json
{
  "status": 200,
  "session_token": "64_character_hex_string",
  "expires_at": "2025-12-04 12:00:00"
}
```

> **Important:** The login response does NOT include a `success` field. Check `status === 200` for success.

**Error Responses:**
- `400` - Missing credentials
- `401` - Invalid credentials

---

### GET /api/auth/me
Get current authenticated user's profile.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "user": {
    "id_user": 1,
    "nama_lengkap": "John Doe",
    "email": "john@example.com",
    "no_telp": "081234567890",
    "alamat": "Jl. Example No. 123",
    "foto_profil": "base64_encoded_image_or_null",
    "role": "user"
  }
}
```

---

## User Endpoints

### GET /api/user/{id}
Get user by ID (public endpoint, no authentication required).

**Success Response (200):**
```json
{
  "success": true,
  "user": {
    "id_user": 1,
    "nama_lengkap": "John Doe",
    "email": "john@example.com",
    "no_telp": "081234567890",
    "alamat": "Jl. Example No. 123",
    "foto_profil": "base64_encoded_image_or_null",
    "role": "user"
  }
}
```

**Error Responses:**
- `400` - Invalid user ID
- `404` - User not found

---

### POST /api/user/update
Update current user's profile.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Request Body:**
```json
{
  "nama_lengkap": "John Updated",
  "no_telp": "089876543210",
  "alamat": "Jl. New Address No. 456",
  "role": "user"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| nama_lengkap | string | No | Updated full name (max 100 chars) |
| no_telp | string | No | Updated phone number (max 15 chars) |
| alamat | string | No | Updated address |
| role | string | No | `user` or `admin` |

> **Note:** All fields are optional. Only provided fields will be updated. At least one field must be provided.

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "message": "User updated successfully.",
  "user": {
    "id_user": 1,
    "nama_lengkap": "John Updated",
    "email": "john@example.com",
    "no_telp": "089876543210",
    "alamat": "Jl. New Address No. 456",
    "foto_profil": "base64_encoded_image_or_null",
    "role": "user"
  }
}
```

**Error Responses:**
- `400` - No fields to update / Validation failed
- `401` - Unauthorized

---

### DELETE /api/user/delete
Delete current user's account.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "message": "Account deleted successfully."
}
```

> ⚠️ **Warning:** This action is irreversible. All associated pets and boarding records will also be deleted (CASCADE).

---

## Pet Endpoints

### GET /api/hewan
Get all pets for authenticated user.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "pets": [
    {
      "id_pet": 1,
      "id_user": 1,
      "nama_pet": "Buddy",
      "jenis_pet": "Anjing",
      "ras": "Golden Retriever",
      "umur": 3,
      "jenis_kelamin": "Jantan",
      "warna": "Emas",
      "alergi": null,
      "catatan_medis": "Vaksin lengkap",
      "foto_pet": null
    }
  ]
}
```

---

### GET /api/hewan/{id}
Get specific pet by ID.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "pet": {
    "id_pet": 1,
    "id_user": 1,
    "nama_pet": "Buddy",
    "jenis_pet": "Anjing",
    "ras": "Golden Retriever",
    "umur": 3,
    "jenis_kelamin": "Jantan",
    "warna": "Emas",
    "alergi": null,
    "catatan_medis": "Vaksin lengkap",
    "foto_pet": null
  }
}
```

**Error Response (404):**
```json
{
  "status": 404,
  "success": false,
  "error": "Pet not found"
}
```

---

### POST /api/hewan/tambah
Add a new pet.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Request Body:**
```json
{
  "nama_pet": "Buddy",
  "jenis_pet": "Anjing",
  "ras": "Golden Retriever",
  "umur": 3,
  "jenis_kelamin": "Jantan",
  "warna": "Emas",
  "alergi": "Tidak ada",
  "catatan_medis": "Vaksin lengkap",
  "foto_pet": "base64_or_url"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| nama_pet | string | Yes | Pet name (max 100 chars) |
| jenis_pet | string | No | Species (Anjing, Kucing, etc., max 50 chars) |
| ras | string | No | Breed (max 50 chars) |
| umur | integer | No | Age in years |
| jenis_kelamin | string | No | `Jantan` or `Betina` (max 10 chars) |
| warna | string | No | Color (max 50 chars) |
| alergi | string | No | Known allergies |
| catatan_medis | string | No | Medical notes |
| foto_pet | string | No | Photo URL or base64 string |

**Success Response (201):**
```json
{
  "status": 201,
  "success": true,
  "message": "Pet added successfully.",
  "pet_id": 1
}
```

---

### PUT /api/hewan/update/{id}
Update existing pet.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Request Body:**
```json
{
  "nama_pet": "Buddy Jr",
  "umur": 4,
  "foto_pet": "new_photo_base64_or_url"
}
```

| Field | Type | Description |
|-------|------|-------------|
| nama_pet | string | Updated pet name |
| jenis_pet | string | Updated species |
| ras | string | Updated breed |
| umur | integer | Updated age |
| jenis_kelamin | string | Updated gender |
| warna | string | Updated color |
| alergi | string | Updated allergies |
| catatan_medis | string | Updated medical notes |
| foto_pet | string | Updated photo |

> All fields are optional. Only provided fields will be updated.

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "message": "Pet updated successfully."
}
```

---

### DELETE /api/hewan/delete/{id}
Delete a pet.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "message": "Pet deleted successfully."
}
```

**Error Responses:**
- `401` - Authorization required
- `404` - Pet not found (or not owned by user)

---

## Penitipan (Boarding) Endpoints

### GET /api/penitipan
Get all boarding records for authenticated user.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "penitipan": [
    {
      "id_penitipan": 1,
      "id_user": 1,
      "id_pet": 1,
      "tgl_checkin": "2025-12-15",
      "tgl_checkout": "2025-12-20",
      "id_paket": 1,
      "status_penitipan": "aktif",
      "nama_pet": "Buddy",
      "jenis_pet": "Anjing",
      "nama_paket": "Paket Premium",
      "harga_per_hari": 150000.00
    }
  ]
}
```

---

### GET /api/penitipan/aktif
Get only active boarding records.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "penitipan": [
    {
      "id_penitipan": 1,
      "id_user": 1,
      "id_pet": 1,
      "tgl_checkin": "2025-12-15",
      "tgl_checkout": "2025-12-20",
      "id_paket": 1,
      "status_penitipan": "aktif",
      "nama_pet": "Buddy",
      "jenis_pet": "Anjing"
    }
  ]
}
```

---

### GET /api/penitipan/jumlah
Get count of boarding records. Works with or without authentication.

**Headers (Optional):**
```
Authorization: Bearer <session_token>
```

**Success Response (200) - Without Auth (Admin Dashboard):**
```json
{
  "status": 200,
  "success": true,
  "total": 50
}
```
> Returns total count of ALL boarding records.

**Success Response (200) - With Auth (User):**
```json
{
  "status": 200,
  "success": true,
  "total": 5
}
```
> Returns count of boarding records for the authenticated user only.

---

### GET /api/penitipan/{id}
Get specific boarding record.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "penitipan": {
    "id_penitipan": 1,
    "id_user": 1,
    "id_pet": 1,
    "tgl_checkin": "2025-12-15",
    "tgl_checkout": "2025-12-20",
    "id_paket": 1,
    "status_penitipan": "aktif",
    "nama_pet": "Buddy",
    "jenis_pet": "Anjing"
  }
}
```

---

### POST /api/penitipan/tambah
Create a new boarding record.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Request Body:**
```json
{
  "id_pet": 1,
  "tgl_checkin": "2025-12-15",
  "tgl_checkout": "2025-12-20",
  "id_paket": 1
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| id_pet | integer | Yes | Pet ID (must belong to user) |
| tgl_checkin | date | Yes | Check-in date (YYYY-MM-DD) |
| tgl_checkout | date | Yes | Check-out date (YYYY-MM-DD) |
| id_paket | integer | No | Package ID |
| status_penitipan | string | No | Default: `aktif` |

**Success Response (201):**
```json
{
  "status": 201,
  "success": true,
  "message": "Penitipan created successfully.",
  "penitipan_id": 1
}
```

**Validation Rules:**
- Check-out date must be after check-in date
- Pet must belong to the authenticated user
- Date format must be YYYY-MM-DD

**Error Responses:**
- `400` - Validation failed / Pet not found or does not belong to you
- `401` - Authorization required
- `500` - Server error

---

### PUT /api/penitipan/update/{id}
Update existing boarding record.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Request Body:**
```json
{
  "tgl_checkout": "2025-12-22",
  "status_penitipan": "selesai"
}
```

| Field | Type | Description |
|-------|------|-------------|
| id_pet | integer | Change pet (must belong to user) |
| tgl_checkin | date | Update check-in date (YYYY-MM-DD) |
| tgl_checkout | date | Update check-out date (YYYY-MM-DD) |
| id_paket | integer | Change package (can be null/empty to clear) |
| status_penitipan | string | `aktif`, `selesai`, `batal`, etc. |

> All fields are optional. Only provided fields will be updated. At least one field must be provided.

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "message": "Penitipan updated successfully."
}
```

**Error Responses:**
- `400` - No fields to update
- `404` - Penitipan not found

---

### DELETE /api/penitipan/delete/{id}
Delete a boarding record.

**Headers:**
```
Authorization: Bearer <session_token>
```

**Success Response (200):**
```json
{
  "status": 200,
  "success": true,
  "message": "Penitipan deleted successfully."
}
```

**Error Responses:**
- `401` - Authorization required
- `404` - Penitipan not found (or not owned by user)

---

## Error Handling

### Standard Error Response Format
```json
{
  "status": 400,
  "success": false,
  "error": "Error message here",
  "details": ["Additional details", "if available"]
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request - Validation error |
| 401 | Unauthorized - Invalid/expired token |
| 404 | Not Found - Resource doesn't exist |
| 409 | Conflict - Duplicate entry |
| 500 | Server Error |

---

## CRUD Summary

### User CRUD ✅
| Operation | Endpoint | Method |
|-----------|----------|--------|
| **C**reate | `/api/auth/register` | POST |
| **R**ead | `/api/auth/me` | GET |
| **R**ead | `/api/user/{id}` | GET |
| **U**pdate | `/api/user/update` | POST |
| **D**elete | `/api/user/delete` | DELETE |

### Pet CRUD ✅
| Operation | Endpoint | Method |
|-----------|----------|--------|
| **C**reate | `/api/hewan/tambah` | POST |
| **R**ead | `/api/hewan` | GET |
| **R**ead | `/api/hewan/{id}` | GET |
| **U**pdate | `/api/hewan/update/{id}` | PUT |
| **D**elete | `/api/hewan/delete/{id}` | DELETE |

### Penitipan CRUD ✅
| Operation | Endpoint | Method |
|-----------|----------|--------|
| **C**reate | `/api/penitipan/tambah` | POST |
| **R**ead | `/api/penitipan` | GET |
| **R**ead | `/api/penitipan/{id}` | GET |
| **R**ead | `/api/penitipan/aktif` | GET |
| **R**ead | `/api/penitipan/jumlah` | GET |
| **U**pdate | `/api/penitipan/update/{id}` | PUT |
| **D**elete | `/api/penitipan/delete/{id}` | DELETE |

---

## Code Examples

### Password Hashing (SHA-256)
```javascript
async function sha256(message) {
  const msgBuffer = new TextEncoder().encode(message);
  const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}
```

### Authenticated API Request Helper
```javascript
async function fetchWithAuth(url, options = {}) {
  const token = localStorage.getItem('session_token');
  
  return fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...options.headers
    }
  });
}
```

### Register User
```javascript
async function register(data) {
  const hashedPassword = await sha256(data.password);
  
  const response = await fetch('/api/auth/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      username: data.username,
      email: data.email,
      password: hashedPassword,
      confirmPassword: hashedPassword,
      telepon: data.telepon,
      alamat: data.alamat
    })
  });
  
  return await response.json();
}
```

### Login and Store Token
```javascript
async function login(email, password) {
  const hashedPassword = await sha256(password);
  
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password: hashedPassword })
  });
  
  const data = await response.json();
  
  // Note: Login response uses status, not success field
  if (data.status === 200) {
    localStorage.setItem('session_token', data.session_token);
    localStorage.setItem('session_expires_at', data.expires_at);
  }
  
  return data;
}
```

### Add Pet
```javascript
async function addPet(petData) {
  const response = await fetchWithAuth('/api/hewan/tambah', {
    method: 'POST',
    body: JSON.stringify(petData)
  });
  
  return await response.json();
}
```

### Create Boarding
```javascript
async function createBoarding(petId, checkin, checkout) {
  const response = await fetchWithAuth('/api/penitipan/tambah', {
    method: 'POST',
    body: JSON.stringify({
      id_pet: petId,
      tgl_checkin: checkin,
      tgl_checkout: checkout
    })
  });
  
  return await response.json();
}
```

---

### cURL Examples

**Register:**
```bash
curl -X POST http://localhost:80/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"John","email":"john@example.com","password":"HASH","confirmPassword":"HASH","telepon":"08123","alamat":"Jl. Test"}'
```

**Login:**
```bash
curl -X POST http://localhost:80/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"HASH"}'
```

**Get User Profile:**
```bash
curl -X GET http://localhost:80/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Add Pet:**
```bash
curl -X POST http://localhost:80/api/hewan/tambah \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"nama_pet":"Buddy","jenis_pet":"Anjing","umur":3}'
```

**Create Boarding:**
```bash
curl -X POST http://localhost:80/api/penitipan/tambah \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"id_pet":1,"tgl_checkin":"2025-12-15","tgl_checkout":"2025-12-20"}'
```

---

© 2025 PawHaven - Pet Boarding System
