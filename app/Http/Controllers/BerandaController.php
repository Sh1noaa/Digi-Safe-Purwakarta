<?php

namespace App\Http\Controllers;

use App\Models\Testimoni;
use App\Models\User;
use App\Models\UserFollow;
use App\Services\BilingualHelper;
use App\Services\SocialSchemaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function index(): View
    {
        SocialSchemaHelper::ensureReady();
        $isEn = BilingualHelper::isEnglish();

        $hasTestimoni = Schema::hasTable('testimoni');
        $hasUsers = Schema::hasTable('users');

        // Ambil ulasan nyata dari database testimoni (hindari teks default hardcoded yang tidak sesuai)
        $realReviews = $hasTestimoni
            ? Testimoni::with('user')
                ->whereIn('status_approval', ['approved', 'pending'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(24)
                ->get()
                ->filter(fn (Testimoni $t) => mb_strlen(trim((string) $t->cerita)) >= 12)
                ->take(6)
                ->values()
            : collect();

        $featuredReview = $realReviews->first();
        $totalReviewsCount = $hasTestimoni
            ? Testimoni::whereIn('status_approval', ['approved', 'pending'])->count()
            : 0;

        $aboutStats = $isEn ? [
            'mitra_count' => '120+',
            'mitra_desc' => 'Partnering with schools, universities, and village communities across Purwakarta Regency.',
            'komitmen_persen' => '100%',
            'komitmen_label' => 'Commitment to measurable literacy',
            'komitmen_quote' => $featuredReview
                ? '“' . trim($featuredReview->cerita) . '”'
                : '“Share your experience on the Testimonials page so fellow Purwakarta citizens can learn from real stories.”',
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
            'komitmen_quote' => $featuredReview
                ? '“' . trim($featuredReview->cerita) . '”'
                : '“Bagikan pengalaman belajar Anda di halaman Testimoni agar warga Purwakarta lainnya dapat membaca ulasan nyata.”',
            'modul_interaksi' => '520k+',
            'modul_label' => 'Interaksi Modul & Kuis',
            'modul_desc' => 'Diselesaikan secara berkala untuk memperkuat ketahanan digital masyarakat.',
            'kecamatan_count' => '20+',
            'kecamatan_label' => 'Kecamatan & Komunitas',
        ];

        $services = $isEn ? [
            [
                'title' => 'Education Modules',
                'description' => 'Concise, practical materials to recognize cyber fraud schemes, protect data privacy, and secure digital banking accounts.',
                'icon' => 'modul',
                'url' => url('/edukasi'),
            ],
            [
                'title' => 'Minigames & Quizzes',
                'description' => 'Train your instincts to detect fake messages, malicious links, and social engineering via real-world interactive simulations.',
                'icon' => 'kuis',
                'url' => url('/minigame'),
            ],
            [
                'title' => 'Discussion Forum',
                'description' => 'Report newly discovered scams, discuss directly with cybersecurity practitioners, and build a mutually protective cyber community.',
                'icon' => 'forum',
                'url' => url('/forum'),
            ],
        ] : [
            [
                'title' => 'Modul Edukasi',
                'description' => 'Materi ringkas dan aplikatif untuk mengenali modus penipuan siber, menjaga privasi data, serta mengamankan akun perbankan digital.',
                'icon' => 'modul',
                'url' => url('/edukasi'),
            ],
            [
                'title' => 'Minigame & Kuis',
                'description' => 'Latih kepekaan Anda mendeteksi pesan palsu, tautan berbahaya, dan rekayasa sosial melalui simulasi interaktif berbasis skenario nyata.',
                'icon' => 'kuis',
                'url' => url('/minigame'),
            ],
            [
                'title' => 'Forum Diskusi',
                'description' => 'Laporkan temuan modus baru, berdiskusi langsung dengan praktisi keamanan informasi, dan bangun jejaring warga digital yang saling menjaga.',
                'icon' => 'forum',
                'url' => url('/forum'),
            ],
        ];

        $registeredUsers = ($hasUsers && Schema::hasTable('forum') && Schema::hasTable('jawaban_forum') && Schema::hasTable('skor_game') && $hasTestimoni)
            ? User::humans()
                ->withCount(['forums', 'jawabanForums', 'testimonis', 'followers', 'following'])
                ->withSum('skorGames as total_skor_game', 'skor')
                ->orderBy('id')
                ->get()
            : ($hasUsers ? User::humans()->orderBy('id')->get() : collect());

        $followingIds = Auth::check()
            ? UserFollow::where('follower_id', Auth::id())->pluck('following_id')->map(fn ($id) => (int) $id)->all()
            : [];

        return view('beranda', compact('aboutStats', 'services', 'registeredUsers', 'followingIds', 'realReviews', 'featuredReview', 'totalReviewsCount'));
    }

    public function usersDirectory(Request $request): View
    {
        SocialSchemaHelper::ensureReady();

        $users = User::humans()
            ->withCount(['forums', 'jawabanForums', 'testimonis', 'followers', 'following'])
            ->withSum('skorGames as total_skor_game', 'skor')
            ->orderBy('id')
            ->get();

        $followingIds = Auth::check()
            ? UserFollow::where('follower_id', Auth::id())->pluck('following_id')->map(fn ($id) => (int) $id)->all()
            : [];

        return view('users.index', compact('users', 'followingIds'));
    }
}
