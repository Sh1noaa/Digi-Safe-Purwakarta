# Panduan Persiapan Ujian UKK 45 Menit & Latihan Live Coding (Tanpa AI)
**Khusus Peserta:** Razief Afkar Rusli — XII RPL, SMK Negeri 2 Purwakarta  
**Penguji / Skema:** PT. Pratama Solusi Teknologi — Junior Web Developer / Full-Stack Web Developer (45 Menit)

---

## Checklist Persiapan 15 Menit Sebelum Masuk Ruangan

1. **Matikan / Nonaktifkan seluruh ekstensi AI di VS Code** (Copilot, Codeium, Antigravity extension, dll.) sesuai aturan Lingkungan Pengembangan (*"Dilarang menggunakan AI Code Generator saat sesi live coding"*).
2. Pastikan **MySQL (XAMPP/Laragon)** berjalan dan `php artisan serve` aktif di `http://127.0.0.1:8000`.
3. Buka 4 tab di Browser Chrome (+ siapkan DevTools `F12` tab **Network** & **Console**):
   - Tab 1: `http://127.0.0.1:8000` (Halaman Utama)
   - Tab 2: `http://127.0.0.1:8000/tentang` (Menampilkan Fakta Siber & Profil)
   - Tab 3: `http://localhost/phpmyadmin` -> database `digi_safe_db` -> buka tab **Designer** (untuk memperlihatkan 13 relasi Foreign Key!) & tabel `users` (untuk membuktikan 100% password Bcrypt `$2y$`).
   - Tab 4: Preview Markdown `DOKUMEN_PERANCANGAN_ERD_USECASE_FLOWCHART.md` dan `DOKUMEN_QA_DAN_TEST_CASE_UKK.md` di VS Code.
4. Siapkan terminal di VS Code untuk perintah cepat:
   - `git log --oneline -n 15` (bukti keaslian pengerjaan & riwayat commit)
   - `php artisan test` (bukti 54 automated test PASS 100%)

---

## Strategi Manajemen Waktu 45 Menit (Sesuai Alur Soal)

### Tahap 1: Peninjauan Arsitektur & Spesifikasi Sistem (5 Menit)
**Yang harus kamu jelaskan dengan tenang & terstruktur:**
1. **Masalah & Tujuan**: *"Digi-Safe Purwakarta saya bangun untuk membantu masyarakat Purwakarta mencegah penipuan digital seperti file `.APK` kurir/undangan, phishing bank, dan pembajakan WA, berlandaskan data resmi OJK IASC (741 ribu+ laporan), BSSN 2025 (5,2 miliar anomali, 93,78% malware), dan APJII 2025."*
2. **Tech Stack & Arsitektur MVC**: *"Backend menggunakan Laravel 12 (PHP 8), database relasional MySQL InnoDB (30 tabel, 13 Foreign Key), frontend Tailwind CSS v4 + Alpine.js + Vite, serta dukungan PWA."*
   - Pisahkan peran folder: `Models/` untuk representasi data & relasi Eloquent, `Controllers/` untuk menerima HTTP request & validasi, `Services/` untuk logika bisnis kompleks (seperti `LeaderboardService`, `DailyStreakService`, `PhishingLinkScannerService`), `Middleware/` untuk keamanan & proteksi role, dan `resources/views/` untuk tampilan Blade.
3. **Tunjukkan Dokumen Perancangan**: Buka [`DOKUMEN_PERANCANGAN_ERD_USECASE_FLOWCHART.md`](DOKUMEN_PERANCANGAN_ERD_USECASE_FLOWCHART.md) -> tunjukkan **ERD**, **Use Case Multi-Role (`Guest`, `User`, `Admin`, `Owner`)**, dan **Flowchart Proses Bisnis**.

### Tahap 2: Uji Kinerja Fungsional, Keamanan & End-to-End Testing (15 Menit)
**Urutan Demo yang Paling Mengesankan Penguji:**
1. **Autentikasi & Multi-Role + Negative Testing**:
   - Coba akses `/admin/users` tanpa login -> otomatis redirect ke `/login`.
   - Login sebagai **User biasa** (`ukk.warga@gmail.com` / `WargaUKK2026!`) -> coba ketik manual `/admin/users` -> **403 Forbidden** (bukti proteksi role!).
   - Uji **Negative Testing** di form Register/Forum: coba kirim input kosong atau email palsu (`tes@mailinator.com`) -> tertahan validasi klien & server.
