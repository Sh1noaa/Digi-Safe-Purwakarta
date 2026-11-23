# Dokumen Perancangan Sistem (ERD, Use Case, Flowchart & Audit Basis Data)
**Proyek:** Digi-Safe Purwakarta  
**Peserta UKK:** Razief Afkar Rusli — XII RPL, SMK Negeri 2 Purwakarta  
**Skema Uji:** Junior Web Developer / Full-Stack Web Developer (PT. Pratama Solusi Teknologi)

---

## 1. Entity Relationship Diagram (ERD)

Database `digi_safe_db` menggunakan engine **InnoDB** (`utf8mb4_unicode_ci`) dengan **30 tabel** dan **13 Foreign Key constraint aktif** yang memisahkan tabel master (`users`, `modul_edukasi`, `badges`, `minigame_levels`, `soal_minigame`, `daily_streak_questions`) dan tabel transaksi (`skor_game`, `forum`, `jawaban_forum`, `progres_baca`, `testimoni`, `user_badges`, `user_daily_streaks`, `log_aktivitas`, dll.).

```mermaid
erDiagram
    USERS ||--o{ MODUL_EDUKASI : "created_by (FK)"
    USERS ||--o{ PROGRES_BACA : "user_id (FK)"
    MODUL_EDUKASI ||--o{ PROGRES_BACA : "modul_id (FK)"
    USERS ||--o{ SKOR_GAME : "user_id (FK)"
    USERS ||--o{ FORUM : "user_id (FK)"
    FORUM ||--o{ JAWABAN_FORUM : "forum_id (FK)"
    USERS ||--o{ JAWABAN_FORUM : "user_id (FK)"
    USERS ||--o{ TESTIMONI : "user_id (FK)"
    TESTIMONI ||--o{ TESTIMONI_REACTIONS : "testimoni_id (FK)"
    USERS ||--o{ TESTIMONI_REACTIONS : "user_id (FK)"
    USERS ||--o{ USER_BADGES : "user_id (FK)"
    BADGES ||--o{ USER_BADGES : "badge_id (FK)"
    USERS ||--o{ LOG_AKTIVITAS : "user_id (FK)"
    USERS ||--o| USER_DAILY_STREAKS : "user_id"
    USERS ||--o{ USER_DAILY_STREAK_LOGS : "user_id"
    USERS ||--o{ SECURITY_ACTIVITY_LOGS : "user_id"

    USERS {
        int id PK
        string username UK
        string email UK
        string password "Bcrypt Hash ($2y$)"
        string role "owner | admin | user"
        string nama_lengkap
        string specialty_role
        string avatar
        string banner
        string avatar_border
        string locale "id | en"
        string theme "light | dark"
        json privacy_settings
        timestamp created_at
    }

    MODUL_EDUKASI {
        int id PK
        string judul
        string kategori
        string type "teks | link | video"
        text konten
        string link_url
        int created_by FK
        timestamp created_at
    }

    PROGRES_BACA {
        int id PK
        int user_id FK
        int modul_id FK
        timestamp dibaca_pada
    }

    SKOR_GAME {
        int id PK
        int user_id FK
        string game_type "phishing | cyber-defender | link-trap | wa-simulator"
        int skor
        int skor_tertinggi
        int benar
        int salah
        timestamp played_at
    }

    FORUM {
        int id PK
        int user_id FK
        string judul
        string kategori
        text pertanyaan
        string status "terbuka | terjawab"
        boolean is_starred
        int views
        timestamp created_at
    }

    JAWABAN_FORUM {
        int id PK
        int forum_id FK
        int user_id FK
        text jawaban
        boolean is_admin
        timestamp created_at
    }

    TESTIMONI {
        int id PK
        int user_id FK
        string judul
        text cerita
        tinyint rating "1-5"
        string status_approval
        timestamp created_at
    }

    BADGES {
        int id PK
        string nama_badge
        text deskripsi
        string icon
    }

    USER_BADGES {
        int id PK
        int user_id FK
        int badge_id FK
        timestamp earned_at
    }

    USER_DAILY_STREAKS {
        bigint id PK
        bigint user_id UK
        int current_streak
        int longest_streak
        int freeze_recovery_left "max 2x"
        int total_completed
        date last_completed_date
        timestamp last_completed_at
    }
```

