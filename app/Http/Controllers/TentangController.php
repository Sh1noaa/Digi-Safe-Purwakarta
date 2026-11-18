<?php

namespace App\Http\Controllers;

use App\Services\BilingualHelper;
use Illuminate\View\View;

class TentangController extends Controller
{
    public function index(): View
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

        return view('tentang.index', compact('developer', 'visiMisi'));
    }
}
