<?php

namespace App\Services;

class BilingualHelper
{
    public static function isEnglish(): bool
    {
        return app()->getLocale() === 'en';
    }

    public static function translateLevel(string $slug, string $nama, string $deskripsi): array
    {
        if (! self::isEnglish()) {
            return ['nama' => $nama, 'deskripsi' => $deskripsi];
        }

        $map = [
            'easy' => [
                'nama' => 'Cyber Beginner',
                'deskripsi' => 'Basic scam signs such as fake lottery prizes, courier .apk files, and suspicious links.',
            ],
            'medium' => [
                'nama' => 'Intermediate Alert',
                'deskripsi' => 'Lookalike government/agency domains, fake invoices, and network malware attacks.',
            ],
            'hard' => [
                'nama' => 'Cyber Detective',
                'deskripsi' => 'Subtle spear-phishing, psychological manipulation, and rapid waves of cyber attacks.',
            ],
            'expert' => [
                'nama' => 'Forensic Specialist',
                'deskripsi' => 'Business Email Compromise (BEC) analysis, zero-day exploits, and homograph URL traps.',
            ],
            'master' => [
                'nama' => 'Defense Commander',
                'deskripsi' => 'High-speed Advanced Persistent Threat (APT) simulations with minimal error margin.',
            ],
        ];

        return $map[$slug] ?? ['nama' => $nama, 'deskripsi' => $deskripsi];
    }

