<?php

namespace Database\Seeders;

use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\ModulEdukasi;
use App\Models\SoalMinigame;
use App\Models\Testimoni;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // pastikan skema kolom terbaru tersedia
        if (Schema::hasTable('users')) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'admin', 'user') DEFAULT 'user'");
        }

        if (Schema::hasTable('modul_edukasi')) {
            Schema::table('modul_edukasi', function (Blueprint $table) {
                if (! Schema::hasColumn('modul_edukasi', 'type')) {
                    $table->string('type', 30)->default('link')->after('kategori');
                }
                if (! Schema::hasColumn('modul_edukasi', 'url')) {
                    $table->string('url', 500)->nullable()->after('type');
                }
            });
        }

        if (Schema::hasTable('forum') && ! Schema::hasColumn('forum', 'is_starred')) {
            Schema::table('forum', function (Blueprint $table) {
                $table->boolean('is_starred')->default(false)->after('is_hidden');
            });
        }

        if (Schema::hasTable('testimoni') && ! Schema::hasColumn('testimoni', 'rating')) {
            Schema::table('testimoni', function (Blueprint $table) {
                $table->unsignedTinyInteger('rating')->default(5)->after('cerita');
            });
        }

        if (Schema::hasTable('soal_minigame')) {
            DB::statement("ALTER TABLE soal_minigame MODIFY COLUMN tingkat VARCHAR(30) DEFAULT 'easy'");
            DB::statement("ALTER TABLE soal_minigame MODIFY COLUMN game_level VARCHAR(50) DEFAULT 'phishing'");
        }

        // 1. akun owner utama (tidak dapat diubah/dihapus melalui UI)
        $owner = User::updateOrCreate(
            ['email' => 'owner@digisafe.id'],
            [
                'username' => 'Razief Afkar Rusli',
                'nama_lengkap' => 'Razief Afkar Rusli',
                'password' => Hash::make('password123'),
                'role' => 'owner',
            ]
        );

        // 2. akun admin operasional
        $admin = User::updateOrCreate(
            ['email' => 'admin@digisafe.id'],
            [
                'username' => 'admin',
                'nama_lengkap' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // bersihkan data dummy lama di modul_edukasi dan isi dengan modul kurasi resmi
        ModulEdukasi::whereIn('judul', ['modul testing', 'anjay gurinjay', 'testestes123'])->delete();

        $sampleModuls = [
            [
                'judul' => 'Jurnal ITEE — Keamanan Digital',
                'kategori' => 'Jurnal Akademik',
                'type' => 'link',
                'url' => 'https://journal.pandawan.id/itee/article/view/665',
                'link_url' => 'https://journal.pandawan.id/itee/article/view/665',
                'konten' => 'Kajian akademik tentang keamanan informasi dan perlindungan privasi pengguna di era transformasi digital.',
                'created_by' => $owner->id,
            ],
            [
                'judul' => 'Bug Bounty 2025 — Kemendikdasmen',
                'kategori' => 'Talenta Siber',
                'type' => 'link',
                'url' => 'https://www.kemendikdasmen.go.id/en/press-release/14317-boosting-cyber-talent-strengthening-digital-security-in-education-in-indonesia-through-bug-bounty-2025',
                'link_url' => 'https://www.kemendikdasmen.go.id/en/press-release/14317-boosting-cyber-talent-strengthening-digital-security-in-education-in-indonesia-through-bug-bounty-2025',
                'konten' => 'Program penguatan talenta siber dan ketahanan infrastruktur pendidikan nasional.',
                'created_by' => $owner->id,
            ],
            [
                'judul' => 'MAFINDO — Masyarakat Anti Fitnah Indonesia',
                'kategori' => 'Cek Fakta',
                'type' => 'link',
                'url' => 'https://mafindo.or.id/',
                'link_url' => 'https://mafindo.or.id/',
                'konten' => 'Komunitas pemeriksa fakta dan rujukan verifikasi berita bohong (hoaks) di Indonesia.',
                'created_by' => $admin->id,
            ],
            [
                'judul' => 'Literasi Digital Indonesia',
                'kategori' => 'Portal Nasional',
                'type' => 'link',
                'url' => 'https://literasidigital.id/',
                'link_url' => 'https://literasidigital.id/',
                'konten' => 'Portal resmi literasi digital nasional berbasis 4 pilar: Cakap, Aman, Budaya, dan Etis.',
                'created_by' => $admin->id,
            ],
            [
                'judul' => 'Bijak Bermedia Sosial — DJKN Kemenkeu',
                'kategori' => 'Etika Siber',
                'type' => 'link',
                'url' => 'https://www.djkn.kemenkeu.go.id/artikel/baca/15818/Bijak-Bermedia-Sosial.html',
                'link_url' => 'https://www.djkn.kemenkeu.go.id/artikel/baca/15818/Bijak-Bermedia-Sosial.html',
                'konten' => 'Panduan bijak menggunakan media sosial dan menjaga rekam jejak digital dari Kementerian Keuangan.',
                'created_by' => $admin->id,
            ],
            [
                'judul' => 'Panduan Praktis Mengenali Modus File APK & Phishing WhatsApp',
                'kategori' => 'Panduan Publik',
                'type' => 'teks',
                'url' => null,
                'link_url' => '',
                'konten' => "Penipuan bermodus file berekstensi .APK (seperti Surat Undangan Nikah, Resi Kurir Paket, atau Tagihan Listrik) bekerja dengan cara meminta izin baca SMS untuk mencuri kode OTP perbankan Anda.\n\nLangkah Pencegahan Utama:\n1. Jangan pernah membuka atau menginstal file berakhiran .apk yang dikirim melalui WhatsApp atau Telegram.\n2. Matikan izin 'Install Unknown Apps' pada pengaturan browser dan aplikasi pesan Anda.\n3. Apabila terlanjur menginstal, segera matikan koneksi internet (aktifkan Mode Pesawat), hapus aplikasi mencurigakan tersebut, lalu hubungi pihak bank untuk memblokir sementara layanan mobile banking.",
                'created_by' => $owner->id,
            ],
        ];

        foreach ($sampleModuls as $modul) {
            ModulEdukasi::updateOrCreate(
                ['judul' => $modul['judul']],
                array_merge($modul, ['created_at' => now()])
            );
        }

        // bank soal minigame untuk 3 game dan 3 level
        $sampleSoal = [
            [
                'pertanyaan' => 'Anda menerima pesan WhatsApp dari nomor tidak dikenal berisi file "LIHAT-RESI-PAKET.apk". Apa tindakan paling tepat?',
                'pilihan_json' => json_encode([
                    'A' => 'Mengunduh dan menginstal file untuk mengecek paket',
                    'B' => 'Menghapus pesan dan memblokir pengirim karena file .apk berpotensi mencuri OTP',
                    'C' => 'Meneruskan pesan tersebut ke grup keluarga',
                    'D' => 'Membalas pesan dengan mengirimkan nomor rekening',
                ]),
                'jawaban_benar' => 'B',
                'kategori' => 'Phishing Hunter',
                'tingkat' => 'easy',
                'game_level' => 'phishing',
            ],
            [
                'pertanyaan' => 'Email dengan subjek "AKUN ANDA AKAN DIBLOKIR DALAM 10 MENIT" berasal dari alamat support@bca-verifikasi-data.xyz. Indikator utama bahwa ini phishing adalah...',
                'pilihan_json' => json_encode([
                    'A' => 'Menggunakan domain tidak resmi (.xyz) dan menciptakan kepanikan mendesak',
                    'B' => 'Menggunakan bahasa Indonesia yang baku',
                    'C' => 'Memiliki subjek huruf kapital',
                    'D' => 'Dikirim pada jam kerja',
                ]),
                'jawaban_benar' => 'A',
                'kategori' => 'Phishing Hunter',
                'tingkat' => 'medium',
                'game_level' => 'phishing',
            ],
            [
                'pertanyaan' => 'Serangan spear-phishing yang menyamar sebagai surat resmi kepala instansi dengan lampiran dokumen ber-macro berbahaya paling efektif dicegah dengan...',
                'pilihan_json' => json_encode([
                    'A' => 'Langsung mengaktifkan macro agar dokumen terbaca',
                    'B' => 'Verifikasi silang melalui saluran komunikasi resmi dan menonaktifkan auto-run macro',
                    'C' => 'Mengganti wallpaper komputer',
                    'D' => 'Mematikan layar monitor sementara',
                ]),
                'jawaban_benar' => 'B',
                'kategori' => 'Phishing Hunter',
                'tingkat' => 'hard',
                'game_level' => 'phishing',
            ],
            [
                'pertanyaan' => 'Komputer mendadak menampilkan layar terkunci dan meminta tebusan kripto untuk membuka enkripsi file. Serangan ini disebut...',
                'pilihan_json' => json_encode([
                    'A' => 'Adware',
                    'B' => 'Ransomware',
                    'C' => 'Defacement',
                    'D' => 'Keylogger',
                ]),
                'jawaban_benar' => 'B',
                'kategori' => 'Cyber Defender',
                'tingkat' => 'easy',
                'game_level' => 'cyber-defender',
            ],
            [
                'pertanyaan' => 'Saat menggunakan Wi-Fi publik di kafe untuk bekerja, pertahanan terbaik agar lalu lintas data tidak disadap (Man-in-the-Middle) adalah...',
                'pilihan_json' => json_encode([
                    'A' => 'Menggunakan mode penyamaran (Incognito) saja',
                    'B' => 'Mengaktifkan koneksi VPN terenkripsi atau menggunakan hotspot pribadi',
                    'C' => 'Menurunkan kecerahan layar laptop',
                    'D' => 'Menghapus riwayat pencarian browser',
                ]),
                'jawaban_benar' => 'B',
                'kategori' => 'Cyber Defender',
                'tingkat' => 'medium',
                'game_level' => 'cyber-defender',
            ],
            [
                'pertanyaan' => 'Langkah mitigasi pertama ketika mendeteksi satu perangkat di jaringan kantor terinfeksi worm yang menyebar otomatis adalah...',
                'pilihan_json' => json_encode([
                    'A' => 'Mengisolasi perangkat dari jaringan (cabut LAN / matikan Wi-Fi) segera',
                    'B' => 'Membiarkan perangkat tetap terhubung sambil mengunduh game',
                    'C' => 'Membagikan folder kerja ke komputer lain lewat jaringan lokal',
                    'D' => 'Menunggu hingga jam pulang kantor',
                ]),
                'jawaban_benar' => 'A',
                'kategori' => 'Cyber Defender',
                'tingkat' => 'hard',
                'game_level' => 'cyber-defender',
            ],
            [
                'pertanyaan' => 'Manakah di antara alamat URL berikut yang merupakan situs resmi instansi pemerintah Indonesia?',
                'pilihan_json' => json_encode([
                    'A' => 'https://bantuan-kemensos.blogspot.com',
                    'B' => 'https://kemendikdasmen.go.id',
                    'C' => 'http://cek-bansos-resmi.my.id',
                    'D' => 'https://pendaftaran-cpns.net',
                ]),
                'jawaban_benar' => 'B',
                'kategori' => 'Link or Trap',
                'tingkat' => 'easy',
                'game_level' => 'link-trap',
            ],
            [
                'pertanyaan' => 'Perhatikan tautan berikut: "https://klikbca.com.login-session-id.top/auth". Mengapa tautan tersebut berbahaya?',
                'pilihan_json' => json_encode([
                    'A' => 'Karena menggunakan protokol HTTPS',
                    'B' => 'Domain utamanya sebenarnya adalah login-session-id.top, bukan klikbca.com (Subdomain Trick)',
                    'C' => 'Karena terlalu pendek',
                    'D' => 'Karena tidak memiliki gambar logo',
                ]),
                'jawaban_benar' => 'B',
                'kategori' => 'Link or Trap',
                'tingkat' => 'medium',
                'game_level' => 'link-trap',
            ],
            [
                'pertanyaan' => 'Teknik penipuan domain yang mengganti huruf "l" kecil dengan angka "1" atau huruf Cyrillic serupa seperti "paypa1.com" disebut...',
                'pilihan_json' => json_encode([
                    'A' => 'SQL Injection',
                    'B' => 'Typosquatting / Homograph Attack',
                    'C' => 'Cross-Site Scripting',
                    'D' => 'Brute Force',
                ]),
                'jawaban_benar' => 'B',
                'kategori' => 'Link or Trap',
                'tingkat' => 'hard',
                'game_level' => 'link-trap',
            ],
        ];

        foreach ($sampleSoal as $soal) {
            SoalMinigame::updateOrCreate(
                ['pertanyaan' => $soal['pertanyaan']],
                $soal
            );
        }

        // data diskusi forum kontekstual
        $sampleForums = [
            1 => [
                'user_id' => $owner->id,
                'judul' => 'Cara membedakan pesan kurir paket asli dengan modus file APK?',
                'pertanyaan' => 'Kemarin saya menerima pesan WhatsApp dari nomor tidak dikenal yang mengirim file LIHAT-FOTO-PAKET.apk. Bagaimana langkah aman jika terlanjur mengunduh tetapi belum menginstalnya?',
                'kategori' => 'Phishing',
                'status' => 'terjawab',
                'is_starred' => true,
                'views' => 42,
            ],
            2 => [
                'user_id' => 4,
                'judul' => 'Apakah aman menggunakan Wi-Fi publik di taman kota untuk transaksi mobile banking?',
                'pertanyaan' => 'Sering kali saat sedang di luar rumah saya menggunakan Wi-Fi gratis untuk membuka aplikasi perbankan. Apa risiko keamanan utamanya dan bagaimana cara mengatasinya?',
                'kategori' => 'Privasi',
                'status' => 'terjawab',
                'is_starred' => false,
                'views' => 68,
            ],
            3 => [
                'user_id' => 5,
                'judul' => 'Akun Instagram tiba-tiba mengirim DM tautan hadiah ke semua teman',
                'pertanyaan' => 'Teman-teman saya memberi tahu bahwa akun Instagram saya mengirim pesan link phishing secara otomatis. Bagaimana cara memulihkan akun dan mencabut akses aplikasi pihak ketiga?',
                'kategori' => 'Malware',
                'status' => 'terjawab',
                'is_starred' => false,
                'views' => 95,
            ],
            4 => [
                'user_id' => 3,
                'judul' => 'Mengenali telepon penipuan yang mengaku petugas bank meminta kode OTP',
                'pertanyaan' => 'Orang tua saya hampir tertipu telepon yang menyebutkan adanya biaya admin bulanan baru dan meminta 6 digit kode SMS. Bagaimana cara mengedukasi keluarga di rumah agar tidak mudah panik?',
                'kategori' => 'Penipuan',
                'status' => 'terjawab',
                'is_starred' => false,
                'views' => 53,
            ],
            5 => [
                'user_id' => 6,
                'judul' => 'Rekomendasi pengelola kata sandi (Password Manager) dan aktivasi 2FA',
                'pertanyaan' => 'Saya masih sering menggunakan satu password yang sama untuk banyak akun email dan media sosial. Aplikasi autentikator 2FA apa yang paling mudah digunakan untuk pemula?',
                'kategori' => 'Tanya Pakar',
                'status' => 'terjawab',
                'is_starred' => false,
                'views' => 77,
            ],
        ];

        foreach ($sampleForums as $id => $data) {
            Forum::updateOrCreate(['id' => $id], $data);
        }

        $sampleAnswers = [
            1 => [
                'forum_id' => 1,
                'user_id' => $owner->id,
                'jawaban' => 'Jika file .apk baru terunduh namun belum diinstal, segera hapus file tersebut dari folder Download, pastikan opsi "Install Unknown Apps" pada browser/WhatsApp nonaktif, lalu periksa izin SMS di pengaturan ponsel Anda.',
                'is_admin' => 1,
            ],
            2 => [
                'forum_id' => 3,
                'user_id' => $admin->id,
                'jawaban' => 'Segera ganti password Instagram Anda, aktifkan Autentikasi Dua Faktor (2FA), lalu masuk ke menu Settings > Website Permissions > Apps and Websites untuk mencabut sesi perangkat atau aplikasi mencurigakan.',
                'is_admin' => 1,
            ],
            3 => [
                'forum_id' => 3,
                'user_id' => 4,
                'jawaban' => 'Saya pernah mengalami hal serupa! Jangan lupa cek juga bagian "Where You\'re Logged In" dan keluarkan semua perangkat yang tidak dikenali.',
                'is_admin' => 0,
            ],
            4 => [
                'forum_id' => 3,
                'user_id' => 5,
                'jawaban' => 'Terima kasih banyak sarannya! Setelah saya ganti sandi dan cabut akses sesi asing, pesan otomatisnya sudah berhenti.',
                'is_admin' => 0,
            ],
            5 => [
                'forum_id' => 2,
                'user_id' => $admin->id,
                'jawaban' => 'Sebaiknya hindari transaksi finansial di Wi-Fi publik tanpa enkripsi karena rawan penyadapan paket data (Man-in-the-Middle). Gunakan koneksi data seluler pribadi saat membuka mobile banking.',
                'is_admin' => 1,
            ],
            6 => [
                'forum_id' => 4,
                'user_id' => $admin->id,
                'jawaban' => 'Ingatkan keluarga bahwa pihak bank resmi tidak pernah meminta kode OTP, PIN, atau CVV dengan alasan apa pun. Jika menerima telepon mencurigakan, segera tutup dan hubungi Call Center resmi bank.',
                'is_admin' => 1,
            ],
            7 => [
                'forum_id' => 5,
                'user_id' => 4,
                'jawaban' => 'Untuk autentikator 2FA, Google Authenticator atau Bitwarden sangat ringan dan mudah dipakai oleh pelajar maupun orang tua.',
                'is_admin' => 0,
            ],
        ];

        foreach ($sampleAnswers as $id => $ans) {
            JawabanForum::updateOrCreate(['id' => $id], $ans);
        }

        // data testimoni kontekstual yang sudah disetujui (approved)
        $sampleTestimonis = [
            2 => [
                'user_id' => 1,
                'judul' => 'Sangat Membantu Mengenali Link Phishing',
                'cerita' => 'Simulasi Phishing Hunter di Digi-Safe membuat saya jauh lebih teliti memeriksa alamat domain sebelum klik tautan di grup WhatsApp warga.',
                'rating' => 5,
                'mood' => '5',
                'is_anonymous' => 0,
                'status_approval' => 'approved',
            ],
            6 => [
                'user_id' => 5,
                'judul' => 'Edukasi yang Mudah Dipahami Orang Tua',
                'cerita' => 'Setelah membaca modul dan berdiskusi di forum, keluarga saya di rumah tidak lagi mudah panik saat menerima telepon yang mengaku petugas bank.',
                'rating' => 5,
                'mood' => '5',
                'is_anonymous' => 0,
                'status_approval' => 'approved',
            ],
            7 => [
                'user_id' => 4,
                'judul' => 'Berhasil Memulihkan Akun Media Sosial',
                'cerita' => 'Panduan dari admin di Forum Komunitas sangat jelas. Saya berhasil mengamankan kembali akun yang sempat terhubung ke web phishing.',
                'rating' => 5,
                'mood' => '5',
                'is_anonymous' => 1,
                'status_approval' => 'approved',
            ],
            8 => [
                'user_id' => 6,
                'judul' => 'Minigame Seru & Bikin Sadar Siber',
                'cerita' => 'Main Link or Trap bareng teman-teman sekolah jadi cara seru belajar bedain domain resmi .go.id dan domain jebakan.',
                'rating' => 5,
                'mood' => '5',
                'is_anonymous' => 0,
                'status_approval' => 'approved',
            ],
            9 => [
                'user_id' => 3,
                'judul' => 'Ruang Tanya Jawab yang Aman',
                'cerita' => 'Fitur anonim dan respons cepat dari relawan membuat saya nyaman menceritakan kronologi penipuan digital tanpa rasa malu.',
                'rating' => 4,
                'mood' => '4',
                'is_anonymous' => 1,
                'status_approval' => 'approved',
            ],
        ];

        foreach ($sampleTestimonis as $id => $testi) {
            Testimoni::updateOrCreate(['id' => $id], $testi);
        }
    }
}
