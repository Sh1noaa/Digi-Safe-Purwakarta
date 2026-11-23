# Dokumen Quality Assurance (QA) & Skenario Pengujian Fungsional
**Proyek:** Digi-Safe Purwakarta  
**Peserta UKK:** Razief Afkar Rusli — XII RPL, SMK Negeri 2 Purwakarta  
**Tanggal Pengujian:** 23 November 2026  
**Metode:** Black-Box Functional Testing (Positive & Negative/Edge Case) + Automated Feature Testing (`PHPUnit`)

---

## 1. Ringkasan Hasil Pengujian

| Kategori Pengujian | Total Skenario | Lulus (PASS) | Gagal (FAIL) |
|---|---:|---:|---:|
| Pengujian Manual / Black-Box (Tabel QA di bawah) | 26 | 26 | 0 |
| Pengujian Otomatis (`php artisan test`) | 54 Test Cases (653 Assertions) | 54 | 0 |

---

## 2. Tabel Skenario Uji Fungsional (Positive & Negative Testing)

### A. Modul Autentikasi, Multi-Role & Keamanan Kredensial

| ID | Modul / Fitur | Tipe Uji | Skenario Uji | Input / Langkah | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|---|---|
| **TC-01** | Register Akun | Positive | Pendaftaran warga baru dengan data valid | Isi nama, username unik, email `@gmail.com` valid, password >= 8 karakter, konfirmasi cocok | Akun tersimpan dengan password terenkripsi Bcrypt (`$2y$`), otomatis login dan diarahkan ke Beranda | Akun tersimpan terenkripsi Bcrypt, berhasil login dan diarahkan ke `/` | **PASS** |
| **TC-02** | Register Akun | Negative | Pendaftaran memakai email sementara (*disposable*) | Input email `tes@mailinator.com` atau `tes@tempmail.com` | Server menolak registrasi dengan pesan validasi domain email tidak diizinkan | Registrasi ditolak dengan error validasi, tidak ada baris baru di tabel `users` | **PASS** |
| **TC-03** | Register Akun | Negative | Pendaftaran memakai username terlarang (*reserved*) | Input username `admin` / `root` / `owner` | Server menolak dengan error validasi username dicadangkan sistem | Registrasi ditolak secara aman | **PASS** |
| **TC-04** | Login Multi-Role | Positive | Login sebagai Admin dan sebagai User biasa | Login `ukk.admin@digisafe.id` vs `ukk.warga@gmail.com` | Session ter-regenerasi, log aktivitas tercatat di `security_activity_logs`, Admin bisa buka `/admin` | Kedua role berhasil login sesuai hak akses masing-masing | **PASS** |
| **TC-05** | Login Brute-Force | Negative | Salah password berulang kali (>= 5x) | Masukkan password salah 5 kali berturut-turut untuk 1 akun | Akun terkunci sementara (rate-limit per akun) dan menampilkan waktu tunggu | Percobaan ke-6 diblokir oleh `RateLimiter` dengan pesan peringatan | **PASS** |
| **TC-06** | Proteksi Rute Admin | Negative | User biasa mencoba mengakses URL `/admin/users` atau `/admin/streak` secara langsung | Login sebagai `ukk.warga@gmail.com`, ketik URL `/admin/users` di address bar | Ditolak oleh `AdminMiddleware` dengan status HTTP `403 Forbidden` | Mengembalikan HTTP `403 Forbidden`, halaman admin tidak dapat diakses | **PASS** |
| **TC-07** | Proteksi Rute Guest | Negative | Pengunjung belum login mengakses `/profile` atau `/minigame/phishing` | Buka `/profile` tanpa login | Diarahkan paksa (*redirect*) oleh middleware `auth` ke `/login` | Redirect HTTP `302` ke `/login` | **PASS** |
| **TC-08** | Reset Password OTP | Positive | Minta kode OTP ke Gmail lalu reset sandi | Masukkan Gmail terdaftar di `/lupa-password`, input OTP 6 digit valid + sandi baru | Sandi terupdate terenkripsi Bcrypt, seluruh OTP lama terpakai, sesi lama dicabut | Password berhasil diperbarui dan dapat dipakai login | **PASS** |
| **TC-09** | Hapus Akun OTP | Negative | Minta OTP hapus akun dengan password akun salah | Login, buka Pengaturan -> Hapus Akun, masukkan password salah | Server menolak pengiriman OTP sebelum password akun terverifikasi | Permintaan ditolak (`422`), OTP tidak dikirim | **PASS** |
| **TC-10** | Proteksi Owner | Negative | Akun `owner` mencoba menghapus dirinya sendiri | Request `DELETE /profile/delete-account` oleh role `owner` | Sistem menolak penghapusan akun pemilik utama | Ditolak dengan status `403`, akun owner tetap utuh | **PASS** |

