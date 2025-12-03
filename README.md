
# PawHaven - Pet Boarding Management System
[Detail Tugas Besar - PWD F - Bu Jo](tugas.md)

Sebuah sistem manajemen penitipan hewan berbasis web yang komprehensif, dibangun menggunakan PHP, MySQL, dan JavaScript murni.


## Authors

### Front End Development Team
- Silvanus Febrianesha Widyatama (240712992)
- Rhexsen Thenzie (240712805)

### Back End Development Team
- Farelino Alexander Kim (240713000)

### Research and Testing Team
- Hieronimus Ressa (240712814)
- Dylan Arya Immanuel Suhadi (240712776)







## Overview
- Purpose: Manage pet boarding operations end-to-end — user accounts, pet data, booking (penitipan), packages/services, and admin dashboards — with a clean UX and simple deployment.
- Architecture: PHP backend with custom routing and MySQL storage; static XHTML pages with modular JS for views; lightweight APIs returning JSON.
- Key Features: User auth (login/register), profile management with optional photo, pet records, booking form with cost calculation, history, and admin management panels.
- Performance: Splits heavy payloads (e.g., profile photos) into dedicated endpoints for faster page loads; client-side session checks and redirects.
- Deployment: Single `index.php` entry

___

## Codespace Development instruction

### Instruksi Pengembangan di Codespace / Devcontainer

Ikuti langkah-langkah berikut untuk mulai mengembangkan proyek ini di lingkungan Codespace atau Devcontainer:

#### 1. Menggunakan GitHub Codespaces (Recomended)
1. Buka repository ini di GitHub.
2. Klik tombol **Code** lalu pilih **Create codespace on main**.
3. Tunggu proses build devcontainer selesai.
4. Aplikasi web berjalan di port `80` (otomatis ter-forward).
5. Database MariaDB tersedia di port `3306`.

#### 2. Menggunakan Docker Compose Lokal
1. Pastikan Docker sudah terinstal di komputer Anda.
2. Jalankan perintah berikut di terminal dari folder root repository:

   ```bash
   docker compose -f .devcontainer/docker-compose.yml up --build -d
   ```

3. Buka browser dan akses `http://localhost/` untuk melihat aplikasi.
