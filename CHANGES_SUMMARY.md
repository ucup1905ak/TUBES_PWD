# Database Schema & API Changes Summary

## Issues Fixed

### 1. Fatal Error: Incorrect integer value for `id_paket`
**Error Message**: `Incorrect integer value: 'reguler' for column pwd.Penitipan.id_paket at row 1`

**Root Cause**: The `Penitipan` table schema expected `id_paket` as an INTEGER (foreign key reference), but the frontend was sending package names as strings (e.g., 'reguler').

**Solution**: Updated the database schema to store package information properly.

### 2. Duplicate Pet Entries
**Issue**: Pet registration and penitipan creation were not handled independently, potentially causing duplicate pet entries.

**Solution**: Created a new `registerOrGetPet()` function that:
- Checks if a pet with the same name already exists for the user
- Returns existing pet ID if found
- Creates new pet only if it doesn't exist
- Provides clear feedback on whether pet was newly created or already existed

---

## Changes Made

### Database Schema (`src/config/database_setup.php`)

**Old Penitipan Table**:
```sql
CREATE TABLE IF NOT EXISTS Penitipan (
    id_penitipan INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_pet INT,
    tgl_checkin DATE,
    tgl_checkout DATE,
    id_paket INT,
    status_penitipan VARCHAR(50),
    FOREIGN KEY (id_user) REFERENCES User(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_pet) REFERENCES Pet(id_pet) ON DELETE CASCADE,
    FOREIGN KEY (id_paket) REFERENCES Paket_Kamar(id_paket) ON DELETE SET NULL
)
```

**New Penitipan Table**:
```sql
CREATE TABLE IF NOT EXISTS Penitipan (
    id_penitipan INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_pet INT,
    tgl_checkin DATE,
    tgl_checkout DATE,
    nama_paket VARCHAR(100),          -- Changed from id_paket INT
    layanan JSON,                     -- Added
    durasi INT,                       -- Added
    total_biaya INT,                  -- Added
    status_penitipan VARCHAR(50),
    FOREIGN KEY (id_user) REFERENCES User(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_pet) REFERENCES Pet(id_pet) ON DELETE CASCADE
)
```

**Changes**:
- Replaced `id_paket INT` with `nama_paket VARCHAR(100)` to store package names directly
- Added `layanan JSON` to store array of services
- Added `durasi INT` to store reservation duration
- Added `total_biaya INT` to store total cost
- Removed foreign key constraint to `Paket_Kamar` table (no longer needed)

### API Changes (`src/api/penitipan/post_tambah_penitipan.php`)

#### New Function: `registerOrGetPet()`
Handles pet registration independently to prevent duplicates:
- Checks if pet with same name exists for current user
- Returns existing pet ID if found (with `is_existing: true`)
- Creates new pet if doesn't exist (with `is_existing: false`)
- Uses same validation as the main pet registration endpoint
- Returns: `{ status: 200|201, success: true, pet_id, is_existing, message }`

#### Updated Function: `handleTambahPenitipan()`
Enhanced to support independent pet handling:
- **Accepts two ways to handle pets**:
  1. `id_pet`: Use existing pet ID from frontend
  2. `pet_data`: Object containing pet details (triggers `registerOrGetPet()`)
- Validates penitipan input
- Calls `registerOrGetPet()` if `pet_data` is provided
- Inserts penitipan with all new fields: `nama_paket`, `layanan`, `durasi`, `total_biaya`
- Corrected parameter types in `bind_param()`: `"iissssiis"` (int, int, str, str, str, str, int, int, str)

---

## Frontend Integration Guide

### Two Ways to Handle Penitipan Creation

#### Option 1: Use Existing Pet
```json
POST /api/penitipan/tambah
{
  "id_pet": 5,
  "tgl_checkin": "2025-12-10",
  "tgl_checkout": "2025-12-15",
  "kamar": "reguler",
  "layanan": ["grooming", "vaccination"],
  "durasi": 5,
  "total_biaya": 250000
}
```

#### Option 2: Register Pet & Create Penitipan in One Request
```json
POST /api/penitipan/tambah
{
  "pet_data": {
    "nama_pet": "Whiskers",
    "jenis_pet": "kucing",
    "ras": "Persia",
    "umur": 3,
    "jenis_kelamin": "betina",
    "warna": "putih",
    "alergi": "",
    "catatan_medis": ""
  },
  "tgl_checkin": "2025-12-10",
  "tgl_checkout": "2025-12-15",
  "kamar": "reguler",
  "layanan": ["grooming", "vaccination"],
  "durasi": 5,
  "total_biaya": 250000
}
```

### Response Examples

**Success (Pet already existed)**:
```json
{
  "status": 201,
  "success": true,
  "message": "Penitipan created successfully",
  "penitipan_id": 12,
  "pet_status": {
    "is_existing": true,
    "message": "Pet already exists"
  }
}
```

**Success (New pet created)**:
```json
{
  "status": 201,
  "success": true,
  "message": "Penitipan created successfully",
  "penitipan_id": 12,
  "pet_status": {
    "is_existing": false,
    "message": "Pet added successfully",
    "pet_id": 8
  }
}
```

---

## Benefits

1. **Fixes TypeError**: `nama_paket` as VARCHAR eliminates the "Incorrect integer value" error
2. **Prevents Duplicates**: `registerOrGetPet()` ensures users don't accidentally register the same pet twice
3. **Better Data Structure**: Stores all reservation details (services, duration, cost) directly in penitipan table
4. **Flexible Frontend**: Frontend can choose to use existing pet or register new pet in same request
5. **Clear Feedback**: API response indicates whether pet was new or existing
