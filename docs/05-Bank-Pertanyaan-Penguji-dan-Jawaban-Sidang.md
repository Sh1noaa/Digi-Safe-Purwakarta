# Modul 5: Bank Pertanyaan Penguji & Jawaban Terbaik Sidang Digi-Safe Purwakarta

Dokumen ini berisi **35+ daftar pertanyaan yang paling sering diajukan oleh penguji** (mulai dari pertanyaan dasar, teknis kode, keamanan siber, hingga pertanyaan jebakan) lengkap dengan **jawaban lisan yang mudah dipahami, lugas, dan tepat sasaran**.

---

## Bagian A: Pertanyaan Dasar, Bahasa, & Arsitektur Framework

### Q1: "Bahasa pemrograman dan framework apa saja yang kamu gunakan di website ini?"
**Jawaban:**
> "Untuk sisi **Backend**, saya menggunakan bahasa **PHP 8.2+** dengan framework **Laravel** berarsitektur **MVC (Model-View-Controller)**. Untuk sisi **Frontend**, saya menggunakan **Laravel Blade** sebagai template HTML, **Tailwind CSS v4** ditambah CSS kustom di `resources/css/app.css` untuk desain responsif dan Dark Mode, serta **JavaScript (Alpine.js v3 & Fetch API)** untuk interaksi *real-time* tanpa reload halaman. Aset dikompilasi menggunakan **Vite 7**, dan datanya disimpan di database **MySQL (`digi_safe_db`)**."

### Q2: "Kenapa kamu memilih Laravel dan Alpine.js dibandingkan membuat dengan PHP native atau React penuh?"
**Jawaban:**
> "Saya memilih **Laravel** karena sudah menyediakan fondasi keamanan siber berstandar industri secara bawaan, seperti proteksi *CSRF Token*, *Eloquent ORM* yang mencegah *SQL Injection*, *Bcrypt Password Hashing*, dan *Middleware Rate Limiting*. Sedangkan **Alpine.js** saya pilih karena sangat ringan dan bisa langsung ditulis di dalam file Blade (`.blade.php`) untuk membuat fitur interaktif seperti pop-up modal, filter *real-time*, *live chat*, dan *Password Strength Checker* tanpa membuat aplikasi menjadi berat seperti *Single Page Application* penuh."

### Q3: "Apa itu konsep MVC dan di folder mana saja letaknya pada proyekmu?"
**Jawaban:**
> "MVC adalah pemisahan kode menjadi 3 bagian utama agar rapi dan mudah dirawat:
> 1. **Model** (di folder `app/Models/`): Bertugas mewakili dan mengakses tabel database MySQL, misalnya `User.php`, `Forum.php`, dan `Testimoni.php`.
> 2. **View** (di folder `resources/views/`): Bertugas menampilkan antarmuka HTML/Blade kepada pengguna, misalnya `beranda.blade.php` dan `layouts/app.blade.php`.
> 3. **Controller** (di folder `app/Http/Controllers/`): Bertugas sebagai penghubung atau otak logika yang menerima permintaan dari `routes/web.php`, mengolah data lewat Model/Service, lalu mengirimkannya ke View."

### Q4: "Apa fungsi folder `app/Services/` di proyek kamu? Bukannya sudah ada Controller?"
**Jawaban:**
> "Folder `app/Services/` saya gunakan untuk menerapkan prinsip *Clean Code* agar Controller tidak terlalu gemuk (*fat controller*). Jadi perhitungan atau logika khusus saya pisahkan ke dalam *Service Class*, contohnya:
> - `AvatarBorderService.php` untuk mengecek syarat buka 5 border avatar.
> - `AdminAnalyticsService.php` untuk menghitung diagram tren naik/turun pendaftar user dan rating Bintang 5 sampai 1 di Panel Admin.
> - `LeaderboardBuilder.php` untuk menyusun peringkat klasemen.
> - `SimpleXlsxExporter.php` untuk membuat file laporan Excel `.xlsx`."

---

## Bagian B: Pertanyaan Navbar, Animasi Switch 3D, Dark Mode, & Translate