2. **Alur Transaksi Utama (User)**:
   - Tunjukkan **Simulasi Chat WA & `.APK`** (`/minigame/wa-simulator`) -> selesaikan 1 kontak -> skor & akurasi otomatis masuk database & Leaderboard (`/leaderboard`).
   - Tunjukkan **Link & QR Phishing Scanner** (`/minigame/link-scanner`) -> uji 1 link phishing `.apk` (`dangerous`) vs 1 link `.go.id` (`safe`).
   - Tunjukkan **Daily Streak** di Beranda (opsi A-E tingkat lanjut + animasi api menyala & freeze 24 jam).
   - Tunjukkan **Mode Gelap**, **Bahasa EN/ID**, dan **Responsif Mobile** lewat DevTools `F12` (375px).
3. **Alur CRUD & Laporan (Admin)**:
   - Login sebagai **Admin** (`ukk.admin@digisafe.id` / `AdminUKK2026!`).
   - Demo CRUD lengkap (Tambah -> Lihat -> Edit -> Hapus) di `/admin/edukasi` atau `/admin/streak`.
   - Klik tombol **Analitik & Diagram** (popup grafik interaktif) dan klik **Download Laporan XLSX** (`/admin/report/xlsx`) -> tunjukkan file Excel hasil ekspor!
4. **Tunjukkan Dokumen QA**: Buka [`DOKUMEN_QA_DAN_TEST_CASE_UKK.md`](DOKUMEN_QA_DAN_TEST_CASE_UKK.md) dan jalankan `php artisan test` (54 tests PASS dalam ~2 detik!).

### Tahap 3: Audit Basis Data & Tinjauan Kode (10 Menit)
**Buka 4 Hal Ini di Hadapan Penguji:**
1. **phpMyAdmin -> `digi_safe_db` -> Tab Designer**: Tunjukkan 13 garis relasi Foreign Key aktif.
2. **Tabel `users`**: Tunjukkan kolom `password` seluruh akun diawali `$2y$12$...` (Bcrypt hash, nol *plain text*).
3. **Terminal -> `git log --oneline -n 20`**: Tunjukkan riwayat pengerjaan bertahap dari awal hingga akhir.
4. **File Kode Krusial yang Wajib Kamu Hafal Lokasinya**:
   - Routing & Middleware: `routes/web.php`
   - Proteksi Admin: `app/Http/Middleware/AdminMiddleware.php`
   - Security Headers: `app/Http/Middleware/SecurityHeaders.php`
   - Login, Validasi, Bcrypt & Brute-Force: `app/Http/Controllers/AuthController.php`
   - Perhitungan Skor Server-Side (Anti-Tamper): `app/Http/Controllers/MinigameController.php`
   - Export Laporan Excel `.xlsx`: `app/Http/Controllers/Admin/ReportController.php`
   - Logika Peringkat & Agregasi: `app/Services/LeaderboardService.php`

---

## 15 Pertanyaan Tajam Penguji Industri + Cara Menjawab Tegas

1. **Bagaimana aplikasi Anda mencegah SQL Injection?**
   > *"Seluruh query memakai Eloquent ORM dan Query Builder Laravel yang secara internal menggunakan PDO Prepared Statements (parameter binding), sehingga input user tidak pernah digabungkan langsung ke string SQL."*
2. **Bagaimana cara mencegah XSS (Cross-Site Scripting)?**
   > *"Di Blade template, seluruh data input user dirender menggunakan sintaks `{{ $data }}` yang otomatis menjalankan `htmlspecialchars()`, sehingga tag `<script>` berubah menjadi teks biasa dan tidak dieksekusi browser. Selain itu ada middleware `SecurityHeaders` untuk Content-Security-Policy."*
3. **Bagaimana cara kerja CSRF Protection di aplikasi ini?**
   > *"Setiap form `POST`, `PUT`, dan `DELETE` menyertakan directive `@csrf` (atau header `X-CSRF-TOKEN` pada `fetch()` AJAX). Laravel memvalidasi kecocokan token tersebut dengan token session aktif sebelum controller dijalankan."*
4. **Kenapa tidak menyimpan password dengan MD5 atau SHA1?**
   > *"MD5 dan SHA1 terlalu cepat dan rentan serangan rainbow table serta brute-force GPU. Aplikasi ini memakai Bcrypt (`Hash::make()`), yang memiliki salt unik acak per akun dan cost factor sehingga sangat lambat di-brute-force."*
