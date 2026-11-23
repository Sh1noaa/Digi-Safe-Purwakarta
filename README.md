# Digi-Safe Purwakarta

**Platform Edukasi Keamanan Siber, Simulasi Anti-Penipuan, dan Komunitas Literasi Digital Masyarakat Purwakarta**

> Proyek Uji Kompetensi Keahlian (UKK) Konsentrasi Keahlian Rekayasa Perangkat Lunak (RPL) — Tahun Pelajaran 2026/2027  
> Pengembang: **Razief Afkar Rusli** (Kelas XII RPL, SMK Negeri 2 Purwakarta)

---

## 1. Deskripsi Proyek

**Digi-Safe Purwakarta** adalah aplikasi web full-stack berbasis Laravel 12 yang dirancang untuk membantu masyarakat, pelajar, dan keluarga di Kabupaten Purwakarta mengenali dan mencegah penipuan digital (*social engineering*, kiriman file `.APK` palsu, tautan phishing, dan peretasan akun).

Aplikasi ini menggabungkan:
- **Edukasi Literasi Siber** (artikel & video terkurasi dengan kontrol ukuran teks & mode ramah baca).
- **Arena Simulasi & Minigame Interaktif** (*Phishing Hunter*, *Cyber Defender*, *Link or Trap?*, *Password Strength Checker*, **Simulasi Chat WhatsApp & File `.APK` 15 Kontak Multi-Ending**, serta **Pemindai Tautan & QR Code Phishing**).
- **Tantangan Harian (*Daily Streak*)** tingkat lanjut (opsi A-E) dengan mekanisme *Freeze 24 Jam* dan *2x Recovery*.
- **Papan Peringkat (*Leaderboard*) Otomatis** dengan 5 kategori nyata (Minigame Benar, Streak Terbanyak, Akurasi Kebenaran WA, Penjawab Forum, Lencana Terbanyak).
- **Forum Komunitas, Voting Realtime, Pertemanan & Live Chat** antar warga.
- **Panel Kendali Admin & Owner** lengkap dengan CRUD (Modul Edukasi, Soal & Level Minigame, Soal Daily Streak, Moderasi Forum & Testimoni, Manajemen Hak Akses User), **Analitik Tren Interaktif**, dan **Ekspor Laporan Excel (`.xlsx`)**.

---

## 2. Tech Stack & Lingkungan Pengembangan

| Komponen | Teknologi |
|---|---|
| **Backend Framework** | Laravel 12 (PHP 8.2+) |
| **Database Server** | MySQL / MariaDB (Engine `InnoDB`, Charset `utf8mb4`, 30 tabel, 13 Foreign Keys) |
| **Frontend Styling** | Tailwind CSS v4 + Vite 7 |
| **Interaktivitas UI** | Alpine.js + Vanilla JavaScript |
| **PWA Support** | Web App Manifest (`manifest.webmanifest`) + Service Worker (`sw.js`) + Offline Fallback |
| **Otentikasi & Keamanan** | Laravel Session Auth, Bcrypt Hashing, Middleware Multi-Role (`auth`, `admin`), CSRF Token, Rate Limiter, OTP Gmail via HTTPS Webhook, Security Headers Middleware |
| **Pengujian Otomatis** | PHPUnit 11 (54 Feature & Unit Tests, 653 Assertions) |
| **Version Control** | Git & GitHub (`https://github.com/Sh1noaa/Digi-Safe-Purwakarta`) |

---

## 3. Struktur Folder MVC

```text
Digi-Safe-Purwakarta/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/               # CRUD Admin (Edukasi, Minigame, Streak, Forum, User, Report XLSX)
│   │   │   ├── Api/                 # REST API untuk sinkronisasi aplikasi mobile
│   │   │   ├── AuthController.php   # Login, Register, OAuth Google, Brute-Force Lockout
│   │   │   ├── AccountSecurityController.php  # Reset Password & Hapus Akun via OTP Gmail
│   │   │   ├── ForumController.php  # CRUD Forum, Jawaban, dan Voting Realtime
│   │   │   ├── LeaderboardController.php
│   │   │   ├── LinkScannerController.php      # Pemindai Tautan & QR Phishing
│   │   │   ├── MinigameController.php         # Arena kuis & penyimpanan skor tervalidasi
│   │   │   ├── ProfileController.php          # Profil, Banner Solid/Gradient, Border, Privasi
│   │   │   └── WaSimulatorController.php      # Simulasi Chat WhatsApp & .APK
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php  # Proteksi rute admin & owner (403 Forbidden bagi user biasa)
│   │       ├── SecurityHeaders.php  # CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy
│   │       └── SetUserPreferences.php # Sinkronisasi tema (Light/Dark) & bahasa (ID/EN)
│   ├── Models/                      # Eloquent ORM (User, Forum, JawabanForum, ModulEdukasi, SkorGame, dll.)
│   └── Services/                    # Business Logic Layer (LeaderboardService, DailyStreakService,
│                                    # PhishingLinkScannerService, WaScamSimulatorService, AccountOtpService,
│                                    # AdminAnalyticsService, SecurityActivityService)
├── database/
│   ├── digi_safe_purwakarta_ukk.sql # File Database SQL Lengkap (30 tabel, 13 FK, data realistis)
│   └── migrations/                  # Skema migrasi Laravel
├── public/                          # Aset publik, gambar, PWA manifest & service worker
├── resources/
│   ├── css/app.css                  # Styling Tailwind v4 + Dark Mode WCAG AA
│   ├── js/app.js                    # Entrypoint Vite + Alpine.js
│   └── views/                       # Blade Templates (beranda, edukasi, minigame, forum, profile, admin)
├── routes/
│   ├── web.php                      # Rute web lengkap dengan middleware auth, admin, dan throttle
│   └── api.php                      # Rute REST API
├── tests/Feature/                   # 54 Automated Feature Tests (keamanan, CRUD, OTP, leaderboard, dll.)
├── DOKUMEN_PERANCANGAN_ERD_USECASE_FLOWCHART.md
├── DOKUMEN_QA_DAN_TEST_CASE_UKK.md
└── CONTEKAN_LULUS_SEMPURNA_UKK_45_MENIT.md
```

