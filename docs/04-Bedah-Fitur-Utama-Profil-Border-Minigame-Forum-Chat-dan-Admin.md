# Modul 4: Bedah Fitur Utama (Profil, 5 Border Avatar, Minigame, Leaderboard, Forum, Chat, & Panel Admin)

Dokumen ini menjelaskan cara kerja teknis dari seluruh fitur utama di dalam **Digi-Safe Purwakarta** agar Anda siap menjelaskan alur kode maupun database dari fitur mana pun yang ditunjuk penguji.

---

## 1. Halaman Profil, Efek Background Kursor, & Sistem 5 Border Avatar

Lokasi file utama:
- View: [resources/views/profile/show.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/profile/show.blade.php)
- Controller: [app/Http/Controllers/ProfileController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/ProfileController.php)
- Service Border: [app/Services/AvatarBorderService.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Services/AvatarBorderService.php)
- Komponen Avatar Global: [resources/views/components/user-avatar.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/components/user-avatar.blade.php)

### A. Mengapa di Halaman Profil Tidak Ada Footer & Bagaimana Efek Kursor Bekerja?
1. **Menyembunyikan Footer Khusus di Halaman `/profile`**:
   - Di awal [profile/show.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/profile/show.blade.php#L10), disetel variabel `$hideFooter = true;`.
   - Di [layouts/app.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/layouts/app.blade.php#L540), tag `<footer>` dibungkus `@unless (!empty($hideFooter))` sehingga halaman profil tampak bersih seperti aplikasi modern.
2. **Efek Cahaya Background yang Mengikuti Kursor Mouse**:
   - Pada pembungkus utama `.profile-cursor-stage`, event `@mousemove` membaca posisi mouse terhadap halaman dan memperbarui CSS Custom Properties `--px` dan `--py` menggunakan `requestAnimationFrame`:
     ```css
     background:
         radial-gradient(560px circle at var(--px, 50%) var(--py, 30%), rgba(14, 165, 233, 0.22), ...),
         radial-gradient(260px circle at var(--px, 50%) var(--py, 30%), rgba(198, 244, 50, 0.16), ...);
     ```
   - Hasilnya adalah pendaran cahaya lembut yang mengikuti gerakan mouse secara *real-time* tanpa membebani CPU.

### B. Bagaimana Sistem Unlock 5 Border Avatar Bekerja?
Akun baru memiliki `avatar_border = null` (tanpa border). Untuk memakai salah satu dari 5 border di pop-up modal **"Pilih Border Avatar"**, sistem di [AvatarBorderService.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Services/AvatarBorderService.php#L102-L195) mengecek 5 syarat pencapaian nyata dari database:

| Kode Border | Nama Border | File Gambar PNG | Syarat Unlock Otomatis di Server (`AvatarBorderService`) |
| :--- | :--- | :--- | :--- |
| `aqua_crest` | **Aqua Crest** | `public/images/borders/aqua-crest.png` | Menyelesaikan minimal **1 sesi permainan minigame** (`skor_game >= 1`). |
| `astral_orbit` | **Astral Orbit** | `public/images/borders/astral-orbit.png` | Memilih **Specialty Role** di profil DAN berkontribusi minimal **1 diskusi atau balasan forum**. |
| `cosmic_ring` | **Cosmic Ring** | `public/images/borders/cosmic-ring.png` | Mencapai **skor tertinggi minimal 70 poin** pada salah satu arena minigame. |
| `chrono_sovereign` | **Chrono Sovereign** | `public/images/borders/chrono-sovereign.png` | Bermain minimal **3 sesi minigame** DAN memiliki minimal **3 kontribusi komunitas** (forum + balasan + testimoni). |
| `crimson_flare` | **Crimson Flare** | `public/images/borders/crimson-flare.png` | Mencapai **skor tertinggi minimal 90 poin** DAN membuka minimal **2 lencana Achievement Game**. |

- **Validasi Keamanan di Server**: Saat user memilih border di pop-up modal lalu menekan **"Simpan Perubahan"**, `ProfileController::updateBorder` memeriksa `AvatarBorderService::isUnlockedForUser($user, $code)`. Jika belum memenuhi syarat, server menolak dengan status `403 Forbidden`.
- **Tampil Otomatis di Seluruh Halaman**: Komponen `<x-user-avatar :user="$user" />` otomatis menampilkan `<img src="{{ $user->avatar_border_url }}">` dengan ukuran `144%` melingkari foto profil di Beranda, `/pendaftar`, Leaderboard, Forum, Testimoni, Live Chat, dan Panel Admin.

---

## 2. Mengapa Tinggi Kartu Profil Pengguna di Beranda & `/pendaftar` Bisa 100% Sama Rata?

Lokasi file: [resources/views/components/user-profile-card.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/components/user-profile-card.blade.php#L172-L280).

Agar kartu milik user yang punya *Specialty Role* (misal `Phishing Hunter`), user yang belum memilih *role*, maupun akun privat memiliki tinggi vertikal yang sama persis:
1. **Baris Nama (`h-7`)**: Dikunci setinggi `28px` dengan `truncate`.
2. **Slot Specialty Role (`h-6 flex items-center`)**: Dikunci setinggi `24px`. Jika user sudah memilih *role*, tampil badge biru (`Phishing Hunter`); jika belum, tampil badge netral (`Warga Siber` / `Community Member`); jika akun privat, tampil badge `Akun Privat`.
3. **Slot Bio (`h-[38px] leading-[19px] line-clamp-2 overflow-hidden`)**: Dikunci tepat 2 baris (`38px`) baik bio user panjang maupun pendek.
4. **Slot Footer Statistik Bawah (`shrink-0`)**: Tinggi bagian statistik dan baris ikon bawah disamakan antara akun publik dan akun privat (`h-[57px]`).

---

## 3. Sistem Minigame, Password Strength Checker, & Leaderboard

Lokasi file:
- [app/Http/Controllers/MinigameController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/MinigameController.php)
- [app/Http/Controllers/LeaderboardController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/LeaderboardController.php)
- [app/Services/LeaderboardBuilder.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Services/LeaderboardBuilder.php)
- [resources/views/minigame/password-checker.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/minigame/password-checker.blade.php)

### A. 3 Arena Simulasi Minigame & Perhitungan Lencana (*Achievement Badges*)
- Terdapat 3 arena: **Phishing Hunter**, **Cyber Defender**, dan **Link or Trap**, masing-masing memiliki 5 tingkatan level kesulitan dari tabel `minigame_levels`.
- Saat kuis selesai, browser mengirim skor, jumlah benar, jumlah salah, dan sisa waktu ke `POST /minigame/save-score`.
- Server menyimpan ke tabel `skor_game` dan otomatis mengevaluasi syarat pembukaan lencana (*Achievement Badges*) di `User::awardBadges()`:
  - **First Win**: Menyelesaikan minigame pertama kali.
  - **Cyber Novice / Expert / Master**: Berdasarkan pencapaian skor tertinggi (`>= 60`, `>= 80`, `>= 100`).
  - **100% Accuracy**: Hanya diberikan jika `$validated['salah'] === 0 && $validated['benar'] > 0` (diverifikasi langsung di server).

### B. Password Strength Checker (100% Diproses Lokal di Browser)
- Berada di `/minigame/password-checker` ([password-checker.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/minigame/password-checker.blade.php)).
- Input kata sandi dimulai dalam keadaan kosong (`password: ''`) agar user mengetik sandinya sendiri.
- **Privasi Aman**: Kata sandi yang diuji dihitung 100% menggunakan Alpine.js di browser pengguna (memeriksa panjang $\ge 12$ karakter, huruf besar, huruf kecil, angka, simbol, pola berulang, serta membuat 4 variasi rekomendasi sandi kuat berbasis kata yang diketik user) tanpa pernah dikirim ke server!

### C. Halaman Leaderboard (`/leaderboard`)
- Menampilkan 4 kategori klasemen (*Jawaban Benar*, *Jawaban Salah*, *Kontribusi Forum*, *Lencana*) yang disusun oleh `LeaderboardBuilder::build()`.
- Di bawah papan klasemen utama, `LeaderboardController::index()` juga menampilkan data *real-time* dari database:
  1. **Posisi Kamu Saat Ini**: Mencari peringkat terbaik user yang sedang login (`Peringkat #X`) di seluruh kategori klasemen.
  2. **Rekor per Arena Minigame**: Skor tertinggi, rata-rata skor, dan pemegang rekor di masing-masing dari 3 arena.
  3. **Koleksi 5 Border Avatar Prestasi**: Status border mana yang sudah terbuka atau masih terkunci.
  4. **Aktivitas Skor Terbaru**: 6 riwayat permainan minigame terbaru dari warga komunitas.

---

## 4. Sistem Forum Komunitas Real-Time, Voting `Most Helpful`, & Moderasi Pin

Lokasi file:
- [app/Http/Controllers/ForumController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/ForumController.php)
- [resources/views/forum/index.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/forum/index.blade.php)

1. **Sinkronisasi Real-Time Ringan**:
   - Halaman forum menjalankan *polling* ringan setiap 12 detik (`refreshLiveData()`) hanya ketika tab browser sedang aktif (`document.visibilityState === 'visible'`) ke endpoint `/forum/live-data`.
2. **Sistem Voting 1 User = 1 Suara (`jawaban_forum_votes`)**:
   - Setiap user yang login dapat menekan tombol **Membantu (Thumbs Up)** atau **Thumbs Down** pada jawaban diskusi (`POST /forum/jawaban/{jawaban}/vote`).
   - Tabel `jawaban_forum_votes` memiliki *unique constraint* pada `(jawaban_id, user_id)`. Jika user menekan tombol yang sama dua kali, vote dibatalkan (*toggle off*); jika menekan tombol lawan, vote diubah.
3. **Penentuan Otomatis Badge `Most Helpful`**:
   - Di dalam `ForumController::formatForumItem()`, sistem menghitung selisih suara bersih (`net_score = up_count - down_count`) untuk setiap jawaban.
   - Jawaban dengan `up_count >= 1` dan `net_score` tertinggi di dalam diskusi tersebut otomatis diberi label hijau **`Most Helpful`** dan ditempatkan di urutan paling atas!
4. **Edit & Hapus Milik Sendiri + Pin Diskusi oleh Admin**:
   - Pemilik diskusi (`can_manage = true`) atau Admin dapat mengedit judul/kategori/isi diskusi (`PUT /forum/{forum}`) maupun menghapus diskusi/balasan (`DELETE /forum/{forum}`), dengan notifikasi *floating toast* di pojok kanan atas.
   - Admin/Owner memiliki tombol **`Pin Forum`** (`PATCH /admin/forum/{forum}/star`) untuk menyematkan diskusi penting agar selalu berada di baris paling atas.

---

## 5. Perbedaan Follow vs Pertemanan (Friend), Live Chat, & Bot Digika

Lokasi file:
- [app/Http/Controllers/FollowController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/FollowController.php)
- [app/Http/Controllers/SocialChatController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/SocialChatController.php)
- [app/Http/Controllers/Admin/EdukasiController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/Admin/EdukasiController.php#L130-L194)

1. **Follow vs Friend (Pertemanan)**:
   - **Follow (Ikuti)** disimpan di tabel `user_follows`: bersifat **satu arah** (seperti Instagram/X) tanpa perlu persetujuan, berguna untuk mengikuti kontributor favorit.
   - **Friend (Teman)** disimpan di tabel `friendships`: bersifat **dua arah** dengan status `pending` $\rightarrow$ `accepted` untuk membuka percakapan bebas di *Live Chat*.
2. **Aturan Keamanan Anti-Spam di Live Chat**:
   - Jika User A mengirim pesan ke User B yang **belum berteman**, sistem otomatis membuat permintaan pertemanan (`pending`) dan hanya mengizinkan **1 pesan perkenalan pertama**. User A tidak bisa mengirim pesan kedua sebelum User B menekan tombol **Terima (Accept)** di kotak masuk chat!
3. **Bot Digika (Asisten Sistem Otomatis)**:
   - Ketika warga mengajukan usulan tema edukasi baru di halaman `/edukasi`, usulan tersebut masuk ke tab **Request Tema Pengguna** di Panel Admin.
   - Saat Admin mengklik **Setujui** atau **Tolak** (beserta catatan opsional), metode `EdukasiController::applyEvaluationDecision()` memanggil `User::ensureBotDigika()` dan otomatis mengirimkan pesan personal atas nama **Bot Digika** ke inbox *Live Chat* pengguna pengusul!

---

## 6. Panel Admin, Diagram Tren User & Testimoni Bintang 5-1, serta Ekspor Excel (`.xlsx`)

Lokasi file:
- [resources/views/admin/dashboard.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/admin/dashboard.blade.php)
- [app/Services/AdminAnalyticsService.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Services/AdminAnalyticsService.php)
- [app/Http/Controllers/Admin/ReportController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/Admin/ReportController.php)
- [app/Services/SimpleXlsxExporter.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Services/SimpleXlsxExporter.php)

### A. Bagaimana Diagram Pertumbuhan User (Menaik/Menurun) & Rating Testimoni (Bintang 5-1) Bekerja?
1. **Dihitung Real-Time oleh `AdminAnalyticsService::build()`**:
   - Menyediakan 2 mode periode yang bisa diganti dengan klik: **6 Bulan Terakhir (Bulanan)** dan **7 Hari Terakhir (Harian)**.
   - **Diagram Kiri (Tren Pertumbuhan Pendaftar User)**:
     - Mengelompokkan tanggal `created_at` seluruh pengguna (`User::humans()`) ke dalam *bucket* bulanan atau harian.
     - Membandingkan jumlah pendaftar periode berjalan (`current_count`) dengan periode sebelumnya (`previous_count`).
     - Jika `delta > 0`, status otomatis **Tren Menaik (`+X user`)** berwarna hijau; jika `delta < 0`, status **Tren Menurun (`-X user`)** berwarna merah; jika `delta === 0`, status **Tren Stabil**.
     - Menghitung titik koordinat SVG (`x`, `y_new`, `y_cum`, `bar_y`, `bar_h`) di sisi server sehingga grafik batang, kurva area, dan garis akumulasi langsung tampil instan tanpa membebani browser dengan library JavaScript eksternal yang berat.
   - **Diagram Kanan (Distribusi & Tren Rating Testimoni Bintang 5, 4, 3, 2, 1)**:
     - Menghitung jumlah ulasan, persentase proporsi, serta kenaikan/penurunan (`delta`) untuk **masing-masing level Bintang 5, Bintang 4, Bintang 3, Bintang 2, dan Bintang 1**.
     - Menampilkan *banner* kesimpulan otomatis bintang mana yang sedang mengalami kenaikan pada periode ini (contoh: `"Bintang 4 (+2) & Bintang 5 (+1) naik pada bulan ini"`).

### B. Bagaimana Fitur Download Laporan Excel (`.xlsx`) Bekerja Tanpa Library Berat?
- Saat Admin menekan tombol **Download Laporan XLSX** (`GET /admin/report/xlsx`), `ReportController::downloadXlsx()` mengumpulkan seluruh statistik platform dan memanggil `SimpleXlsxExporter`.
- Karena format file `.xlsx` modern (Office OpenXML) sebenarnya adalah arsip ZIP yang berisi file-file XML terstruktur (`[Content_Types].xml`, `xl/workbook.xml`, `xl/styles.xml`, `xl/worksheets/sheet1..4.xml`), `SimpleXlsxExporter` merakit file `.xlsx` 4 *sheets* (*Ringkasan Digi-Safe*, *Pengguna & Pencapaian*, *Detail Pencapaian Titik*, dan *Forum & Edukasi*) menggunakan ekstensi bawaan PHP `ZipArchive`. Hasilnya sangat cepat, ringan, dan bebas dari error kompatibilitas *composer package* di shared hosting!