5. **Apa bedanya Authentication dan Authorization di proyek Anda?**
   > *"Authentication memverifikasi identitas siapa yang login (middleware `auth` di `AuthController`). Authorization memverifikasi hak akses apa yang boleh dilakukan setelah login, misalnya `AdminMiddleware` hanya mengizinkan role `admin` dan `owner`, serta pengecekan kepemilikan `$forum->user_id === Auth::id()` agar user tidak bisa menghapus diskusi orang lain."*
6. **Kenapa logika bisnis ditaruh di folder `app/Services/` bukan semua di Controller?**
   > *"Untuk menerapkan Clean Code & Single Responsibility Principle (SRP). Controller cukup menangani request, validasi, dan response, sedangkan perhitungan rumit seperti `LeaderboardService`, `DailyStreakService`, dan `PhishingLinkScannerService` dipisah di Service agar mudah dirawat dan diuji dengan unit/feature test."*
7. **Mengapa `AuthController` memanggil `$request->session()->regenerate()` setelah login?**
   > *"Untuk mencegah serangan Session Fixation, yaitu saat penyerang menanamkan ID sesi lama sebelum korban login."*
8. **Apa yang terjadi jika user memanipulasi skor minigame lewat Inspect Element / Network DevTools?**
   > *"Gagal, karena perhitungan jumlah benar, salah, akurasi, dan skor dilakukan ulang di server (`MinigameController::saveScore`) berdasarkan kunci jawaban asli di database, bukan mempercayai angka skor dari frontend."*
9. **Apa kegunaan Foreign Key di database Anda?**
   > *"Menjaga integritas referensial antar tabel (referential integrity), misalnya data di `jawaban_forum` tidak boleh merujuk ke `forum_id` atau `user_id` yang tidak ada di tabel induknya."*
10. **Coba jelaskan perbedaan `INNER JOIN` dan `LEFT JOIN`!**
    > *"`INNER JOIN` hanya menampilkan baris yang cocok di kedua tabel (misalnya user yang sudah pernah main game di `skor_game`). `LEFT JOIN` menampilkan semua data dari tabel kiri walaupun belum ada pasangannya di tabel kanan (misalnya semua thread `forum` tetap tampil meski `jawaban_forum`-nya masih 0)."*
11. **Bagaimana Anda melakukan debugging jika muncul HTTP Error 500?**
    > *"Saya buka Chrome DevTools tab Network untuk melihat endpoint & payload-nya, lalu membaca detail stack trace error terbaru di file `storage/logs/laravel.log` untuk menemukan nama file dan nomor baris penyebabnya."*
12. **Apa penyebab umum HTTP Error 419 Page Expired di Laravel?**
    > *"Form `POST` lupa menambahkan `@csrf`, atau session browser sudah kedaluwarsa."*
13. **Apa fungsi `$fillable` pada Model Eloquent?**
    > *"Mencegah kerentanan Mass Assignment, yaitu membatasi kolom mana saja yang diizinkan diisi lewat `Model::create()` atau `$model->update()`, sehingga kolom sensitif seperti `role` tidak bisa disusupkan sembarangan lewat input form."*
14. **Kenapa memakai validasi ganda (Client-side & Server-side)?**
    > *"Validasi client-side (HTML5/JS) memberi umpan balik instan bagi UX tanpa reload halaman, sedangkan validasi server-side (`$request->validate`) wajib ada untuk keamanan karena validasi client-side bisa dilewati lewat DevTools atau Postman."*
15. **Bagaimana fitur OTP Reset Password & Hapus Akun bekerja tanpa terkena blokir port SMTP?**
    > *"Kode OTP 6 digit disimpan dalam bentuk hash di tabel `account_otps` dengan masa berlaku terbatas, cooldown kirim ulang, dan kuota per jam. Pengirimannya dikirim melalui HTTPS port 443 ke Google Apps Script Mail Webhook agar instan dan tidak diblokir firewall port SMTP."*

---

## 8 Pola Live Coding Challenge (Tanpa AI) yang Paling Sering Keluar di UKK Laravel

Latih dan pahami 8 pola ini malam ini! Penguji biasanya meminta 1-2 perubahan kecil dalam 10-15 menit untuk memastikan kamu benar-benar paham kode kamu sendiri:

### Pola 1: Menambah Validasi Baru di Server + Pesan Error Custom
Misal penguji minta: *"Tambahkan aturan agar judul forum minimal 10 karakter dan maksimal 120 karakter."*
- Buka `app/Http/Controllers/ForumController.php` -> cari `$request->validate([...])` -> ubah:
  ```php
  'judul' => ['required', 'string', 'min:10', 'max:120'],
  ```