### B. Modul Keamanan Aplikasi Dasar (SQLi, XSS, CSRF, Anti-Tampering)

| ID | Modul / Fitur | Tipe Uji | Skenario Uji | Input / Langkah | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|---|---|
| **TC-11** | SQL Injection | Negative | Payload SQLi pada kolom login & pencarian edukasi | Input `' OR '1'='1` pada kolom email login dan pencarian `/edukasi?q=' OR 1=1--` | Query aman karena menggunakan Eloquent/PDO Prepared Statements; login gagal normal & pencarian tidak bocor | Login gagal normal, tidak ada error SQL maupun kebocoran data | **PASS** |
| **TC-12** | XSS Prevention | Negative | Payload script pada judul/isi Forum & Bio Profil | Input `<script>alert('xss')</script>` pada form diskusi forum | Blade auto-escaping `{{ ... }}` merender tag sebagai teks literal, script tidak dieksekusi | Ditampilkan sebagai teks biasa tanpa eksekusi JavaScript | **PASS** |
| **TC-13** | Anti-Tampering Skor | Negative | Manipulasi payload skor minigame via DevTools/JSON | Kirim `POST /minigame/save-score` dengan `skor: 9999` padahal jawaban salah | Server mengabaikan skor buatan klien dan menghitung ulang dari kunci jawaban di DB | Server menghitung skor riil berdasarkan jawaban benar/salah | **PASS** |
| **TC-14** | Security Headers | Positive | Inspeksi Response Headers di Chrome DevTools (Network) | Buka `/` dan `/login`, periksa HTTP Response Headers | Terdapat `X-Frame-Options`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, dan `Cache-Control: no-store` pada halaman auth | Seluruh header keamanan terpasang sesuai standar | **PASS** |

### C. Modul CRUD & Proses Bisnis Utama