### Q5: "Di mana letak file yang mengatur Navbar, dan bagaimana Navbar tahu menu mana yang sedang aktif?"
**Jawaban:**
> "Kode Navbar berada di file induk `resources/views/layouts/app.blade.php`. Navbar menggunakan posisi `fixed top-4` dengan bentuk kapsul `rounded-full` dan efek kaca buram `backdrop-blur-md`. Untuk menandai menu yang sedang aktif, saya menggunakan fungsi Laravel `request()->is('edukasi*')` dan `request()->routeIs('minigame.*')`. Jika URL cocok, menu tersebut otomatis berwarna biru (`text-sky`) dan menampilkan garis indikator kecil di bawahnya."

### Q6: "Saat tombol ganti tema (Light/Dark Mode) diklik, bagaimana animasi tombol 3D Bulan dan Matahari di tengah layar itu dibuat?"
**Jawaban:**
> "Animasi tersebut dibuat dengan JavaScript murni dan SVG di dalam fungsi `createIllustratedThemeSwitch()` pada `resources/views/layouts/app.blade.php`. Prosesnya berjalan dalam 3 tahap:
> 1. Saat tombol tema di navbar diklik, script mengambil koordinat `(cx, cy)` tombol tersebut menggunakan `getBoundingClientRect()`.
> 2. Sebuah bulatan switch kecil (`34x34px`) muncul tepat dari posisi tombol navbar yang diklik, lalu meluncur ke tengah layar sambil melebar (*expand*) dari lebar `100px` menjadi kapsul 3D selebar `228px`.
> 3. Bola di dalamnya bergeser menyeberangi kapsul sambil berubah dari **Bulan Berkawah 3D dengan Bintang** menjadi **Matahari Emas dengan Awan Berlapis** (atau sebaliknya). Di bawahnya, teks **`Mode`** dibuat tetap diam (mutlak), sedangkan kata **`Dark/Light`** atau **`Gelap/Terang`** berganti dengan animasi *scroll* vertikal ke bawah di dalam jendela `overflow: hidden`."

### Q7: "Bagaimana sistem menyimpan pilihan Dark Mode dan Bahasa (Indonesia/Inggris) supaya tidak hilang saat pindah halaman?"
**Jawaban:**
> "Saat tombol tema atau bahasa diklik, browser mengirim request AJAX `POST /preferences/quick` ke `ProfileController::quickPreferences`. Di sana, pilihan disimpan ke **3 tempat**:
> 1. **Session Laravel** (`user_theme` dan `user_locale`).
> 2. **Kolom `theme` dan `locale` pada tabel `users`** jika user sedang login.
> 3. **Browser Cookie** (`digisafe_theme` dan `digisafe_locale`) selama 1 tahun untuk pengunjung tamu.
> Setiap kali halaman dimuat, middleware `ApplyUserPreferences.php` membaca nilai tersebut, memanggil `App::setLocale($locale)`, dan memasang class `dark` pada tag `<html>`."

### Q8: "Di mana teks bahasa Indonesia dan bahasa Inggris disimpan?"
**Jawaban:**
> "Untuk teks tampilan antarmuka disimpan di `lang/id/ui.php` (Indonesia) dan `lang/en/ui.php` (Inggris) yang dipanggil di Blade dengan fungsi `__('ui.nama_key')`. Sedangkan untuk menerjemahkan materi modul edukasi dan soal minigame dari database, saya membuat `app/Services/BilingualHelper.php`."

### Q9: "Kenapa notifikasi setelah simpan/edit muncul di pojok kanan atas dan tidak menggeser halaman ke bawah?"
**Jawaban:**
> "Saya membuat wadah global `#digisafe-toast-stack` di `resources/views/layouts/app.blade.php` dengan posisi `fixed top-20 right-4 sm:right-6 z-[95]` serta fungsi `window.digisafeToast()`. Karena menggunakan `position: fixed`, notifikasi melayang di atas lapisan halaman tanpa menambah tinggi dokumen HTML, sehingga struktur halaman tidak bergeser dan tidak memicu *scroll*."

---

## Bagian C: Pertanyaan Login, Register, Validasi Email, & Keamanan Siber