- Di view Blade (validasi client), tambahkan atribut HTML5 `minlength="10" maxlength="120" required` dan tampilkan `@error('judul') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror`.

### Pola 2: Menambah Fitur Pencarian / Filter Kategori di Controller
Misal penguji minta: *"Tambahkan filter berdasarkan kategori pada halaman admin edukasi."*
```php
$query = ModulEdukasi::query();
if ($request->filled('kategori')) {
    $query->where('kategori', $request->string('kategori'));
}
if ($request->filled('q')) {
    $q = $request->string('q');
    $query->where('judul', 'like', "%{$q}%");
}
$moduls = $query->latest()->get();
```

### Pola 3: Menambah Kolom Baru ke Tabel + Model + Form
Misal penguji minta: *"Tambahkan kolom `sumber` pada modul edukasi."*
1. Buat migration: `php artisan make:migration add_sumber_to_modul_edukasi_table`
2. Di method `up()`:
   ```php
   Schema::table('modul_edukasi', function (Blueprint $table) {
       $table->string('sumber', 150)->nullable()->after('kategori');
   });
   ```
3. Jalankan `php artisan migrate`.
4. Tambahkan `'sumber'` ke array `$fillable` di `app/Models/ModulEdukasi.php`.
5. Tambahkan validasi `'sumber' => ['nullable', 'string', 'max:150']` di Controller dan `<input name="sumber">` di Blade.

### Pola 4: Menambah Route Baru yang Dilindungi Middleware Role
Misal penguji minta: *"Buat halaman khusus admin `/admin/ringkasan` yang hanya bisa dibuka admin."*
- Di `routes/web.php` di dalam grup `Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(...)`:
  ```php
  Route::get('/ringkasan', [DashboardController::class, 'ringkasan'])->name('ringkasan');
  ```

### Pola 5: Menulis Query Agregasi / JOIN di Eloquent atau DB Facade
Misal penguji minta: *"Tampilkan 5 user dengan total skor minigame tertinggi."*
```php
$topUsers = DB::table('users')
    ->join('skor_game', 'skor_game.user_id', '=', 'users.id')
    ->select('users.nama_lengkap', DB::raw('SUM(skor_game.skor) as total_skor'), DB::raw('COUNT(skor_game.id) as total_main'))
    ->groupBy('users.id', 'users.nama_lengkap')
    ->orderByDesc('total_skor')
    ->limit(5)
    ->get();
```

### Pola 6: Memperbaiki Celah Keamanan yang Sengaja Ditanyakan
- Jika melihat `{!! $userInput !!}` -> ubah menjadi `{{ $userInput }}` (cegah XSS).
- Jika melihat `DB::select("SELECT * FROM users WHERE email = '$email'")` -> ubah menjadi `DB::select('SELECT * FROM users WHERE email = ?', [$email])` atau `User::where('email', $email)->first()` (cegah SQL Injection).
- Jika melihat `'password' => $request->password` -> ubah menjadi `'password' => Hash::make($request->password)` (enkripsi Bcrypt).
- Jika melihat `User::create($request->all())` -> ubah menjadi `User::create($validated)` (cegah Mass Assignment).

### Pola 7: Menambah Tombol Aksi / Toggle Status di Admin
1. Route `PATCH`: `Route::patch('/item/{item}/toggle', [ItemController::class, 'toggle'])->name('item.toggle');`
2. Controller:
   ```php
   $item->update(['is_active' => ! $item->is_active]);
   return back()->with('success', 'Status berhasil diperbarui.');
   ```
3. Form di Blade:
   ```blade
   <form method="POST" action="{{ route('admin.item.toggle', $item) }}">
       @csrf
       @method('PATCH')
       <button type="submit">Ubah Status</button>
   </form>
   ```

### Pola 8: Debugging Cepat Saat Error Muncul Live
1. **Error 500**: Buka `storage/logs/laravel.log` bagian paling bawah -> baca pesan error & nomor baris.
2. **Error 404**: Cek `php artisan route:list` -> pastikan urutan route spesifik berada di ATAS route wildcard `/{id}` atau `/{type}`.
3. **Error 419**: Tambahkan `@csrf` di dalam `<form>`.
4. **Error 422 / Validation tidak kelihatan**: Tampilkan `@if ($errors->any())` di Blade atau cek tab **Network -> Response** di DevTools.
5. **View / Route tidak berubah**: Jalankan `php artisan view:clear` atau `php artisan route:clear`.