| ID | Modul / Fitur | Tipe Uji | Skenario Uji | Input / Langkah | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|---|---|
| **TC-15** | CRUD Modul Edukasi | Positive | Admin menambah, mengubah, dan menghapus modul edukasi | Login Admin -> `/admin/edukasi` -> Tambah -> Edit -> Hapus | Data tersimpan di `modul_edukasi`, tampil di `/edukasi`, lalu terhapus bersih | Seluruh operasi Create, Read, Update, Delete berjalan tanpa error | **PASS** |
| **TC-16** | Validasi Ganda Form | Negative | Kirim form tambah Edukasi / Forum dengan field wajib kosong | Kosongkan judul lalu klik Simpan (dan uji bypass HTML5 lewat request langsung) | Validasi HTML5 (`required`) menahan di klien; server mengembalikan error validasi `422` / redirect error | Validasi klien dan server keduanya aktif menahan input kosong | **PASS** |
| **TC-17** | CRUD Soal Daily Streak | Positive | Admin menambah, mengedit, toggle status aktif, dan menghapus soal streak | Login Admin -> `/admin/streak` -> Tambah soal opsi A-E -> Edit -> Toggle -> Hapus | Perubahan langsung sinkron dengan tabel `daily_streak_questions` | Seluruh siklus CRUD berjalan mulus | **PASS** |
| **TC-18** | Proses Bisnis Laporan XLSX | Positive | Admin mengunduh laporan rekap sistem dalam format Excel | Login Admin -> klik `Download Laporan XLSX` (`/admin/report/xlsx`) | Browser mengunduh file `.xlsx` valid berisi rekap pengguna, skor, forum, dan edukasi | File `.xlsx` berhasil diunduh dan terbaca rapi di Excel | **PASS** |
| **TC-19** | Simulasi Chat WA & `.APK` | Positive | Bermain skenario chat WA multi-turn hingga mencapai ending | Buka `/minigame/wa-simulator`, pilih kontak, balas 3-4 giliran | Pesan bercabang sesuai jawaban, menampilkan ending (Aman/Nyaris/Terjebak), skor tercatat ke `skor_game` & Leaderboard WA Accuracy | Percakapan 15 kontak berjalan lancar di ID & EN dan masuk Leaderboard | **PASS** |
| **TC-20** | Link & QR Phishing Scanner | Positive | Pindai URL phishing `.apk` vs URL resmi `.go.id` | Masukkan `http://bri-tarif-baru2026.xyz/Formulir.apk` lalu `https://purwakartakab.go.id` | URL pertama diberi verdict `dangerous` (skor risiko tinggi), URL kedua `safe` | Verdict, skor risiko, dan bedah anatomi domain akurat | **PASS** |
| **TC-21** | Daily Streak & Freeze 24j | Positive | Jawab benar soal harian A-E, simulasi lewat 24 jam (Freeze), lalu pulihkan | Jawab benar di widget streak -> simulasi >24j -> jawab benar lagi | Streak menyala (1), berubah status `frozen` setelah 24 jam, lalu pulih (`recovered`) mengurangi jatah recovery dari 2x menjadi 1x | Logika streak, freeze, dan 2x recovery berjalan akurat | **PASS** |
| **TC-22** | Forum & Voting Realtime | Positive | Buat diskusi, kirim balasan, lakukan upvote, dan edit/hapus milik sendiri | Login -> `/forum` -> Buat topik -> Balas -> Vote | Diskusi tampil realtime, vote terhitung, user lain ditolak (`403`) jika mencoba edit/hapus milik orang lain | CRUD & kepemilikan data forum terproteksi penuh | **PASS** |
| **TC-23** | Leaderboard 5 Kategori | Positive | Periksa klasemen komunitas di `/leaderboard` | Buka `/leaderboard`, ganti tab 5 kategori | Menampilkan 5 kategori aktif (Minigame Benar, Streak Terbanyak, Akurasi WA, Penjawab Forum, Lencana Terbanyak) tanpa kategori salah | Seluruh 5 kategori dan API `/api/leaderboard` tampil akurat | **PASS** |
| **TC-24** | Profil & Banner Warna | Positive | Pilih warna default banner Solid, Custom Hex, dan Gradient | Login -> Edit Profil -> Pilih warna banner Solid/Gradient -> Simpan | Warna banner tersimpan di `privacy_settings` dan tampil pada header `/profile/{id}` | Banner terupdate seketika di profil publik & pribadi | **PASS** |

### D. Modul UI/UX, Responsivitas, Tema Gelap & Bilingual

| ID | Modul / Fitur | Tipe Uji | Skenario Uji | Input / Langkah | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|---|---|
| **TC-25** | Responsivitas Mobile | Positive | Uji tampilan pada lebar layar 375px (Mobile) dan 1440px (Desktop) di DevTools | Buka Beranda, Edukasi, Minigame, WA Simulator, Tentang di Chrome DevTools Device Toolbar | Tidak ada *horizontal scroll*, tombol mudah disentuh (>=44px), kartu tertata vertikal rapi | Tampilan responsif dan bebas overflow di semua halaman | **PASS** |
| **TC-26** | Mode Gelap & Bilingual | Positive | Ganti tema Light <-> Dark dan bahasa ID <-> EN | Klik toggle tema dan bahasa di navbar lalu cek `/`, `/tentang`, `/minigame/wa-simulator` | Seluruh teks terjemahan berubah ID/EN dan kontras teks tetap terbaca jelas (WCAG AA) | Tema gelap dan terjemahan ID/EN berfungsi konsisten | **PASS** |

---

## 3. Bukti Eksekusi Automated Testing (`php artisan test`)

```text
PASS  Tests\Unit\ExampleTest (1 passed)
PASS  Tests\Feature\AccountOtpSecurityTest (9 passed)
PASS  Tests\Feature\AdminStreakQuestionTest (3 passed)
PASS  Tests\Feature\ApiEndpointsTest (3 passed)
PASS  Tests\Feature\AvatarBorderAndUiPolishTest (6 passed)
PASS  Tests\Feature\ExampleTest (1 passed)
PASS  Tests\Feature\NewFeaturesTest (9 passed)
PASS  Tests\Feature\ProfileCropRoleEdukasiBotTest (4 passed)
PASS  Tests\Feature\RevisionFeaturesTest (4 passed)
PASS  Tests\Feature\SecurityAndPerformanceTest (11 passed)
PASS  Tests\Feature\SocialChatAndRoleTest (3 passed)

Tests:    54 passed (653 assertions)
```
