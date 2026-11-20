<?php

namespace App\Services;

use Illuminate\Support\Str;

class PhishingLinkScannerService
{
    /**
     * Daftar brand & instansi resmi Indonesia beserta domain sahnya untuk deteksi typosquatting/lookalike.
     *
     * @var array<string, array{name: string, official: array<int, string>, keywords: array<int, string>}>
     */
    private const PROTECTED_BRANDS = [
        'bri' => [
            'name' => 'Bank BRI',
            'official' => ['bri.co.id', 'ib.bri.co.id', 'brimo.bri.co.id'],
            'keywords' => ['bri', 'brimo', 'bankbri', 'tarif-bri', 'bri-id'],
        ],
        'bca' => [
            'name' => 'Bank BCA',
            'official' => ['bca.co.id', 'klikbca.com', 'mybca.bca.co.id'],
            'keywords' => ['bca', 'bcaa', 'klikbca', 'mybca', 'bca-id', 'bca-mobile'],
        ],
        'mandiri' => [
            'name' => 'Bank Mandiri',
            'official' => ['bankmandiri.co.id', 'bmri.id'],
            'keywords' => ['mandiri', 'livinmandiri', 'livin-mandiri', 'bankmandiri'],
        ],
        'bni' => [
            'name' => 'Bank BNI',
            'official' => ['bni.co.id', 'wondr.bni.co.id'],
            'keywords' => ['bni', 'wondrbni', 'bni-mobile', 'bankbni'],
        ],
        'bjb' => [
            'name' => 'Bank BJB',
            'official' => ['bankbjb.co.id'],
            'keywords' => ['bankbjb', 'bjb-digi', 'bjbdigi'],
        ],
        'dana' => [
            'name' => 'DANA E-Wallet',
            'official' => ['dana.id', 'link.dana.id'],
            'keywords' => ['dana', 'danakaget', 'dana-kaget', 'klaim-dana'],
        ],
        'ovo' => [
            'name' => 'OVO E-Wallet',
            'official' => ['ovo.id'],
            'keywords' => ['ovo', 'ovo-id', 'klaim-ovo'],
        ],
        'shopee' => [
            'name' => 'Shopee Indonesia',
            'official' => ['shopee.co.id', 'shp.ee'],
            'keywords' => ['shopee', 'shopeepay', 'shoope', 'shoppe'],
        ],
        'tokopedia' => [
            'name' => 'Tokopedia',
            'official' => ['tokopedia.com', 'tokopedia.link'],
            'keywords' => ['tokopedia', 'tokped'],
        ],
        'djp' => [
            'name' => 'Direktorat Jenderal Pajak (DJP)',
            'official' => ['pajak.go.id', 'djponline.pajak.go.id'],
            'keywords' => ['pajak', 'djponline', 'djp-online', 'npwp', 'mpajak'],
        ],
        'polri' => [
            'name' => 'Polri / E-TLE Nasional',
            'official' => ['polri.go.id', 'etle-pmj.info', 'konfirmasi-etle.polri.go.id'],
            'keywords' => ['etle', 'tilang-polri', 'polri'],
        ],
        'pln' => [
            'name' => 'PLN Persero',
            'official' => ['pln.co.id'],
            'keywords' => ['pln', 'token-pln', 'subsidi-pln'],
        ],
        'bpjs' => [
            'name' => 'BPJS Kesehatan',
            'official' => ['bpjs-kesehatan.go.id'],
            'keywords' => ['bpjs', 'jkn-bpjs'],
        ],
        'jne' => [
            'name' => 'Ekspedisi JNE',
            'official' => ['jne.co.id'],
            'keywords' => ['jne', 'resi-jne', 'cek-jne'],
        ],
        'digisafe' => [
            'name' => 'Digi-Safe Purwakarta',
            'official' => ['digisafepurwakarta.web.id'],
            'keywords' => ['digisafe', 'digisafepurwakarta'],
        ],
    ];

    private const HIGH_RISK_TLDS = [
        'xyz', 'top', 'click', 'site', 'shop', 'cfd', 'icu', 'buzz', 'tk', 'ml', 'ga', 'cf', 'gq', 'vip', 'sbs', 'rest', 'pw', 'my.id', 'biz.id',
    ];

    private const SHORTENERS = [
        'bit.ly', 's.id', 'tinyurl.com', 'cutt.ly', 'rb.gy', 'is.gd', 't.co', 'rebrand.ly', 'shorturl.at',
    ];

