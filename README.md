# TUBES_PWD



spesifikasi aplikasi web **Tempat Penitipan Pet**
## 1. Gambaran Umum Aplikasi

**Nama aplikasi (contoh):** PetCareHub
**Tema:** Sistem pemesanan dan manajemen tempat penitipan hewan (anjing, kucing, dll).

**Tujuan aplikasi:**

* Membantu pemilik hewan (user) mencari, memesan, dan mengelola layanan penitipan hewan.
* Membantu pengelola penitipan (admin) mengelola data pengguna, data hewan, dan transaksi penitipan.

---

## 2. Aktor Sistem

1. **Pengguna (User)**

   * Pemilik hewan yang ingin menitipkan hewan.
   * Bisa registrasi, login, kelola profil, tambah data hewan, buat pemesanan penitipan, lihat riwayat.

2. **Admin**

   * Pengelola tempat penitipan pet.
   * Mengelola data user, data hewan, data transaksi, dan melihat laporan sederhana.

---

## 3. Fitur Utama (Sesuai Ketentuan)

### 3.1 Backend (PHP murni, tanpa framework)

* Menggunakan PHP prosedural atau OOP *tanpa* framework (Laravel, CodeIgniter, dsb).
* Memproses:

  * Registrasi & login.
  * Enkripsi password saat registrasi (misal pakai `password_hash()`).
  * CRUD data pengguna, hewan, dan transaksi penitipan.
  * Upload & update foto profil.
  * API sederhana untuk cek ketersediaan username/email (AJAX).

---

### 3.2 Frontend (HTML, CSS, JavaScript)

* **HTML** untuk struktur halaman (form, tabel, layout).
* **CSS** (boleh native atau pakai sedikit framework seperti Bootstrap, kalau diizinkan dosen).
* **JavaScript:**

  * Validasi form (client-side).
  * AJAX untuk cek username/email sudah dipakai atau belum (bonus).
  * Geolocation (akses lokasi user dan menampilkan jarak ke tempat penitipan).

---

### 3.3 Fitur Registrasi Pengguna Baru

**Halaman: `register.php`**
Field (contoh):

* Nama lengkap
* Username
* Email
* Password
* Konfirmasi password
* Nomor telepon
* Alamat

**Flow:**

