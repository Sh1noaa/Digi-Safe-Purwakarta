<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MinigameLevel extends Model
{
    protected $table = 'minigame_levels';

    public $timestamps = false;

    protected $fillable = [
        'slug',
        'nama_level',
        'badge_label',
        'deskripsi',
        'waktu_detik',
        'poin_benar',
        'penalti_salah',
        'hp_awal',
        'urutan',
        'warna',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'waktu_detik' => 'integer',
            'poin_benar' => 'integer',
            'penalti_salah' => 'integer',
            'hp_awal' => 'integer',
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getBadgeClassesAttribute(): string
    {
        return match ($this->warna) {
            'sky' => 'bg-[#DBEAFE] text-sky border-sky/30',
            'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
            'purple' => 'bg-purple-50 text-purple-700 border-purple-200',
            'red' => 'bg-red-50 text-red-600 border-red-200',
            'lime' => 'bg-lime/40 text-ink border-lime',
            default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }

    /**
     * Pastikan tabel minigame_levels dan 5 level standar tersedia di database.
     */
    public static function ensureInitialized(): void
    {
        try {
            if (! Schema::hasTable('minigame_levels')) {
                Schema::create('minigame_levels', function (Blueprint $table) {
                    $table->id();
                    $table->string('slug', 50)->unique();
                    $table->string('nama_level', 100);
                    $table->string('badge_label', 60)->default('LEVEL');
                    $table->text('deskripsi')->nullable();
                    $table->integer('waktu_detik')->default(15);
                    $table->integer('poin_benar')->default(10);
                    $table->integer('penalti_salah')->default(5);
                    $table->integer('hp_awal')->default(3);
                    $table->integer('urutan')->default(1);
                    $table->string('warna', 30)->default('emerald');
                    $table->boolean('is_active')->default(true);
                });
            }

            if (static::query()->count() === 0) {
                $defaults = [
                    [
                        'slug' => 'easy',
                        'nama_level' => 'Pemula Siber',
                        'badge_label' => 'LEVEL 1 • EASY',
                        'deskripsi' => 'Ciri penipuan dasar seperti hadiah undian palsu, file APK kurir, dan tautan mencurigakan.',
                        'waktu_detik' => 15,
                        'poin_benar' => 10,
                        'penalti_salah' => 5,
                        'hp_awal' => 3,
                        'urutan' => 1,
                        'warna' => 'emerald',
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'medium',
                        'nama_level' => 'Waspada Menengah',
                        'badge_label' => 'LEVEL 2 • MEDIUM',
                        'deskripsi' => 'Samaran domain mirip instansi resmi, tagihan palsu, dan serangan malware jaringan.',
                        'waktu_detik' => 13,
                        'poin_benar' => 15,
                        'penalti_salah' => 5,
                        'hp_awal' => 3,
                        'urutan' => 2,
                        'warna' => 'sky',
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'hard',
                        'nama_level' => 'Detektif Siber',
                        'badge_label' => 'LEVEL 3 • HARD',
                        'deskripsi' => 'Spear-phishing halus, manipulasi psikologis, dan gelombang serangan siber beruntun.',
                        'waktu_detik' => 11,
                        'poin_benar' => 20,
                        'penalti_salah' => 8,
                        'hp_awal' => 3,
                        'urutan' => 3,
                        'warna' => 'amber',
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'expert',
                        'nama_level' => 'Spesialis Forensik',
                        'badge_label' => 'LEVEL 4 • EXPERT',
                        'deskripsi' => 'Analisis serangan Business Email Compromise (BEC), zero-day exploit, dan homograph URL.',
                        'waktu_detik' => 9,
                        'poin_benar' => 25,
                        'penalti_salah' => 10,
                        'hp_awal' => 2,
                        'urutan' => 4,
                        'warna' => 'purple',
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'master',
                        'nama_level' => 'Komandan Pertahanan',
                        'badge_label' => 'LEVEL 5 • MASTER',
                        'deskripsi' => 'Simulasi ancaman APT (Advanced Persistent Threat) berkecepatan tinggi dengan toleransi minimal.',
                        'waktu_detik' => 7,
                        'poin_benar' => 30,
                        'penalti_salah' => 12,
                        'hp_awal' => 2,
                        'urutan' => 5,
                        'warna' => 'red',
                        'is_active' => true,
                    ],
                ];

                foreach ($defaults as $row) {
                    static::create($row);
                }
            }

            // Pastikan soal untuk level expert & master tersedia di bank soal
            if (Schema::hasTable('soal_minigame')) {
                $hasExpert = SoalMinigame::where('tingkat', 'expert')->exists();
                if (! $hasExpert) {
                    $extraQuestions = [
                        [
                            'pertanyaan' => 'Email dari "ceo-office@perusahaan-anda.co.id" tetapi header Reply-To mengarah ke "transfer-cepat@protonmail.com" meminta pencairan dana darurat. Langkah paling tepat?',
                            'pilihan_json' => json_encode([
                                'A' => 'Segera transfer agar tidak ditegur pimpinan',
                                'B' => 'Verifikasi langsung melalui kanal komunikasi internal resmi (telepon kantor) dan tolak balasan ke email eksternal',
                                'C' => 'Membalas email dan mengirim foto token perbankan',
                                'D' => 'Mengunduh lampiran invoice.iso yang disertakan',
                            ]),
                            'jawaban_benar' => 'B',
                            'kategori' => 'Phishing Hunter',
                            'tingkat' => 'expert',
                            'game_level' => 'phishing',
                        ],
                        [
                            'pertanyaan' => 'Terdapat percobaan koneksi keluar mencurigakan dari server internal ke IP asing pada port 4444 (Reverse Shell). Pertahanan apa yang wajib diaktifkan?',
                            'pilihan_json' => json_encode([
                                'A' => 'Membiarkan port tetap terbuka',
                                'B' => 'Menonaktifkan log audit server',
                                'C' => 'Blokir trafik keluar dengan Firewall (Egress Filtering) & isolasi host',
                                'D' => 'Mengabaikan peringatan IDS',
                            ]),
                            'jawaban_benar' => 'C',
                            'kategori' => 'Cyber Defender',
                            'tingkat' => 'expert',
                            'game_level' => 'cyber-defender',
                        ],
                        [
                            'pertanyaan' => 'Perhatikan tautan berikut: "https://sso.purwakartakab.go.id@login-portal-resmi.top/auth". Bagaimana status keamanan URL tersebut?',
                            'pilihan_json' => json_encode([
                                'A' => 'Aman karena diawali sso.purwakartakab.go.id',
                                'B' => 'Trap! Karakter @ membuat browser mengabaikan teks sebelumnya dan mengarahkan ke domain palsu login-portal-resmi.top',
                                'C' => 'Aman karena sudah menggunakan protokol HTTPS',
                                'D' => 'Merupakan subdomain resmi pemerintah daerah',
                            ]),
                            'jawaban_benar' => 'B',
                            'kategori' => 'Link or Trap',
                            'tingkat' => 'expert',
                            'game_level' => 'link-trap',
                        ],
                        [
                            'pertanyaan' => 'Anda menerima undangan rapat kalender otomatis (.ics) yang menyisipkan tautan login OAuth aplikasi pihak ketiga yang meminta izin "Baca & Kirim Email". Apa analisis Anda?',
                            'pilihan_json' => json_encode([
                                'A' => 'Serangan Consent Phishing (OAuth Abuse) untuk mengambil alih akses email tanpa mencuri password',
                                'B' => 'Fitur standar pembaruan kalender resmi',
                                'C' => 'Aman selama tidak meminta nomor kartu kredit',
                                'D' => 'Wajib disetujui agar jadwal rapat tersinkronisasi',
                            ]),
                            'jawaban_benar' => 'A',
                            'kategori' => 'Phishing Hunter',
                            'tingkat' => 'master',
                            'game_level' => 'phishing',
                        ],
                        [
                            'pertanyaan' => 'Serangan Ransomware Zero-Day mulai mengenkripsi folder bersama melalui protokol SMB di jaringan lokal. Langkah pertahanan darurat paling efektif?',
                            'pilihan_json' => json_encode([
                                'A' => 'Membayar tebusan kripto secepatnya',
                                'B' => 'Mematikan monitor komputer saja',
                                'C' => 'Putuskan koneksi jaringan (Isolasi Segmen Firewall) & hentikan layanan SMB segera',
                                'D' => 'Menghapus seluruh backup offline',
                            ]),
                            'jawaban_benar' => 'C',
                            'kategori' => 'Cyber Defender',
                            'tingkat' => 'master',
                            'game_level' => 'cyber-defender',
                        ],
                        [
                            'pertanyaan' => 'Inspeksi URL: "https://xn--bca-c1a.co.id/ibir/login" (tampil di beberapa browser sebagai bca dengan titik bawah). Apakah tautan ini aman?',
                            'pilihan_json' => json_encode([
                                'A' => 'Aman karena berakhiran .co.id',
                                'B' => 'Trap! Ini adalah serangan IDN Homograph menggunakan Punycode (xn--) untuk meniru domain perbankan',
                                'C' => 'Aman karena merupakan server cadangan bank',
                                'D' => 'Aman selama diakses melalui jaringan Wi-Fi rumah',
                            ]),
                            'jawaban_benar' => 'B',
                            'kategori' => 'Link or Trap',
                            'tingkat' => 'master',
                            'game_level' => 'link-trap',
                        ],
                    ];

                    foreach ($extraQuestions as $q) {
                        SoalMinigame::create($q);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika koneksi DB belum siap saat CLI tertentu dijalankan
        }
    }
}