    public static function analyze(string $rawInput, bool $isEn = false): array
    {
        $trimmed = trim($rawInput);
        if ($trimmed === '') {
            return [
                'ok' => false,
                'message' => $isEn ? 'Please enter a URL or QR code text to scan.' : 'Masukkan URL atau teks hasil pindai QR terlebih dahulu.',
            ];
        }

        $normalized = $trimmed;
        if (! preg_match('#^[a-zA-Z][a-zA-Z0-9+\-.]*://#', $normalized)) {
            $normalized = 'http://' . ltrim($normalized, '/');
        }

        $parts = @parse_url($normalized);
        if (! is_array($parts) || empty($parts['host'])) {
            return [
                'ok' => false,
                'message' => $isEn ? 'Invalid URL format. Example: https://domain.com/path' : 'Format tautan tidak valid. Contoh: https://domain.com/jalur',
            ];
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'http'));
        $host = strtolower((string) $parts['host']);
        $path = (string) ($parts['path'] ?? '/');
        $query = (string) ($parts['query'] ?? '');
        $hasUserAuth = ! empty($parts['user']) || str_contains($trimmed, '@');

        $riskScore = 0;
        $findings = [];

        // 1. Cek Protokol HTTP vs HTTPS
        if ($scheme !== 'https') {
            $riskScore += 22;
            $findings[] = [
                'severity' => 'warn',
                'title' => $isEn ? 'Unencrypted HTTP Protocol' : 'Protokol HTTP Tidak Terenkripsi',
                'detail' => $isEn
                    ? 'This link uses plain HTTP without TLS encryption. Credentials or OTPs typed here can be intercepted on public Wi-Fi.'
                    : 'Tautan ini masih menggunakan HTTP tanpa enkripsi TLS/SSL. Data login atau OTP yang diketik rawan disadap.',
            ];
        } else {
            $findings[] = [
                'severity' => 'safe',
                'title' => $isEn ? 'HTTPS Encrypted Transport' : 'Menggunakan Protokol HTTPS',
                'detail' => $isEn
                    ? 'Connection uses TLS encryption (Note: phishing sites can also use free HTTPS certificates, so domain verification remains mandatory).'
                    : 'Koneksi terenkripsi TLS (Catatan: situs phishing juga bisa memakai HTTPS gratis, jadi tetap periksa keaslian nama domain).',
            ];
        }

        // 2. Cek File Berbahaya (.APK / .EXE / .SCR)
        $fullPathLower = strtolower($path . '?' . $query);
        if (preg_match('/\.(apk|exe|scr|bat|msi|vbs|jar)(\b|$|\?)/i', $fullPathLower, $extMatch)) {
            $ext = strtoupper($extMatch[1]);
            $riskScore += 65;
            $findings[] = [
                'severity' => 'danger',
                'title' => $isEn ? "Malicious Installer File Detected (.{$ext})" : "Terdeteksi File Instalasi Berbahaya (.{$ext})",
                'detail' => $isEn
                    ? "The URL points directly to an executable .{$ext} file. Scammers disguise SMS-stealing trojans as wedding invitations, courier receipts, or E-TLE tickets."
                    : "Tautan ini mengarah ke unduhan aplikasi .{$ext}. Penipu sering menyamarkan malware pencuri SMS OTP sebagai undangan nikah, resi kurir, atau surat tilang.",
            ];
        }