    public static function translateDbQuestion(int $id, string $pertanyaan, array $opsi, string $kunci): array
    {
        if (! self::isEnglish()) {
            return [
                'pertanyaan' => $pertanyaan,
                'opsi' => $opsi,
                'kunci_teks' => $opsi[$kunci] ?? '',
            ];
        }

        $map = [
            1 => [
                'pertanyaan' => 'What is a key sign of a phishing email?',
                'opsi' => ['A' => 'Official sender address', 'B' => 'Asks for personal data', 'C' => 'Sent by a close friend', 'D' => 'Displays a company logo'],
            ],
            2 => [
                'pertanyaan' => 'Which password is the strongest and safest?',
                'opsi' => ['A' => '12345678', 'B' => 'password123', 'C' => 'P@ssw0rd!2024', 'D' => 'yourname'],
            ],
            3 => [
                'pertanyaan' => 'If you receive a big giveaway prize message, what should you do?',
                'opsi' => ['A' => 'Click immediately', 'B' => 'Share with friends', 'C' => 'Verify its authenticity first', 'D' => 'Fill in personal details'],
            ],
            4 => [
                'pertanyaan' => 'What is ransomware?',
                'opsi' => ['A' => 'An antivirus app', 'B' => 'Data-encrypting malware', 'C' => 'High-speed WiFi', 'D' => 'A password manager'],
            ],
            5 => [
                'pertanyaan' => 'What is the main function of a VPN?',
                'opsi' => ['A' => 'Speed up WiFi', 'B' => 'Encrypt internet connection', 'C' => 'Virus Protection', 'D' => 'Video editor'],
            ],
            6 => [
                'pertanyaan' => 'What does 2FA stand for?',
                'opsi' => ['A' => 'Double length password', 'B' => '2-step verification', 'C' => '2 Facebook Accounts', 'D' => 'Two Applications'],
            ],
            7 => [
                'pertanyaan' => 'You receive a WhatsApp message from an unknown number containing "LIHAT-RESI-PAKET.apk". What is the safest action?',
                'opsi' => [
                    'A' => 'Download and install the file to check the parcel',
                    'B' => 'Delete the message and block the sender because .apk files can steal SMS/OTP codes',
                    'C' => 'Forward the message to a family group chat',
                    'D' => 'Reply with your bank account number',
                ],
            ],
            8 => [
                'pertanyaan' => 'An email titled "YOUR ACCOUNT WILL BE BLOCKED IN 10 MINUTES" comes from support@bca-verifikasi-data.xyz. The main phishing indicator is...',
                'opsi' => [
                    'A' => 'Uses an unofficial domain (.xyz) and creates artificial urgency/panic',
                    'B' => 'Uses formal wording',
                    'C' => 'Has an uppercase subject line',
                    'D' => 'Was sent during office hours',
                ],
            ],
            9 => [
                'pertanyaan' => 'A spear-phishing attack impersonating an agency director with a malicious macro-enabled attachment is best prevented by...',
                'opsi' => [
                    'A' => 'Immediately enabling macros so the document displays',
                    'B' => 'Cross-verifying via official channels and disabling auto-run macros',
                    'C' => 'Changing your desktop wallpaper',
                    'D' => 'Turning off the monitor temporarily',
                ],
            ],
            10 => [
                'pertanyaan' => 'A computer suddenly shows a locked screen demanding cryptocurrency ransom to decrypt files. This attack is called...',
                'opsi' => ['A' => 'Adware', 'B' => 'Ransomware', 'C' => 'Defacement', 'D' => 'Keylogger'],
            ],
            11 => [
                'pertanyaan' => 'When using public café Wi-Fi for work, the best defense against Man-in-the-Middle eavesdropping is...',
                'opsi' => [
                    'A' => 'Using browser Incognito mode only',
                    'B' => 'Enabling an encrypted VPN connection or using a personal hotspot',
                    'C' => 'Lowering laptop screen brightness',
                    'D' => 'Clearing browser search history',
                ],
            ],
            12 => [
                'pertanyaan' => 'What is the first mitigation step when one device on an office network is infected with a self-propagating worm?',
                'opsi' => [
                    'A' => 'Isolate the device from the network immediately (unplug LAN / disable Wi-Fi)',
                    'B' => 'Keep it connected while downloading games',
                    'C' => 'Share work folders to other computers over LAN',
                    'D' => 'Wait until the end of the workday',
                ],
            ],
            13 => [
                'pertanyaan' => 'Which URL below is an official Indonesian government agency website?',
                'opsi' => [
                    'A' => 'https://bantuan-kemensos.blogspot.com',
                    'B' => 'https://kemendikdasmen.go.id',
                    'C' => 'http://cek-bansos-resmi.my.id',
                    'D' => 'https://pendaftaran-cpns.net',
                ],
            ],
            14 => [
                'pertanyaan' => 'Inspect this link: "https://klikbca.com.login-session-id.top/auth". Why is it dangerous?',
                'opsi' => [
                    'A' => 'Because it uses HTTPS',
                    'B' => 'The real root domain is login-session-id.top, not klikbca.com (Subdomain Trick)',
                    'C' => 'Because the URL is too short',
                    'D' => 'Because it lacks a logo image',
                ],
            ],
            15 => [
                'pertanyaan' => 'A domain spoofing technique that replaces lowercase "l" with "1" or lookalike Cyrillic characters such as "paypa1.com" is called...',
                'opsi' => ['A' => 'SQL Injection', 'B' => 'Typosquatting / Homograph Attack', 'C' => 'Cross-Site Scripting', 'D' => 'Brute Force'],
            ],
            16 => [
                'pertanyaan' => 'An email from "ceo-office@perusahaan-anda.co.id" has a Reply-To pointing to "transfer-cepat@protonmail.com" requesting an urgent fund transfer. Best action?',
                'opsi' => [
                    'A' => 'Transfer immediately to avoid upsetting leadership',
                    'B' => 'Verify directly via official internal channels (office phone) and reject replies to external email',
                    'C' => 'Reply with a photo of your banking token',
                    'D' => 'Download and open the attached invoice.iso file',
                ],
            ],
            17 => [
                'pertanyaan' => 'Suspicious outbound connections are detected from an internal server to an unknown IP on port 4444 (Reverse Shell). What defense must be activated?',
                'opsi' => [
                    'A' => 'Leave the port open',
                    'B' => 'Disable server audit logs',
                    'C' => 'Block outbound traffic with Firewall (Egress Filtering) & isolate the host',
                    'D' => 'Ignore IDS alerts',
                ],
            ],
            18 => [
                'pertanyaan' => 'Inspect this URL: "https://sso.purwakartakab.go.id@login-portal-resmi.top/auth". What is the security status of this link?',
                'opsi' => [
                    'A' => 'Safe because it begins with sso.purwakartakab.go.id',
                    'B' => 'Trap! The @ symbol causes browsers to ignore text before it and redirect to fake domain login-portal-resmi.top',
                    'C' => 'Safe because it uses HTTPS protocol',
                    'D' => 'It is an official regional government subdomain',
                ],
            ],
            19 => [
                'pertanyaan' => 'You receive an automated calendar invite (.ics) containing a third-party OAuth app login link requesting "Read & Send Email" permission. What is your analysis?',
                'opsi' => [
                    'A' => 'Consent Phishing (OAuth Abuse) attack to hijack email access without stealing your password',
                    'B' => 'Standard official calendar update feature',
                    'C' => 'Safe as long as it does not ask for a credit card number',
                    'D' => 'Must be approved so the meeting schedule syncs',
                ],
            ],
            20 => [
                'pertanyaan' => 'A Zero-Day Ransomware attack begins encrypting shared folders over the SMB protocol on a local network. What is the most effective emergency defense?',
                'opsi' => [
                    'A' => 'Pay the crypto ransom immediately',
                    'B' => 'Just turn off the computer monitor',
                    'C' => 'Disconnect network access (Firewall Segment Isolation) & stop SMB services immediately',
                    'D' => 'Delete all offline backups',
                ],
            ],
            21 => [
                'pertanyaan' => 'URL Inspection: "https://xn--bca-c1a.co.id/ibir/login" (rendered in some browsers like bca with a dot below). Is this link safe?',
                'opsi' => [
                    'A' => 'Safe because it ends with .co.id',
                    'B' => 'Trap! This is an IDN Homograph attack using Punycode (xn--) to impersonate a banking domain',
                    'C' => 'Safe because it is a bank backup server',
                    'D' => 'Safe when accessed over home Wi-Fi',
                ],
            ],
        ];

        if (isset($map[$id])) {
            $tOpts = $map[$id]['opsi'];

            return [
                'pertanyaan' => $map[$id]['pertanyaan'],
                'opsi' => $tOpts,
                'kunci_teks' => $tOpts[$kunci] ?? '',
            ];
        }

        return [
            'pertanyaan' => $pertanyaan,
            'opsi' => $opsi,
            'kunci_teks' => $opsi[$kunci] ?? '',
        ];
    }