### Q10: "Bagaimana kamu mencegah orang mendaftar dengan email palsu atau asal-asalan seperti `asdf@abc.com`?"
**Jawaban:**
> "Saya membuat *Custom Validation Rule* di `app/Rules/ValidRealEmail.php` yang dipasang pada `AuthController::register()`. Rule ini menyaring email dalam 6 tahap:
> 1. Memastikan format standar RFC email valid.
> 2. Memblokir domain dummy/lokal seperti `example.com`, `.test`, `.local`.
> 3. Mendeteksi salah ketik domain populer (misal `@gmial.com` atau `@yaho.com`) dan memberi saran perbaikan.
> 4. Memblokir lebih dari 60 domain *disposable/temporary email* seperti `yopmail.com`, `mailinator.com`, dan `10minutemail.com`.
> 5. Memblokir pola ketikan acak/bot seperti `asdfgh`, `qwerty`, `test123`, atau nama akun yang hanya berisi angka.
> 6. Mengecek langsung ke internet menggunakan fungsi PHP `checkdnsrr($domain, 'MX')` untuk memastikan domain email tersebut benar-benar memiliki server email aktif."

### Q11: "Apakah password user di database bisa dilihat oleh Admin?"
**Jawaban:**
> "Tidak bisa sama sekali. Saat registrasi maupun ganti password, kata sandi di-*hash* satu arah menggunakan algoritma **Bcrypt** melalui `Hash::make()`. Di model `User.php`, kolom `password` juga dimasukkan ke dalam array `$hidden` sehingga tidak akan pernah ikut terkirim jika data user diubah menjadi JSON."

### Q12: "Bagaimana kalau ada hacker mencoba mengunggah file script `.php` berbahaya lewat fitur ganti Foto Profil atau Banner?"
**Jawaban:**
> "Sudah saya amankan di `app/Services/UploadValidationHelper.php`. Pertama, sistem **tidak pernah mempercayai ekstensi dari nama file asli pengguna** (`getClientOriginalExtension()`), melainkan memeriksa isi *magic bytes* gambar menggunakan `getimagesize()` dan memetakan MIME-nya hanya ke ekstensi aman (`jpg`, `png`, `gif`, `webp`). Kedua, di rute `/storage/{path}` dan file `.htaccess`, eksekusi file `.php`, `.phtml`, `.phar`, `.html`, `.svg`, dan `.js` diblokir dengan status `403 Forbidden`."

### Q13: "Bagaimana kalau pemain minigame berusaha curang mengubah skor menjadi `999999` lewat Inspect Element / Network Request?"
**Jawaban:**
> "Di `MinigameController::saveScore()`, server memvalidasi bahwa skor berada pada rentang wajar (`0` sampai `300`), jawaban benar dan salah berada di rentang `0..50`, serta membatasi pengiriman maksimal 15 kali per menit (`throttle:15,1`). Selain itu, lencana *100% Accuracy* tidak percaya pada klaim dari browser, melainkan dihitung ulang oleh server dengan mengecek apakah `$validated['salah'] === 0 && $validated['benar'] > 0`."

### Q14: "Bagaimana cara kerja Login dengan Google di website ini?"
**Jawaban:**
> "Menggunakan protokol **OAuth 2.0** di `AuthController.php`. Saat user mengklik tombol Google, `redirectToGoogle()` membuat token rahasia `state` di session dan mengarahkan user ke server Google. Setelah user menyetujui, Google mengarahkan kembali ke `handleGoogleCallback()`. Server kami memverifikasi token `state`, menukar `code` menjadi `id_token`, memeriksa kecocokan `aud` (*Client ID*) dan status `email_verified`, lalu meloginkan atau membuatkan akun user secara otomatis."

---

## Bagian D: Pertanyaan Fitur Profil, Border Avatar, Minigame, Forum, & Chat

### Q15: "Bagaimana sistem 5 Border Avatar bekerja? Apakah akun baru langsung dapat semuanya?"
**Jawaban:**
> "Tidak. Saat akun baru mendaftar, kolom `avatar_border` bernilai `null` (polos tanpa border). Saya membuat `app/Services/AvatarBorderService.php` yang mengatur 5 border dari 5 gambar referensi (`Aqua Crest`, `Astral Orbit`, `Cosmic Ring`, `Chrono Sovereign`, dan `Crimson Flare`). Setiap border memiliki syarat pembukaan (*unlock*) bertahap yang dihitung langsung dari database, misalnya harus bermain minimal 1 minigame untuk membuka *Aqua Crest*, atau mencapai skor $\ge 90$ dan 2 lencana untuk membuka *Crimson Flare*. User memilih border lewat pop-up modal di halaman Profil lalu menekan tombol **Simpan Perubahan**."