        // 3. Cek Raw IP Address
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $riskScore += 45;
            $findings[] = [
                'severity' => 'danger',
                'title' => $isEn ? 'Raw IP Address Hostname' : 'Menggunakan Alamat IP Mentah (Bukan Domain Resmi)',
                'detail' => $isEn
                    ? "Official banks and government portals never ask citizens to log in via a raw IP address ({$host})."
                    : "Instansi resmi dan perbankan tidak pernah menggunakan alamat angka IP mentah ({$host}) untuk halaman layanan warga.",
            ];
        }

        // 4. Cek Trik @ di URL & Punycode
        if ($hasUserAuth) {
            $riskScore += 50;
            $findings[] = [
                'severity' => 'danger',
                'title' => $isEn ? 'Deceptive @ Authority Trick' : 'Trik Manipulasi Simbol @ pada URL',
                'detail' => $isEn
                    ? 'The URL contains an "@" symbol before the real host to trick users into believing they are visiting a trusted domain.'
                    : 'Tautan menyisipkan simbol "@" untuk mengecoh mata seolah-olah menuju domain resmi, padahal mengarah ke server penyerang di belakang "@".',
            ];
        }

        if (str_contains($host, 'xn--')) {
            $riskScore += 40;
            $findings[] = [
                'severity' => 'danger',
                'title' => $isEn ? 'Punycode / IDN Homograph Detected' : 'Terdeteksi Karakter Kembar Punycode (xn--)',
                'detail' => $isEn
                    ? 'Domain uses Unicode lookalike characters (Punycode) to visually impersonate a well-known brand.'
                    : 'Domain menggunakan huruf Unicode mirip (homograph attack) untuk memalsukan tampilan nama situs terkenal.',
            ];
        }

        // 5. Cek Pemendek URL (Shortlink)
        foreach (self::SHORTENERS as $shortDomain) {
            if ($host === $shortDomain || str_ends_with($host, '.' . $shortDomain)) {
                $riskScore += 28;
                $findings[] = [
                    'severity' => 'warn',
                    'title' => $isEn ? 'Hidden Destination (URL Shortener)' : 'Tujuan Asli Disembunyikan (Shortlink)',
                    'detail' => $isEn
                        ? "This link uses a URL shortener ({$shortDomain}) that masks the final destination domain."
                        : "Tautan menggunakan pemendek URL ({$shortDomain}) sehingga domain tujuan aslinya tidak terlihat secara langsung.",
                ];
                break;
            }
        }

        // 6. Cek Typosquatting / Pencatutan Nama Bank & Instansi
        $matchedOfficialBrand = null;
        foreach (self::PROTECTED_BRANDS as $brand) {
            $isOfficial = false;
            foreach ($brand['official'] as $offDom) {
                if ($host === $offDom || str_ends_with($host, '.' . $offDom)) {
                    $isOfficial = true;
                    $matchedOfficialBrand = $brand['name'];
                    break;
                }
            }

            if ($isOfficial) {
                break;
            }

            foreach ($brand['keywords'] as $kw) {
                if (str_contains($host, $kw) || str_contains(strtolower($path), $kw)) {
                    $riskScore += 55;
                    $officialList = implode(', ', $brand['official']);
                    $findings[] = [
                        'severity' => 'danger',
                        'title' => $isEn
                            ? "Suspected Impersonation of {$brand['name']}"
                            : "Indikasi Pencatutan / Tiruan {$brand['name']}",
                        'detail' => $isEn
                            ? "This link references \"{$kw}\" on an unofficial domain ({$host}). Official {$brand['name']} domains are: {$officialList}."
                            : "Tautan memuat kata kunci \"{$kw}\" tetapi berada di domain tidak resmi ({$host}). Domain resmi {$brand['name']} hanyalah: {$officialList}.",
                    ];
                    break 2;
                }
            }
        }

        if ($matchedOfficialBrand !== null) {
            $findings[] = [
                'severity' => 'safe',
                'title' => $isEn ? "Verified Official Domain ({$matchedOfficialBrand})" : "Domain Resmi Terverifikasi ({$matchedOfficialBrand})",
                'detail' => $isEn
                    ? "The hostname {$host} matches the authentic domain structure of {$matchedOfficialBrand}."
                    : "Hostname {$host} sesuai dengan domain resmi milik {$matchedOfficialBrand}.",
            ];
        } elseif (str_ends_with($host, '.go.id') || str_ends_with($host, '.ac.id') || str_ends_with($host, '.sch.id')) {
            $findings[] = [
                'severity' => 'safe',
                'title' => $isEn ? 'Restricted Indonesian Official TLD' : 'Domain Resmi Instansi/Pendidikan Indonesia',
                'detail' => $isEn
                    ? 'Uses a restricted Indonesian government/academic domain extension (.go.id / .ac.id / .sch.id).'
                    : 'Menggunakan ekstensi domain resmi pemerintah/pendidikan Indonesia (.go.id / .ac.id / .sch.id) yang memerlukan verifikasi dokumen negara.',
            ];
        }

        // 7. Cek TLD Berisiko Tinggi
        foreach (self::HIGH_RISK_TLDS as $badTld) {
            if ($host === $badTld || str_ends_with($host, '.' . $badTld)) {
                $riskScore += 28;
                $findings[] = [
                    'severity' => 'warn',
                    'title' => $isEn ? "High-Abuse Domain Extension (.{$badTld})" : "Ekstensi Domain Sering Disalahgunakan (.{$badTld})",
                    'detail' => $isEn
                        ? "The .{$badTld} extension is cheap/free and frequently used by disposable phishing campaigns."
                        : "Ekstensi .{$badTld} sangat murah/mudah dibuat secara anonim dan paling sering dipakai untuk situs phishing sekali buang.",
                ];
                break;
            }
        }

        // 8. Cek Kata Kunci Umpan Phishing di Path/Query
        if (preg_match('/(klaim|hadiah|undangan|tarif|tilang|blokir|reset-pin|verifikasi-otp|dana-kaget|subsidi)/i', $host . $path . $query, $kwMatch)) {
            if ($matchedOfficialBrand === null && ! str_ends_with($host, '.go.id')) {
                $lure = strtolower($kwMatch[1]);
                $riskScore += 24;
                $findings[] = [
                    'severity' => 'warn',
                    'title' => $isEn ? "Social Engineering Lure Keyword (\"{$lure}\")" : "Kata Kunci Umpan Rekayasa Sosial (\"{$lure}\")",
                    'detail' => $isEn
                        ? "The URL uses urgency or reward lure wording (\"{$lure}\") common in WhatsApp & SMS scams."
                        : "URL memuat kata umpan hadiah/ancaman darurat (\"{$lure}\") yang umum dipakai penipuan pesan berantai.",
                ];
            }
        }

        $riskScore = max(0, min(100, $riskScore));
        if ($matchedOfficialBrand !== null && $scheme === 'https' && $riskScore < 30) {
            $riskScore = 5;
        }

        $verdict = match (true) {
            $riskScore >= 55 => 'dangerous',
            $riskScore >= 25 => 'suspicious',
            default => 'safe',
        };

        $verdictLabel = match ($verdict) {
            'dangerous' => $isEn ? 'HIGH RISK / PHISHING DETECTED' : 'BERBAHAYA / INDIKASI KUAT PHISHING',
            'suspicious' => $isEn ? 'SUSPICIOUS / PROCEED WITH CAUTION' : 'MENCURIGAKAN / WASPADA',
            default => $isEn ? 'LOW RISK / VERIFIED STRUCTURE' : 'AMAN / STRUKTUR DOMAIN WAJAR',
        };

        $summary = match ($verdict) {
            'dangerous' => $isEn
                ? 'Do NOT open this link, install any downloaded file, or enter your PIN/OTP/password. It exhibits strong indicators of phishing or malware delivery.'
                : 'JANGAN membuka tautan ini, jangan memasang file yang terunduh, dan jangan pernah mengisi PIN, password, atau kode OTP Anda.',
            'suspicious' => $isEn
                ? 'This link shows several warning signs (such as a shortener, unencrypted HTTP, or unusual domain extension). Verify the sender independently before opening.'
                : 'Tautan ini memiliki beberapa tanda peringatan (seperti shortlink, HTTP tanpa enkripsi, atau ekstensi domain tidak lazim). Pastikan kebenarannya lewat kanal resmi.',
            default => $isEn
                ? 'No obvious phishing or malware patterns were found in the URL structure. Always remain cautious and never share your OTP code.'
                : 'Tidak ditemukan pola pencatutan domain atau file berbahaya pada struktur URL ini. Tetap jaga kerahasiaan PIN dan kode OTP Anda.',
        };

        $hostSegments = explode('.', $host);
        $tld = count($hostSegments) >= 2
            ? implode('.', array_slice($hostSegments, -2))
            : $host;

        return [
            'ok' => true,
            'input' => $trimmed,
            'normalized_url' => $normalized,
            'risk_score' => $riskScore,
            'verdict' => $verdict,
            'verdict_label' => $verdictLabel,
            'summary' => $summary,
            'anatomy' => [
                'scheme' => strtoupper($scheme),
                'host' => $host,
                'tld' => '.' . ltrim($tld, '.'),
                'path' => $path !== '' ? $path : '/',
                'query' => $query !== '' ? ('?' . Str::limit($query, 80)) : ($isEn ? '(none)' : '(kosong)'),
            ],
            'findings' => $findings,
        ];
    }
}