1. User mengisi form.
2. JS melakukan:

   * Validasi form (tidak boleh kosong, format email, panjang password, dsb).
   * AJAX ke `check_user.php` untuk cek apakah username/email sudah terdaftar (bonus #3).
3. Jika lolos validasi:

   * PHP menerima data.
   * Password dienkripsi dengan `password_hash()`.
   * Data disimpan ke tabel `users`.

---

### 3.4 Enkripsi Password

* Di backend (PHP), saat registrasi:

  * `password_hash($password, PASSWORD_DEFAULT)`.
* Saat login:

  * `password_verify($password_input, $password_db)`.

Ketentuan #4 terpenuhi.

---

### 3.5 Fitur Login

**Halaman: `login.php`**
Field:

* Username/email
* Password

**Flow:**

1. User mengisi username/email & password.
2. PHP mencari user di tabel `users`.
3. Gunakan `password_verify`.
4. Jika cocok:

   * Simpan data user ke `$_SESSION`.
   * Redirect ke dashboard user.
5. Jika salah:

   * Tampilkan pesan error.

Ketentuan #5 terpenuhi.

---

### 3.6 Fitur Lihat & Update Profil Pengguna

**Halaman: `profile.php`** (user sudah login)

User bisa:

* Melihat data profil: nama, email, username, telepon, alamat, foto profil.
* Mengedit data: nama, telepon, alamat.
* Mengganti password (opsional).
* Upload/update foto profil (bonus #4).

**Flow update profil:**

1. User mengubah data lalu submit.
2. PHP memproses dan melakukan `UPDATE` pada tabel `users`.
3. Jika upload foto:

   * Validasi ekstensi & ukuran file.
   * Simpan ke folder `uploads/` dan update field `photo` di tabel `users`.

Ketentuan #6 + Bonus #4 terpenuhi.

---

## 4. Fitur CRUD (Transaksi) – Minimal 2 Operasi CRUD

Di sini kita buat beberapa entitas:

1. **Data Hewan (Pets)** – CRUD oleh user.
2. **Data Transaksi Penitipan (Bookings)** – CRUD oleh user (sebagian) dan admin.

### 4.1 CRUD Data Hewan (Pets)

**Halaman untuk User:**

* `pets.php` (list hewan milik user)
* `pet_add.php` (tambah hewan)
* `pet_edit.php` (edit hewan)
* `pet_delete.php` (hapus hewan)

Field data hewan:

* Nama hewan
* Jenis (anjing/kucing/dll)
* Ras
* Umur
* Catatan khusus (alergi, obat, dsb)

Operasi CRUD:

* **Create:** user menambahkan hewan baru.
* **Read:** user melihat daftar hewan miliknya.
* **Update:** user mengedit data hewannya.
* **Delete:** user menghapus data hewannya.

---

### 4.2 CRUD Data Transaksi Penitipan (Bookings)

**Halaman untuk User:**

* `booking_add.php` (form pemesanan penitipan)
* `bookings.php` (riwayat pemesanan)

Field:

* User ID
* Pet ID
* Tanggal mulai penitipan
* Tanggal selesai penitipan
* Paket/jenis layanan (harian, mingguan, grooming + penitipan, dsb)
* Status (menunggu konfirmasi, diterima, selesai, dibatalkan)
* Total biaya

**Operasi CRUD:**

* **Create:** user membuat permintaan penitipan.
* **Read:** user melihat daftar pemesanan & status.
* **Update:**

  * User boleh mengubah data pemesanan selama status masih “menunggu konfirmasi”.
  * Admin bisa mengubah status (misalnya menjadi “diterima” atau “selesai”).
* **Delete:**

  * User bisa membatalkan pemesanan (hapus atau ubah status menjadi “dibatalkan”).
  * Admin bisa menghapus pemesanan tertentu.

Dengan ini, *minimal 2 operasi CRUD* sudah terpenuhi (Pets & Bookings).

---

## 5. Bonus: Backend Admin dengan UI

**Halaman Admin (contoh):**

* `admin_login.php`
* `admin_dashboard.php`
* `admin_users.php` (list user + detail)
* `admin_pets.php` (list seluruh hewan)
* `admin_bookings.php` (kelola pemesanan)
* `admin_reports.php` (laporan sederhana, misal jumlah booking per bulan)

**Fitur admin:**

* Melihat semua user & detail (Read).
* Mengedit status booking (Update).
* Menghapus user atau booking tertentu (Delete).
* Menambahkan paket layanan penitipan (CRUD tabel `services` jika mau ditambah).

Ini memenuhi bonus #1.

---

## 6. Bonus: Fitur Geolocation

**Tujuan geolocation:**
Menampilkan lokasi user saat ini dan menunjukkan jarak ke tempat penitipan pet (yang lokasinya fixed, misal di kampus).

**Implementasi:**

* Di halaman `find_us.php` atau di `dashboard.php`:

  * Gunakan `navigator.geolocation.getCurrentPosition()` di JavaScript untuk mendapat koordinat user.
  * Tempat penitipan punya koordinat tetap (disimpan di JS atau di database).
  * Hitung atau tampilkan jarak dengan:

    * API map (opsional, atau hanya hitung & tampilkan jarak numerik).
  * Tampilkan map sederhana menggunakan embed Google Maps (iframe) berdasarkan koordinat tempat penitipan.

Ini memenuhi bonus #2 (geolocation relevan dengan “mencari lokasi tempat penitipan”).

---

## 7. Bonus: Deteksi Username/Email Saat Registrasi

**Flow:**

* Pada `register.php`, ketika user mengetik username/email dan pindah field:

  * JS mengirim AJAX ke `check_user.php`
  * `check_user.php` mengecek ke database:

    * Jika sudah ada -> kirim response `{status: "taken"}`.
    * Jika belum -> kirim response `{status: "available"}`.
  * JS menampilkan pesan di bawah input.

Ini memenuhi bonus #3.

---

## 8. Struktur Database (Contoh)

### 8.1 Tabel `users`

* `id` (INT, PK, AUTO_INCREMENT)
* `name` (VARCHAR)
* `username` (VARCHAR, UNIQUE)
* `email` (VARCHAR, UNIQUE)
* `password` (VARCHAR) – disimpan hash
* `phone` (VARCHAR)
* `address` (TEXT)
* `photo` (VARCHAR, nullable) – path foto profil
* `role` (ENUM: 'user','admin')
* `created_at` (DATETIME)

### 8.2 Tabel `pets`

* `id` (INT, PK, AUTO_INCREMENT)
* `user_id` (INT, FK -> users.id)
* `name` (VARCHAR)
* `type` (VARCHAR) – anjing/kucing/dll
* `breed` (VARCHAR)
* `age` (INT)
* `notes` (TEXT, nullable)

### 8.3 Tabel `bookings`

* `id` (INT, PK, AUTO_INCREMENT)
* `user_id` (INT, FK -> users.id)
* `pet_id` (INT, FK -> pets.id)
* `start_date` (DATE)
* `end_date` (DATE)
* `service_type` (VARCHAR)
* `total_price` (INT)
* `status` (ENUM: 'pending','approved','completed','cancelled')
* `created_at` (DATETIME)

*(Opsional tambahan)*

### 8.4 Tabel `services` (opsional)

* `id`
* `name` (misal: “Penitipan Harian”)
* `description`
* `price_per_day`

---

## 9. Struktur Folder Project (Contoh)

```text
/penitipan-pet
  /assets
    /css
    /js
    /images
    /uploads        -> untuk foto profil & foto pet (kalau mau)
  /config
    db.php          -> koneksi database
  /includes
    header.php
    footer.php
    navbar.php
    auth.php        -> pengecekan sesi login
  index.php         -> landing page
  login.php
  register.php
  logout.php
  profile.php
  pets.php
  pet_add.php
  pet_edit.php
  bookings.php
  booking_add.php
  check_user.php    -> AJAX cek username/email
  find_us.php       -> halaman geolocation
  /admin
    admin_login.php
    admin_dashboard.php
    admin_users.php
    admin_pets.php
    admin_bookings.php
```

---

## 10. Pemetaan ke Ketentuan Tugas

1. **Backend PHP murni:**

   * Semua file `.php` tanpa framework.

2. **Frontend HTML, CSS, JS:**

   * Halaman utama, form, tabel, dsb.

3. **Registrasi pengguna baru:**

   * `register.php`.

4. **Enkripsi password:**

   * `password_hash` & `password_verify` di backend.

5. **Login setelah registrasi:**

   * `login.php`, `logout.php`, session.

6. **Lihat & update profil:**

   * `profile.php`, update data user + upload foto.

7. **Minimal 2 operasi CRUD:**

   * CRUD `pets` dan CRUD `bookings` (plus bisa tambah lagi untuk admin & services).

**Bonus yang diambil:**

* UI backend admin (list data dari frontend) ✔
* Geolocation (halaman `find_us.php`) ✔
* Deteksi username/email saat registrasi (AJAX) ✔
* Upload/update foto profil user ✔

---
