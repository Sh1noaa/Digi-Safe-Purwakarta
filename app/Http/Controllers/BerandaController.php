<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BilingualHelper;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function index(): View
    {
        $isEn = BilingualHelper::isEnglish();

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

        $registeredUsers = User::withCount(['forums', 'jawabanForums', 'testimonis'])
            ->withSum('skorGames as total_skor_game', 'skor')
            ->orderBy('id')
            ->get();

        return view('beranda', compact('aboutStats', 'services', 'registeredUsers'));
    }

    public function usersDirectory(Request $request): View
    {
        $users = User::withCount(['forums', 'jawabanForums', 'testimonis'])
            ->withSum('skorGames as total_skor_game', 'skor')
            ->orderBy('id')
            ->get();

        return view('users.index', compact('users'));
    }
}
