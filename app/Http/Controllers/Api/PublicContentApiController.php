<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EdukasiRequest;
use App\Models\ModulEdukasi;
use App\Models\Testimoni;
use App\Models\User;
use App\Services\ApiResourceFormatter;
use App\Services\BilingualHelper;
use App\Services\LeaderboardService;
use App\Services\SocialSchemaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicContentApiController extends Controller
{
    public function beranda(): JsonResponse
    {
        $isEn = BilingualHelper::isEnglish();
        $viewer = Auth::user();

        $aboutStats = $isEn ? [
            'mitra_count' => '120+',
            'mitra_desc' => 'Partnering with schools, universities, and village communities across Purwakarta Regency.',
            'komitmen_persen' => '100%',
            'komitmen_label' => 'Commitment to measurable literacy',
            'komitmen_quote' => '“Digi-Safe security simulations transformed how our citizens and students spot phishing links. The materials are practical, smart, and easy to understand.”',
            'modul_interaksi' => '520k+',
            'modul_label' => 'Module & Quiz Interactions',
            'modul_desc' => 'Completed regularly to strengthen community digital resilience.',
            'kecamatan_count' => '20+',
            'kecamatan_label' => 'Districts & Communities',
        ] : [
            'mitra_count' => '120+',
            'mitra_desc' => 'Bekerja sama dengan sekolah, kampus, dan komunitas desa di seluruh Kabupaten Purwakarta.',
            'komitmen_persen' => '100%',
            'komitmen_label' => 'Komitmen pada literasi terukur',
            'komitmen_quote' => '“Simulasi keamanan Digi-Safe mengubah cara warga dan pelajar kami mengenali tautan phishing. Materinya praktis, cerdas, dan mudah dipahami.”',
            'modul_interaksi' => '520k+',
            'modul_label' => 'Interaksi Modul & Kuis',
            'modul_desc' => 'Diselesaikan secara berkala untuk memperkuat ketahanan digital masyarakat.',
            'kecamatan_count' => '20+',
            'kecamatan_label' => 'Kecamatan & Komunitas',
        ];

        $services = $isEn ? [
            [
                'slug' => 'edukasi',
                'title' => 'Education Modules',
                'description' => 'Concise, practical materials to recognize cyber fraud schemes, protect data privacy, and secure digital banking accounts.',
                'icon' => 'modul',
            ],
            [
                'slug' => 'minigame',
                'title' => 'Minigames & Quizzes',
                'description' => 'Train your instincts to detect fake messages, malicious links, and social engineering via real-world interactive simulations.',
                'icon' => 'kuis',
            ],
            [
                'slug' => 'forum',
                'title' => 'Discussion Forum',
                'description' => 'Report newly discovered scams, discuss directly with cybersecurity practitioners, and build a mutually protective cyber community.',
                'icon' => 'forum',
            ],
        ] : [
            [
                'slug' => 'edukasi',
                'title' => 'Modul Edukasi',
                'description' => 'Materi ringkas dan aplikatif untuk mengenali modus penipuan siber, menjaga privasi data, serta mengamankan akun perbankan digital.',
                'icon' => 'modul',
            ],
            [
                'slug' => 'minigame',
                'title' => 'Minigame & Kuis',
                'description' => 'Latih kepekaan Anda mendeteksi pesan palsu, tautan berbahaya, dan rekayasa sosial melalui simulasi interaktif berbasis skenario nyata.',
                'icon' => 'kuis',
            ],
            [
                'slug' => 'forum',
                'title' => 'Forum Diskusi',
                'description' => 'Laporkan temuan modus baru, berdiskusi langsung dengan praktisi keamanan informasi, dan bangun jejaring warga digital yang saling menjaga.',
                'icon' => 'forum',
            ],
        ];

        $faqGroups = $this->buildFaqGroups($isEn);

        $registeredUsers = User::humans()->withCount(['skorGames', 'forums', 'jawabanForums', 'testimonis'])
            ->withSum('skorGames as total_skor_game', 'skor')
            ->withMax('skorGames as top_skor_game', 'skor_tertinggi')
            ->orderBy('id')
            ->get()
            ->map(fn (User $u) => ApiResourceFormatter::formatUser($u, $viewer, true))
            ->values();

        $apkFile = public_path('downloads/digi-safe-purwakarta.apk');
        $apkUrl = is_file($apkFile) ? asset('downloads/digi-safe-purwakarta.apk') : null;

        return response()->json([
            'success' => true,
            'locale' => app()->getLocale(),
            'about_stats' => $aboutStats,
            'services' => $services,
            'faq_groups' => $faqGroups,
            'registered_users_count' => $registeredUsers->count(),
            'registered_users' => $registeredUsers,
            'apk_download_url' => $apkUrl,
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $viewer = Auth::user();
        $search = trim((string) $request->query('q', ''));

        $query = User::humans()->withCount(['skorGames', 'forums', 'jawabanForums', 'testimonis'])
            ->withSum('skorGames as total_skor_game', 'skor')
            ->withMax('skorGames as top_skor_game', 'skor_tertinggi')
            ->orderBy('id');

        if ($search !== '') {
            $escaped = str_replace(['\\', '%'], ['\\\\', '\\%'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('nama_lengkap', 'like', "%{$escaped}%")
                    ->orWhere('username', 'like', "%{$escaped}%");
            });
        }

        $users = $query->get()
            ->map(fn (User $u) => ApiResourceFormatter::formatUser($u, $viewer, true))
            ->values();

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    public function userProfile(string $identifier): JsonResponse
    {
        $viewer = Auth::user();
        $user = User::query()
            ->where('id', $identifier)
            ->orWhere('username', $identifier)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'user' => ApiResourceFormatter::formatUser($user, $viewer, true),
        ]);
    }

    public function edukasi(): JsonResponse
    {
        $moduls = ModulEdukasi::orderBy('id')->get();
        $cards = $moduls->values()
            ->map(fn (ModulEdukasi $modul, int $idx) => ApiResourceFormatter::formatModule($modul, $idx))
            ->values();

        return response()->json([
            'success' => true,
            'locale' => app()->getLocale(),
            'count' => $cards->count(),
            'modules' => $cards,
        ]);
    }

    public function tentang(): JsonResponse
    {
        $isEn = BilingualHelper::isEnglish();

        $developer = [
            'nama' => 'Razief Afkar Rusli',
            'inisial' => 'RA',
            'asal' => $isEn ? '12th Grade Software Engineering, SMKN 2 Purwakarta' : 'Kelas XII RPL, SMK Negeri 2 Purwakarta',
            'deskripsi' => $isEn
                ? 'Developer behind the Digi-Safe platform. A student passionate about cybersecurity and building technology that protects local communities.'
                : 'Pengembang di balik platform Digi-Safe. Seorang pelajar yang peduli dengan keamanan digital dan ingin berkontribusi melalui teknologi.',
            'socials' => [
                [
                    'label' => 'Instagram',
                    'handle' => '@rzifafkarr',
                    'url' => 'https://instagram.com/rzifafkarr',
                    'type' => 'instagram',
                ],
                [
                    'label' => 'TikTok',
                    'handle' => '@rzfffff',
                    'url' => 'https://tiktok.com/@rzfffff',
                    'type' => 'tiktok',
                ],
                [
                    'label' => 'Website / Blog',
                    'handle' => 'raziefafkar.vercel.app',
                    'url' => 'https://raziefafkar.vercel.app',
                    'type' => 'web',
                ],
            ],
        ];

        $visiMisi = $isEn ? [
            [
                'tahap' => '01',
                'judul' => 'Inclusive & Practical Education',
                'deskripsi' => 'Simplifying cybersecurity concepts so students, parents, and local officials can apply them effortlessly.',
            ],
            [
                'tahap' => '02',
                'judul' => 'Real-World Case Simulations',
                'deskripsi' => 'Training quick reflexes to detect the latest scam tactics through interactive minigames with zero real-world risk.',
            ],
            [
                'tahap' => '03',
                'judul' => 'Community-Driven Protection',
                'deskripsi' => 'Building an open discussion space where citizens report emerging cyber threats and share verified solutions.',
            ],
        ] : [
            [
                'tahap' => '01',
                'judul' => 'Edukasi yang Inklusif & Aplikatif',
                'deskripsi' => 'Menyederhanakan konsep keamanan siber agar mudah dipahami oleh pelajar, orang tua, hingga perangkat desa di Purwakarta.',
            ],
            [
                'tahap' => '02',
                'judul' => 'Simulasi Berbasis Kasus Nyata',
                'deskripsi' => 'Melatih refleks pengguna dalam mengenali modus penipuan terbaru melalui minigame interaktif tanpa risiko nyata.',
            ],
            [
                'tahap' => '03',
                'judul' => 'Ekosistem Warga Saling Menjaga',
                'deskripsi' => 'Membangun ruang diskusi terbuka tempat masyarakat dapat melaporkan temuan ancaman baru dan memperoleh solusi terverifikasi.',
            ],
        ];

        return response()->json([
            'success' => true,
            'developer' => $developer,
            'visi_misi' => $visiMisi,
            'location' => [
                'name' => 'SMK Negeri 2 Purwakarta',
                'address' => 'Jl. Jend. Ahmad Yani No. 98, Purwakarta, Jawa Barat',
                'maps_url' => 'https://maps.google.com/?q=SMKN+2+Purwakarta',
            ],
        ]);
    }

    public function testimoni(): JsonResponse
    {
        $items = Testimoni::with('user')
            ->whereIn('status_approval', ['approved', 'pending'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Testimoni $t) => ApiResourceFormatter::formatTestimoni($t))
            ->values();

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'testimonis' => $items,
        ]);
    }

    public function storeTestimoni(Request $request): JsonResponse
    {
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'cerita' => ['required', 'string', 'max:1500'],
            'is_anonymous' => ['nullable', 'boolean'],
        ], [
            'rating.required' => $isEn ? 'Please select a star rating first.' : 'Silakan pilih rating bintang terlebih dahulu.',
            'cerita.required' => $isEn ? 'Your experience story cannot be empty.' : 'Cerita pengalaman tidak boleh kosong.',
        ]);

        $testimoni = Testimoni::create([
            'user_id' => Auth::id(),
            'judul' => mb_substr(trim($validated['cerita']), 0, 50),
            'cerita' => $validated['cerita'],
            'rating' => (int) $validated['rating'],
            'mood' => (string) $validated['rating'],
            'is_anonymous' => $request->boolean('is_anonymous') ? 1 : 0,
            'status_approval' => 'approved',
            'created_at' => now(),
        ]);

        $testimoni->load('user');

        return response()->json([
            'success' => true,
            'message' => $isEn
                ? 'Thank you! Your review has been published and is now live.'
                : 'Terima kasih! Ulasanmu telah diterbitkan dan langsung tampil.',
            'item' => ApiResourceFormatter::formatTestimoni($testimoni),
        ], 201);
    }

    public function leaderboard(): JsonResponse
    {
        $data = LeaderboardService::allCategories(10);

        return response()->json([
            'success' => true,
            'locale' => app()->getLocale(),
            'categories' => $data['categories'] ?? [],
        ]);
    }

    public function myEdukasiRequests(): JsonResponse
    {
        SocialSchemaHelper::ensureReady();

        $requests = EdukasiRequest::where('user_id', Auth::id())
            ->latest('id')
            ->limit(25)
            ->get()
            ->map(fn (EdukasiRequest $r) => [
                'id' => (int) $r->id,
                'tema' => $r->tema,
                'kategori' => $r->kategori,
                'preferred_type' => $r->preferred_type ?: 'teks',
                'deskripsi' => $r->deskripsi,
                'status' => $r->status,
                'admin_note' => $r->admin_note,
                'user_notified_read' => (bool) $r->user_notified_read,
                'created_at' => $r->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') . ($r->created_at ? ' WIB' : ''),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'requests' => $requests,
            'unread_decisions' => $requests->whereIn('status', ['approved', 'rejected'])->where('user_notified_read', false)->count(),
        ]);
    }

    public function storeEdukasiRequest(Request $request): JsonResponse
    {
        SocialSchemaHelper::ensureReady();
        $isEn = app()->getLocale() === 'en';

        $validated = $request->validate([
            'tema' => ['required', 'string', 'min:4', 'max:180'],
            'kategori' => ['required', 'string', 'max:80'],
            'preferred_type' => ['nullable', 'string', 'in:teks,link'],
            'deskripsi' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $reqItem = EdukasiRequest::create([
            'user_id' => Auth::id(),
            'tema' => trim($validated['tema']),
            'kategori' => trim($validated['kategori']),
            'preferred_type' => $validated['preferred_type'] ?? 'teks',
            'deskripsi' => trim($validated['deskripsi']),
            'status' => 'pending',
            'user_notified_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $isEn
                ? 'Your education topic request has been sent! Bot Digika will message you once the Admin team reviews it.'
                : 'Request tema edukasi berhasil dikirim! Tim Admin akan mengevaluasinya dan Bot Digika akan mengirim pesan otomatis kepada Anda.',
            'request' => [
                'id' => (int) $reqItem->id,
                'tema' => $reqItem->tema,
                'kategori' => $reqItem->kategori,
                'preferred_type' => $reqItem->preferred_type,
                'deskripsi' => $reqItem->deskripsi,
                'status' => $reqItem->status,
                'admin_note' => null,
                'user_notified_read' => false,
                'created_at' => $reqItem->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
            ],
        ], 201);
    }

    public function markEdukasiRequestsSeen(): JsonResponse
    {
        SocialSchemaHelper::ensureReady();

        EdukasiRequest::where('user_id', Auth::id())
            ->whereIn('status', ['approved', 'rejected'])
            ->where('user_notified_read', false)
            ->update(['user_notified_read' => true]);

        return response()->json(['success' => true]);
    }

    private function buildFaqGroups(bool $isEn): array
    {
        return $isEn ? [
            'pelajar' => [
                [
                    'q' => 'What is Digi-Safe Purwakarta and how does it help students learn?',
                    'a' => 'Digi-Safe Purwakarta is an interactive cybersecurity literacy platform built to help students and young citizens spot online scams, phishing links, fake giveaways, and malware safely before encountering real threats.',
                ],
                [
                    'q' => 'Are the learning modules and minigames free to access?',
                    'a' => 'Yes! All reading modules in the Education Center and community forums are completely free. Signing in unlocks all tiered minigame levels, score tracking, achievement badges, and Leaderboard rankings.',
                ],
                [
                    'q' => 'How is Digi-Safe different from general articles on the internet?',
                    'a' => 'Instead of passive theory, Digi-Safe trains your reflexes using real Indonesian & Purwakarta case simulations—such as fake social aid domains, WhatsApp APK traps, and deceptive banking alerts.',
                ],
                [
                    'q' => 'Can I switch the minigame challenges and app to English or Indonesian?',
                    'a' => 'Absolutely! Open Profile Settings anytime to toggle seamlessly between Bahasa Indonesia and English, as well as Light or Dark mode.',
                ],
            ],
            'orangtua' => [
                [
                    'q' => 'How can Digi-Safe help parents protect children online?',
                    'a' => 'Digi-Safe equips parents with practical guides on screen habits, suspicious APK files, social engineering on chat apps, and checklists to safeguard family accounts.',
                ],
                [
                    'q' => 'Is personal information safe on Digi-Safe?',
                    'a' => 'Yes. Inside Profile Settings, every user can hide their location/bio, email address, stats, or enable Private Account mode.',
                ],
                [
                    'q' => 'What should we do if we receive a suspicious WhatsApp file or bank link?',
                    'a' => 'Never install unknown APK files or enter OTP/PIN codes. Check our Education modules first or post the situation inside the Community Forum.',
                ],
            ],
            'komunitas' => [
                [
                    'q' => 'How can teachers or schools use Digi-Safe in class?',
                    'a' => 'Teachers can assign Education reading modules alongside tiered Minigame levels (Levels 1–5) as hands-on classroom exercises.',
                ],
                [
                    'q' => 'How does the Most Helpful feature in the Forum work?',
                    'a' => 'Signed-in members can react once per reply with 👍 or 👎. Replies with the highest net helpful votes automatically earn the real-time Most Helpful badge.',
                ],
                [
                    'q' => 'Do updates from Admin appear automatically in the APK?',
                    'a' => 'Yes! Education modules, minigame questions, levels, forum posts, and reviews sync in real time with the server database.',
                ],
            ],
        ] : [
            'pelajar' => [
                [
                    'q' => 'Apa itu Digi-Safe Purwakarta dan bagaimana ia membantuku belajar keamanan digital?',
                    'a' => 'Digi-Safe Purwakarta adalah platform edukasi keamanan siber interaktif yang membantu pelajar dan generasi muda mengenali tautan phishing, pesan penipuan, hoaks, serta ancaman malware sebelum mengalaminya langsung.',
                ],
                [
                    'q' => 'Apakah modul edukasi dan seluruh level minigame bisa diakses gratis?',
                    'a' => 'Ya! Seluruh modul bacaan dan forum diskusi gratis diakses. Setelah masuk akun gratis, kamu bisa memainkan seluruh level minigame, menyimpan riwayat skor, mengoleksi lencana achievement, dan masuk ke Leaderboard.',
                ],
                [
                    'q' => 'Apa bedanya latihan di Digi-Safe dengan membaca artikel biasa di internet?',
                    'a' => 'Di Digi-Safe kamu tidak hanya membaca teori, tetapi langsung melatih insting melalui simulasi kasus nyata—mulai dari modus undangan APK, link bansos palsu, hingga jebakan domain tiruan dengan pembahasan instan.',
                ],
                [
                    'q' => 'Apakah soal minigame dan tampilan bisa diganti ke Bahasa Inggris atau mode gelap?',
                    'a' => 'Tentu bisa! Buka menu Profil Saya lalu klik Pengaturan untuk mengganti bahasa (Indonesia / English), mode layar (Terang / Gelap), serta kontrol privasi kapan saja.',
                ],
            ],
            'orangtua' => [
                [
                    'q' => 'Bagaimana Digi-Safe membantu orang tua melindungi keluarga di dunia maya?',
                    'a' => 'Digi-Safe menyediakan panduan praktis bagi orang tua untuk memahami bahaya file APK asing, rekayasa sosial di grup chat keluarga, pencurian OTP perbankan, serta kebiasaan digital sehat.',
                ],
                [
                    'q' => 'Apakah data pribadi atau alamat rumah kami aman jika mendaftar?',
                    'a' => 'Aman sekali. Di Pengaturan Profil tersedia opsi untuk menyembunyikan alamat/bio, email, statistik skor, hingga Mode Akun Privat.',
                ],
                [
                    'q' => 'Apa yang harus dilakukan jika menerima link hadiah atau file .apk mencurigakan?',
                    'a' => 'Jangan pernah klik tautan atau memasang file dari pengirim tak dikenal. Anda dapat menanyakannya di Forum Komunitas Digi-Safe untuk dibantu pengecekan bersama.',
                ],
            ],
            'komunitas' => [
                [
                    'q' => 'Bagaimana guru atau sekolah dapat memanfaatkan Digi-Safe untuk kegiatan belajar?',
                    'a' => 'Guru dapat menugaskan siswa membaca kurikulum di halaman Edukasi lalu mempraktikkannya lewat Level 1–5 pada Minigame Phishing Hunter, Cyber Defender, dan Link or Trap.',
                ],
                [
                    'q' => 'Bagaimana sistem penilaian "Most Helpful" di Forum Komunitas bekerja?',
                    'a' => 'Setiap pengguna terdaftar dapat memberikan maksimal satu reaksi 👍 atau 👎 per jawaban. Balasan dengan dukungan positif terbanyak otomatis mendapat label Most Helpful secara real-time.',
                ],
                [
                    'q' => 'Apakah jika Admin mengupload edukasi/soal di website, APK langsung ikut terupdate?',
                    'a' => 'Ya! Aplikasi Android Digi-Safe terhubung langsung via REST API ke server yang sama, sehingga setiap upload modul edukasi, level, maupun soal baru dari Admin langsung muncul di APK.',
                ],
            ],
        ];
    }
}