### Q16: "Bagaimana border avatar itu bisa muncul di semua halaman (Beranda, Pendaftar, Leaderboard, Forum, Testimoni) tanpa menulis ulang kodenya satu-satu?"
**Jawaban:**
> "Karena saya memusatkan tampilan foto profil pada komponen Blade `<x-user-avatar>` di `resources/views/components/user-avatar.blade.php` dan accessor `$user->avatar_border_url` di model `User.php`. Setiap kali komponen `<x-user-avatar>` dipanggil atau data JSON dikirim ke Forum/Testimoni/Leaderboard, gambar PNG border otomatis dirender melingkari foto profil pengguna."

### Q17: "Pada fitur Password Strength Checker, apakah password yang diketik user disimpan ke database?"
**Jawaban:**
> "Tidak. Fitur *Password Strength Checker* di `resources/views/minigame/password-checker.blade.php` diproses **100% secara lokal di browser pengguna menggunakan Alpine.js**. Input dimulai dalam keadaan kosong, dan saat user mengetik, JavaScript menghitung skor kekuatan (0-100%), estimasi waktu retas, serta menghasilkan 4 variasi kata sandi yang lebih kuat dari kata dasar user tanpa mengirim satu huruf pun ke server."

### Q18: "Bagaimana cara kerja fitur 'Most Helpful' (Jawaban Paling Membantu) di Forum Komunitas?"
**Jawaban:**
> "Di halaman Forum, setiap anggota yang login bisa memberikan 1 suara (*Thumbs Up* atau *Thumbs Down*) pada setiap balasan diskusi yang disimpan di tabel `jawaban_forum_votes`. Di `ForumController.php`, sistem menghitung skor bersih tiap jawaban (`up_count - down_count`). Jawaban yang memiliki minimal 1 *Thumbs Up* dan skor bersih tertinggi di topik tersebut otomatis mendapatkan badge hijau **`Most Helpful`** dan dinaikkan ke posisi paling atas daftar balasan."

### Q19: "Apa bedanya fitur Follow dan fitur Pertemanan (Friend) di website ini?"
**Jawaban:**
> "**Follow** (tabel `user_follows`) bersifat **satu arah** tanpa perlu persetujuan, digunakan untuk mengikuti warga/kontributor dan melihat jumlah *Followers/Following* di kartu profil. Sedangkan **Friend / Pertemanan** (tabel `friendships`) bersifat **dua arah** yang membutuhkan persetujuan (*Accept*) untuk membuka akses *Live Chat* tanpa batas. Jika belum berteman, seorang user hanya bisa mengirim 1 pesan perkenalan pertama dan harus menunggu penerima menyetujui permintaan pertemanan."

### Q20: "Siapa itu Bot Digika dan kapan dia mengirim pesan?"
**Jawaban:**
> "**Bot Digika** adalah akun asisten sistem otomatis (`bot.digika@digisafe.id`) yang dipastikan selalu tersedia lewat metode `User::ensureBotDigika()`. Ketika seorang pengguna mengusulkan tema modul edukasi baru di halaman `/edukasi`, lalu Admin menekan tombol **Setujui** atau **Tolak** di Panel Admin, metode `Admin\EdukasiController::applyEvaluationDecision()` otomatis mengirimkan pesan pemberitahuan resmi atas nama **Bot Digika** ke kotak masuk *Live Chat* pengguna tersebut."

---

## Bagian E: Pertanyaan Panel Admin, Diagram Analitik, & Laporan Excel

### Q21: "Bagaimana cara kerja diagram pertumbuhan user (Menaik/Menurun) dan diagram Bintang 5, 4, 3, 2, 1 Testimoni di Panel Admin?"
**Jawaban:**
> "Kedua diagram tersebut dihitung secara *real-time* dari database oleh `app/Services/AdminAnalyticsService.php` dan ditampilkan di `resources/views/admin/dashboard.blade.php`.
> 1. **Diagram Pertumbuhan User**: Mengelompokkan data `created_at` tabel `users` ke dalam periode **6 Bulan Terakhir** dan **7 Hari Terakhir**. Sistem membandingkan jumlah pendaftar periode ini dengan periode sebelumnya: jika bertambah, muncul badge hijau **Tren Menaik (+X user)**; jika berkurang, muncul badge merah **Tren Menurun (-X user)**; jika sama, muncul **Tren Stabil**, lengkap dengan grafik SVG batang, garis tren, dan garis akumulasi.
> 2. **Diagram Rating Testimoni (Bintang 5 sampai 1)**: Menghitung jumlah ulasan, persentase proporsi, dan selisih kenaikan (`delta`) untuk masing-masing level **Bintang 5, Bintang 4, Bintang 3, Bintang 2, dan Bintang 1**, serta menampilkan ringkasan otomatis bintang mana yang sedang naik pada periode ini."