---

## 4. Langkah Instalasi Lokal

### Prasyarat
- PHP >= 8.2 (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`)
- Composer >= 2.x
- Node.js >= 18.x & NPM
- MySQL / MariaDB (XAMPP atau Laragon)

### Tahap Instalasi
1. **Clone repository dan masuk ke direktori proyek:**
   ```bash
   git clone https://github.com/Sh1noaa/Digi-Safe-Purwakarta.git
   cd Digi-Safe-Purwakarta
   ```
2. **Instal dependensi backend dan frontend:**
   ```bash
   composer install
   npm install
   npm run build
   ```
3. **Salin dan sesuaikan file konfigurasi `.env`:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan konfigurasi database pada `.env`:
   ```env
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=digi_safe_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. **Buat database dan impor file SQL UKK:**
   - Buat database baru bernama `digi_safe_db` di phpMyAdmin / MySQL CLI.
   - Impor file **[`database/digi_safe_purwakarta_ukk.sql`](database/digi_safe_purwakarta_ukk.sql)** (berisi 30 tabel InnoDB, 13 Foreign Key aktif, dan data uji realistis).
   - Alternatif via CLI MySQL:
     ```bash
     mysql -u root -e "CREATE DATABASE IF NOT EXISTS digi_safe_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
     mysql -u root digi_safe_db < database/digi_safe_purwakarta_ukk.sql
     ```
5. **Hubungkan storage publik dan jalankan server lokal:**
   ```bash
   php artisan storage:link
   php artisan serve
   ```
   Buka browser di **`http://127.0.0.1:8000`**.

---

## 5. Daftar Akun Uji Coba (UKK)

Seluruh kata sandi disimpan dalam bentuk hash **Bcrypt (`$2y$`)** di tabel `users` (tidak ada satupun password *plain text*).

| Role | Email Login | Username | Password | Hak Akses Utama |
|---|---|---|---|---|
| **Admin** | `ukk.admin@digisafe.id` | `admin_ukk` | `AdminUKK2026!` | Akses penuh `/admin/*`, CRUD Edukasi, Soal Minigame, Soal Streak, Moderasi Forum/Testimoni, Analitik, dan Unduh Laporan `.xlsx`. |
| **User (Warga)** | `ukk.warga@gmail.com` | `warga_ukk` | `WargaUKK2026!` | Akses Beranda, Edukasi, Minigame, Simulasi WA, Link/QR Scanner, Daily Streak, Forum, Live Chat, dan Profil (diblokir `403` saat mengakses `/admin/*`). |

> Tersedia juga pendaftaran akun baru melalui halaman `/register` (memvalidasi email non-disposable, kekuatan password, dan memblokir username sensitif/terlarang).

---

## 6. Implementasi Keamanan Aplikasi Dasar

1. **Enkripsi Kata Sandi**: Menggunakan `Hash::make()` (Bcrypt) pada seluruh registrasi, OAuth, dan reset password.
2. **Proteksi Multi-Role & Session**: Rute privat dilindungi middleware `auth` dan rute panel kendali dilindungi `AdminMiddleware` (`admin` / `owner`), disertai regenerasi session saat login (`$request->session()->regenerate()`) dan pencabutan sesi saat ganti/reset password.
3. **Pencegahan SQL Injection**: Seluruh query database menggunakan Eloquent ORM dan *Parameterized Queries* (PDO prepared statements).
4. **Pencegahan XSS (Cross-Site Scripting)**: Template Blade menggunakan sintaks auto-escaping `{{ ... }}` untuk seluruh input pengguna serta header HTTP `Content-Security-Policy` dan `X-Content-Type-Options: nosniff`.
5. **Proteksi CSRF & Brute Force**: Setiap form `POST`/`PUT`/`DELETE` dilindungi `@csrf` token serta pembatasan *rate limiting* (`throttle` & `RateLimiter` per akun).
6. **Validasi Ganda**: Validasi HTML5/Alpine.js di sisi klien dan `$request->validate(...)` di sisi server, termasuk perhitungan skor minigame di sisi server agar tidak bisa dimanipulasi lewat DevTools.

---

## 7. Menjalankan Automated Test Suite

Proyek dilengkapi **54 pengujian otomatis (653 assertions)** menggunakan PHPUnit:
```bash
php artisan test
```

---

## 8. Artefak Dokumentasi UKK Terkait

- **File Database SQL**: [`database/digi_safe_purwakarta_ukk.sql`](database/digi_safe_purwakarta_ukk.sql)
- **Dokumen Perancangan (ERD, Use Case, Flowchart & Query JOIN)**: [`DOKUMEN_PERANCANGAN_ERD_USECASE_FLOWCHART.md`](DOKUMEN_PERANCANGAN_ERD_USECASE_FLOWCHART.md)
- **Dokumen QA & Test Case (Positive + Negative Testing)**: [`DOKUMEN_QA_DAN_TEST_CASE_UKK.md`](DOKUMEN_QA_DAN_TEST_CASE_UKK.md)
- **Panduan Arsitektur & Bedah Kode**: [`PANDUAN_ARSITEKTUR_DAN_BEDAH_KODE_UKK.md`](PANDUAN_ARSITEKTUR_DAN_BEDAH_KODE_UKK.md)