### Daftar 13 Foreign Key Aktif di MySQL (`digi_safe_db`)
1. `forum.user_id` -> `users.id` (`forum_ibfk_1`)
2. `jawaban_forum.forum_id` -> `forum.id` (`jawaban_forum_ibfk_1`)
3. `jawaban_forum.user_id` -> `users.id` (`jawaban_forum_ibfk_2`)
4. `log_aktivitas.user_id` -> `users.id` (`log_aktivitas_ibfk_1`)
5. `modul_edukasi.created_by` -> `users.id` (`modul_edukasi_ibfk_1`)
6. `progres_baca.user_id` -> `users.id` (`progres_baca_ibfk_1`)
7. `progres_baca.modul_id` -> `modul_edukasi.id` (`progres_baca_ibfk_2`)
8. `skor_game.user_id` -> `users.id` (`skor_game_ibfk_1`)
9. `testimoni.user_id` -> `users.id` (`testimoni_ibfk_1`)
10. `testimoni_reactions.testimoni_id` -> `testimoni.id` (`testimoni_reactions_ibfk_1`)
11. `testimoni_reactions.user_id` -> `users.id` (`testimoni_reactions_ibfk_2`)
12. `user_badges.user_id` -> `users.id` (`user_badges_ibfk_1`)
13. `user_badges.badge_id` -> `badges.id` (`user_badges_ibfk_2`)

---

## 2. Use Case Diagram (Hak Akses Multi-Role)

```mermaid
flowchart LR
    Guest([Pengunjung / Guest])
    User([Warga Terdaftar / User])
    Admin([Administrator / Admin])
    Owner([Pemilik Sistem / Owner])

    subgraph Publik [Area Publik]
        UC1[Baca Modul Edukasi & Mode Ramah Baca]
        UC2[Cek Password & Pemindai Link/QR Phishing]
        UC3[Simulasi Chat WA & Lihat Leaderboard]
        UC4[Registrasi & Login / Reset Password OTP]
    end

    subgraph Anggota [Area Terautentikasi - Middleware: auth]
        UC5[Main Arena Kuis Minigame & Simpan Skor]
        UC6[Tantangan Daily Streak A-E & Pemulihan Freeze]
        UC7[Posting Forum, Balas, Voting & Testimoni]
        UC8[Kelola Profil, Banner Solid/Gradient, Border & Hapus Akun OTP]
        UC9[Pertemanan, Follow & Live Chat]
    end

    subgraph Pengelola [Area Panel Kendali - Middleware: auth + admin]
        UC10[CRUD Modul Edukasi & Evaluasi Request Topik]
        UC11[CRUD Soal Minigame, Level & Soal Daily Streak]
        UC12[Moderasi Forum & Pin Diskusi]
        UC13[Lihat Analitik Tren & Unduh Laporan Excel XLSX]
        UC14[Kelola Role User & Promote/Demote Admin]
    end

    Guest --> UC1
    Guest --> UC2
    Guest --> UC3
    Guest --> UC4

    User --> UC1
    User --> UC2
    User --> UC3
    User --> UC5
    User --> UC6
    User --> UC7
    User --> UC8
    User --> UC9

    Admin --> UC10
    Admin --> UC11
    Admin --> UC12
    Admin --> UC13

    Owner --> UC10
    Owner --> UC11
    Owner --> UC12
    Owner --> UC13
    Owner --> UC14
```

---

## 3. Flowchart Alur Proses Bisnis Utama

### A. Alur Autentikasi, Proteksi Rute & Brute-Force Lockout
```mermaid
flowchart TD
    Start([User Akses Halaman]) --> CheckAuth{Rute Membutuhkan Login?}
    CheckAuth -- Tidak --> PublicView[Tampilkan Halaman Publik]
    CheckAuth -- Ya --> IsLoggedIn{Sudah Login?}
    IsLoggedIn -- Tidak --> RedirectLogin[Redirect ke /login]
    RedirectLogin --> InputCred[Input Email & Password]
    InputCred --> ValidateClient[Validasi Client HTML5/JS]
    ValidateClient --> RateLimit{Percobaan Gagal >= 5x?}
    RateLimit -- Ya --> Lockout[Tolak + Pesan Tunggu Lockout]
    RateLimit -- Tidak --> VerifyHash{Cocokkan Hash Bcrypt?}
    VerifyHash -- Salah --> IncrementFail[Catat Percobaan Gagal]
    IncrementFail --> RedirectLogin
    VerifyHash -- Benar --> RegenSession[Regenerasi Session & Catat Security Log]
    RegenSession --> CheckRole{Akses Rute /admin/*?}
    IsLoggedIn -- Ya --> CheckRole
    CheckRole -- Tidak --> UserDashboard[Akses Fitur Member]
    CheckRole -- Ya --> IsAdmin{Role admin atau owner?}
    IsAdmin -- Tidak --> Error403[HTTP 403 Forbidden]
    IsAdmin -- Ya --> AdminPanel[Masuk Panel Kendali Admin]
```

