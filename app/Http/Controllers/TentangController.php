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

        $cyberFacts = $isEn ? [
            'datasets' => [
                [
                    'key' => 'iasc',
                    'short' => 'OJK',
                    'source' => 'OJK IASC (2024-2026)',
                    'card_tag' => 'Financial Fraud Reports',
                    'card_metric' => 'IDR 9T+',
                    'card_sub' => 'Reported losses through late 2025',
                    'float_label' => 'Late Reporting Gap',
                    'float_value' => '80%',
                    'float_unit' => 'report after 12h',
                    'headline_label' => 'Verified Fraud Reports (OJK IASC)',
                    'headline_value' => '741,545+',
                    'headline_note' => 'IDR 724B-825B in linked funds frozen across 600,000+ blocked accounts.',
                    'bars' => [
                        ['label' => '2024', 'value' => '112K', 'pct' => 28, 'active' => false],
                        ['label' => 'Q2 25', 'value' => '248K', 'pct' => 44, 'active' => false],
                        ['label' => 'Q4 25', 'value' => '432K', 'pct' => 64, 'active' => false],
                        ['label' => '2026', 'value' => '741K+', 'pct' => 94, 'active' => true],
                        ['label' => 'Blocked', 'value' => '600K+', 'pct' => 76, 'active' => false],
                    ],
                ],
                [
                    'key' => 'bssn',
                    'short' => 'BSSN',
                    'source' => 'BSSN Annual Report (2025)',
                    'card_tag' => 'Dominant Threat Vector',
                    'card_metric' => '93.78%',
                    'card_sub' => 'Malware share of traffic anomalies',
                    'float_label' => 'Primary Attack Type',
                    'float_value' => 'Malware',
                    'float_unit' => 'Trojans & Botnets',
                    'headline_label' => 'National Cyber Traffic Anomalies',
                    'headline_value' => '5.2 Billion+',
                    'headline_note' => 'Recorded across Indonesian networks in 2025, targeting finance and public services.',
                    'bars' => [
                        ['label' => '2023', 'value' => '403M', 'pct' => 24, 'active' => false],
                        ['label' => 'Malware', 'value' => '93.8%', 'pct' => 94, 'active' => true],
                        ['label' => 'Trojan', 'value' => 'High', 'pct' => 72, 'active' => false],
                        ['label' => 'Web App', 'value' => 'Risk', 'pct' => 54, 'active' => false],
                        ['label' => 'Human', 'value' => 'Top Gap', 'pct' => 82, 'active' => false],
                    ],
                ],
                [
                    'key' => 'apjii',
                    'short' => 'APJII',
                    'source' => 'APJII Internet Survey (2025)',
                    'card_tag' => 'Connected Population',
                    'card_metric' => '221M+',
                    'card_sub' => 'Active internet users nationwide',
                    'float_label' => 'Most Active Cohort',
                    'float_value' => '89.12%',
                    'float_unit' => 'Millennials online',
                    'headline_label' => 'Indonesia Internet Penetration',
                    'headline_value' => '80.66%',
                    'headline_note' => 'Surveyed across 38 provinces; Gen Z reaches 87.8% while seniors remain vulnerable.',
                    'bars' => [
                        ['label' => 'Millennial', 'value' => '89.1%', 'pct' => 92, 'active' => false],
                        ['label' => 'Gen Z', 'value' => '87.8%', 'pct' => 88, 'active' => false],
                        ['label' => 'National', 'value' => '80.7%', 'pct' => 82, 'active' => true],
                        ['label' => 'Gen X', 'value' => '79.0%', 'pct' => 76, 'active' => false],
                        ['label' => 'Boomer', 'value' => '59.4%', 'pct' => 58, 'active' => false],
                    ],
                ],
            ],
            'benchmarks' => [
                [
                    'value' => '741,545+',
                    'org' => 'OJK IASC (2024-2026)',
                    'desc' => 'Financial fraud reports logged nationwide',
                ],
                [
                    'value' => '5.2 Billion+',
                    'org' => 'BSSN (2025)',
                    'desc' => 'Cyber anomalies, 93.78% driven by malware',
                ],
                [
                    'value' => '80.66%',
                    'org' => 'APJII (2025)',
                    'desc' => 'National internet penetration across 38 provinces',
                ],
            ],
            'riskBars' => [
                ['label' => 'Malware', 'value' => '93.8%', 'pct' => 94, 'source' => 'BSSN', 'highlight' => false],
                ['label' => 'Millennial', 'value' => '89.1%', 'pct' => 89, 'source' => 'APJII', 'highlight' => false],
                ['label' => 'Late >12h', 'value' => '80.0%', 'pct' => 80, 'source' => 'IASC', 'highlight' => true],
                ['label' => 'Gen Z', 'value' => '87.8%', 'pct' => 86, 'source' => 'APJII', 'highlight' => false],
                ['label' => 'Seniors', 'value' => '59.4%', 'pct' => 59, 'source' => 'APJII', 'highlight' => false],
            ],
            'checklist' => [
                '15 branching WhatsApp and malicious .APK chat scenarios',
                'Forensic URL and QR code phishing scanner',
                'Advanced daily challenges with weekly streak tracking',
                'Local community scam reports and verified discussions',
            ],
        ] : [
            'datasets' => [
                [
                    'key' => 'iasc',
                    'short' => 'OJK',
                    'source' => 'OJK IASC (2024-2026)',
                    'card_tag' => 'Laporan Penipuan Keuangan',
                    'card_metric' => 'Rp9 T+',
                    'card_sub' => 'Kerugian terlapor hingga akhir 2025',
                    'float_label' => 'Keterlambatan Lapor',
                    'float_value' => '80%',
                    'float_unit' => 'lapor >12 jam',
                    'headline_label' => 'Aduan Penipuan Tercatat (IASC OJK)',
                    'headline_value' => '741.545+',
                    'headline_note' => 'Dana Rp724-825 miliar berhasil diblokir dari 600.000+ rekening terindikasi penipuan.',
                    'bars' => [
                        ['label' => '2024', 'value' => '112 rb', 'pct' => 28, 'active' => false],
                        ['label' => 'Q2 25', 'value' => '248 rb', 'pct' => 44, 'active' => false],
                        ['label' => 'Q4 25', 'value' => '432 rb', 'pct' => 64, 'active' => false],
                        ['label' => '2026', 'value' => '741 rb+', 'pct' => 94, 'active' => true],
                        ['label' => 'Blokir', 'value' => '600 rb+', 'pct' => 76, 'active' => false],
                    ],
                ],
                [
                    'key' => 'bssn',
                    'short' => 'BSSN',
                    'source' => 'Laporan Tahunan BSSN (2025)',
                    'card_tag' => 'Dominasi Serangan Siber',
                    'card_metric' => '93,78%',
                    'card_sub' => 'Aktivitas malware di trafik anomali',
                    'float_label' => 'Ancaman Terbanyak',
                    'float_value' => 'Malware',
                    'float_unit' => 'Trojan & Botnet',
                    'headline_label' => 'Anomali Trafik Siber Indonesia',
                    'headline_value' => '5,2 Miliar+',
                    'headline_note' => 'Tercatat sepanjang 2025, menyasar sektor layanan publik, keuangan, dan warga.',
                    'bars' => [
                        ['label' => '2023', 'value' => '403 jt', 'pct' => 24, 'active' => false],
                        ['label' => 'Malware', 'value' => '93,8%', 'pct' => 94, 'active' => true],
                        ['label' => 'Trojan', 'value' => 'Tinggi', 'pct' => 72, 'active' => false],
                        ['label' => 'Web App', 'value' => 'Rentan', 'pct' => 54, 'active' => false],
                        ['label' => 'Manusia', 'value' => 'Celah', 'pct' => 82, 'active' => false],
                    ],
                ],
                [
                    'key' => 'apjii',
                    'short' => 'APJII',
                    'source' => 'Survei Internet APJII (2025)',
                    'card_tag' => 'Warga Terhubung Internet',
                    'card_metric' => '221 Juta+',
                    'card_sub' => 'Pengguna aktif internet di Indonesia',
                    'float_label' => 'Kelompok Paling Aktif',
                    'float_value' => '89,12%',
                    'float_unit' => 'Milenial online',
                    'headline_label' => 'Tingkat Penetrasi Internet Nasional',
                    'headline_value' => '80,66%',
                    'headline_note' => 'Survei 38 provinsi; Gen Z mencapai 87,8% sementara kelompok lansia kian terpapar.',
                    'bars' => [
                        ['label' => 'Milenial', 'value' => '89,1%', 'pct' => 92, 'active' => false],
                        ['label' => 'Gen Z', 'value' => '87,8%', 'pct' => 88, 'active' => false],
                        ['label' => 'Nasional', 'value' => '80,7%', 'pct' => 82, 'active' => true],
                        ['label' => 'Gen X', 'value' => '79,0%', 'pct' => 76, 'active' => false],
                        ['label' => 'Lansia', 'value' => '59,4%', 'pct' => 58, 'active' => false],
                    ],
                ],
            ],
            'benchmarks' => [
                [
                    'value' => '741.545+',
                    'org' => 'OJK IASC (2024-2026)',
                    'desc' => 'Aduan penipuan transaksi keuangan nasional',
                ],
                [
                    'value' => '5,2 Miliar+',
                    'org' => 'BSSN (2025)',
                    'desc' => 'Anomali trafik siber, 93,78% berupa malware',
                ],
                [
                    'value' => '80,66%',
                    'org' => 'APJII (2025)',
                    'desc' => 'Penetrasi internet nasional di 38 provinsi',
                ],
            ],
            'riskBars' => [
                ['label' => 'Malware', 'value' => '93,8%', 'pct' => 94, 'source' => 'BSSN', 'highlight' => false],
                ['label' => 'Milenial', 'value' => '89,1%', 'pct' => 89, 'source' => 'APJII', 'highlight' => false],
                ['label' => 'Lapor >12j', 'value' => '80,0%', 'pct' => 80, 'source' => 'IASC', 'highlight' => true],
                ['label' => 'Gen Z', 'value' => '87,8%', 'pct' => 86, 'source' => 'APJII', 'highlight' => false],
                ['label' => 'Lansia', 'value' => '59,4%', 'pct' => 59, 'source' => 'APJII', 'highlight' => false],
            ],
            'checklist' => [
                'Simulasi 15 skenario chat WhatsApp dan jebakan file .APK',
                'Pemindai tautan mencurigakan dan gambar QR Code phishing',
                'Tantangan harian tingkat lanjut dengan sistem streak mingguan',
                'Forum laporan warga Purwakarta dan diskusi terverifikasi',
            ],
        ];

        return view('tentang.index', compact('developer', 'visiMisi', 'cyberFacts'));
    }
}