### Q22: "Kenapa diagram di Panel Admin menggunakan SVG yang dihitung dari PHP (`AdminAnalyticsService`), bukan memakai library eksternal seperti Chart.js?"
**Jawaban:**
> "Dengan menghitung titik koordinat SVG (`<polyline>`, `<polygon>`, `<rect>`) langsung di `AdminAnalyticsService.php`, grafik langsung ter-render bersamaan dengan HTML pada milidetik pertama tanpa perlu mengunduh file JavaScript Chart.js ratusan kilobyte dari CDN. Hasilnya halaman Admin jauh lebih cepat, tidak mengalami *layout shift*, dan 100% tahan terhadap pemblokiran CDN."

### Q23: "Apa bedanya hak akses `Owner` dan `Admin` di Panel Admin?"
**Jawaban:**
> "Keduanya bisa mengakses Panel Admin (`/admin`) untuk mengelola modul edukasi, soal minigame, moderasi forum, dan mengunduh laporan Excel. Perbedaannya ada pada halaman **Hak Akses User** (`Admin\UserController.php`):
> - Akun **Owner (`owner@digisafe.id` / Razief Afkar Rusli)** bersifat mutlak dan dilindungi di level tampilan maupun controller: tidak ada seorang pun yang bisa mengubah atau menurunkan role Owner.
> - Hanya **Owner** yang memiliki wewenang mengangkat atau menurunkan seorang **Admin** lainnya, sedangkan Admin biasa hanya bisa mengatur pengguna berstatus `user`."

---

## Bagian F: Contekan Lokasi File Cepat (Saat Diminta Buka Kode oleh Penguji)

Jika penguji berkata **"Coba buka kodenya dan tunjukkan bagian..."**, langsung buka file ini:

| Jika Penguji Minta Tunjukkan... | Langsung Buka File Ini |
| :--- | :--- |
| **Daftar seluruh URL / Route & Rate Limit (`throttle`)** | [routes/web.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/routes/web.php) |
| **Navbar, Toast Kanan Atas, & Animasi Switch 3D** | [resources/views/layouts/app.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/layouts/app.blade.php) |
| **Aturan Warna Dark Mode (`html.dark`)** | [resources/css/app.css](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/css/app.css) |
| **Login, Register, & Google OAuth 2.0** | [app/Http/Controllers/AuthController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/AuthController.php) |
| **Validasi Anti-Email Asal / Spam & DNS MX** | [app/Rules/ValidRealEmail.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Rules/ValidRealEmail.php) |
| **Syarat Unlock 5 Border Avatar** | [app/Services/AvatarBorderService.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Services/AvatarBorderService.php) |
| **Halaman Profil, Efek Kursor, & Pop-up Border** | [resources/views/profile/show.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/profile/show.blade.php) |
| **Kartu Profil Pengguna yang Tingginya Seragam** | [resources/views/components/user-profile-card.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/components/user-profile-card.blade.php) |
| **Kuis Minigame & Anti-Cheat Simpan Skor** | [app/Http/Controllers/MinigameController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/MinigameController.php) |
| **Password Strength Checker** | [resources/views/minigame/password-checker.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/minigame/password-checker.blade.php) |
| **Forum Real-Time & Perhitungan `Most Helpful`** | [app/Http/Controllers/ForumController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/ForumController.php) |
| **Evaluasi Request Edukasi & Pesan Otomatis Bot Digika** | [app/Http/Controllers/Admin/EdukasiController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/Admin/EdukasiController.php) |
| **Diagram Tren User & Rating Bintang 5-1 di Panel Admin** | [app/Services/AdminAnalyticsService.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Services/AdminAnalyticsService.php) & [resources/views/admin/dashboard.blade.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/resources/views/admin/dashboard.blade.php) |
| **Generator Laporan Excel `.xlsx` 4 Sheets** | [app/Http/Controllers/Admin/ReportController.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Http/Controllers/Admin/ReportController.php) & [app/Services/SimpleXlsxExporter.php](file:///d:/digisafepurwakarta/Digi-Safe-Purwakarta/app/Services/SimpleXlsxExporter.php) |