### B. Alur Transaksi Minigame, Perhitungan Skor Server-Side & Leaderboard
```mermaid
flowchart TD
    MStart([User Selesai Bermain Minigame]) --> SubmitAns[Kirim Jawaban + CSRF Token ke /minigame/save-score]
    SubmitAns --> ServerValidate[Validasi Server: Cek game_type & Kunci Jawaban DB]
    ServerValidate --> CalcScore[Hitung Ulang Jumlah Benar, Salah, Akurasi & Skor di Server]
    CalcScore --> AntiTamper[Tolak Skor Manipulasi Klien]
    AntiTamper --> SaveDB[(Simpan ke Tabel skor_game)]
    SaveDB --> CheckBorder[Evaluasi Pembukaan Badge & Avatar Border]
    CheckBorder --> UpdateBoard[Rekap Otomatis di LeaderboardService 5 Kategori]
    UpdateBoard --> ShowResult[Tampilkan Skor, Akurasi & Peringkat Baru]
```

### C. Alur Transaksi Admin CRUD & Cetak Laporan Excel (`.xlsx`)
```mermaid
flowchart TD
    AStart([Admin Login ke /admin]) --> Action{Pilih Aksi}
    Action -- CRUD --> CRUDForm[Tambah / Edit / Hapus Edukasi, Minigame, atau Soal Streak]
    CRUDForm --> ValidateSrv[Validasi Server-Side + Sanitasi Input]
    ValidateSrv -- Tidak Valid --> BackError[Kembali dengan Pesan Error Spesifik]
    ValidateSrv -- Valid --> Persist[(Simpan / Update / Hapus di MySQL)]
    Persist --> FlashOK[Redirect + Pesan Sukses]
    Action -- Laporan --> ExportReq[Klik Download Laporan XLSX /admin/report/xlsx]
    ExportReq --> Aggregate[Query Agregasi Users, Skor Game, Forum, Edukasi & Testimoni]
    Aggregate --> BuildXLSX[Generate File Spreadsheet OOXML .xlsx]
    BuildXLSX --> Download([Browser Mengunduh Laporan Siap Cetak])
```

---

## 4. Contoh Query SQL Relasional (JOIN & Agregasi) untuk Tahap 3 Audit Basis Data

Penguji dapat meminta pembuktian pemahaman query `JOIN` dan fungsi agregasi (`COUNT`, `MAX`, `AVG`, `SUM`, `GROUP BY`). Berikut query nyata yang langsung berjalan pada database `digi_safe_db`:

### 1. Rekap Performa Pemain per User (INNER JOIN + Agregasi `COUNT`, `SUM`, `MAX`, `AVG`)
```sql
SELECT
    u.username,
    u.nama_lengkap,
    COUNT(s.id) AS total_sesi,
    SUM(s.benar) AS total_benar,
    MAX(s.skor_tertinggi) AS rekor_skor,
    ROUND(AVG(s.skor), 1) AS rata_rata_skor
FROM users u
INNER JOIN skor_game s ON s.user_id = u.id
GROUP BY u.id, u.username, u.nama_lengkap
ORDER BY total_benar DESC, rekor_skor DESC
LIMIT 10;
```

### 2. Rekap Diskusi Forum & Jumlah Balasan (LEFT JOIN + `COUNT`)
```sql
SELECT
    f.id,
    f.judul,
    u.nama_lengkap AS penulis,
    f.kategori,
    COUNT(j.id) AS jumlah_balasan
FROM forum f
INNER JOIN users u ON u.id = f.user_id
LEFT JOIN jawaban_forum j ON j.forum_id = f.id
GROUP BY f.id, f.judul, u.nama_lengkap, f.kategori
ORDER BY jumlah_balasan DESC;
```

### 3. Peringkat Streak Harian Terpanjang (INNER JOIN `users` & `user_daily_streaks`)
```sql
SELECT
    u.nama_lengkap,
    u.username,
    ds.current_streak,
    ds.longest_streak,
    ds.total_completed
FROM user_daily_streaks ds
INNER JOIN users u ON u.id = ds.user_id
ORDER BY ds.longest_streak DESC, ds.current_streak DESC;
```