    public static function translateModule(int $id, string $title, string $description, string $konten, string $tag): array
    {
        if (! self::isEnglish()) {
            return compact('title', 'description', 'konten', 'tag');
        }

        $map = [
            7 => [
                'title' => 'ITEE Journal — Digital Security',
                'description' => 'Academic research articles on cybersecurity, network threats, and modern information protection.',
                'tag' => 'Academic Journal',
            ],
            8 => [
                'title' => 'Bug Bounty 2025 — Kemendikdasmen',
                'description' => 'National cybersecurity talent program and ethical vulnerability reporting initiative.',
                'tag' => 'Cyber Talent',
            ],
            9 => [
                'title' => 'MAFINDO — Anti-Hoax Indonesia',
                'description' => 'Independent fact-checking community portal to verify hoaxes, misinformation, and scam alerts.',
                'tag' => 'Fact Check',
            ],
            10 => [
                'title' => 'Indonesia Digital Literacy Portal',
                'description' => 'National digital literacy modules covering safety, ethics, digital culture, and digital skills.',
                'tag' => 'National Portal',
            ],
            11 => [
                'title' => 'Smart Social Media — DJKN Kemenkeu',
                'description' => 'Official guide on staying ethical, cautious, and privacy-aware on social media platforms.',
                'tag' => 'Cyber Ethics',
            ],
            12 => [
                'title' => 'Practical Guide: Spotting Fake .APK Files & WhatsApp Phishing',
                'description' => 'Step-by-step guide to identifying fake parcel courier .APK scams, OTP theft, and social engineering.',
                'konten' => "1. Never install .apk files sent from unknown WhatsApp numbers claiming to be courier parcels or wedding invitations.\n2. Official banks, couriers, and government agencies never ask for your PIN, password, or 6-digit OTP code.\n3. Always verify suspicious links by checking the root domain before tapping.",
                'tag' => 'Public Guide',
            ],
        ];

        if (isset($map[$id])) {
            return [
                'title' => $map[$id]['title'],
                'description' => $map[$id]['description'],
                'konten' => $map[$id]['konten'] ?? $map[$id]['description'],
                'tag' => $map[$id]['tag'],
            ];
        }

        return compact('title', 'description', 'konten', 'tag');
    }
}

