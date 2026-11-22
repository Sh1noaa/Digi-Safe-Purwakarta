<?php

namespace App\Services;

use App\Models\DailyStreakQuestion;
use App\Models\SkorGame;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DailyStreakService
{
    public const MAX_FREEZE_RECOVERIES = 2;
    public const FREEZE_HOURS_THRESHOLD = 24;

    public static function ensureTables(): void
    {
        try {
            if (! Schema::hasTable('user_daily_streaks')) {
                Schema::create('user_daily_streaks', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->unique();
                    $table->unsignedInteger('current_streak')->default(0);
                    $table->unsignedInteger('longest_streak')->default(0);
                    $table->unsignedTinyInteger('freeze_recovery_left')->default(self::MAX_FREEZE_RECOVERIES);
                    $table->unsignedInteger('total_completed')->default(0);
                    $table->string('status', 20)->default('idle');
                    $table->timestamp('last_completed_at')->nullable();
                    $table->string('last_completed_date', 10)->nullable();
                    $table->text('weekly_days_json')->nullable();
                    $table->timestamps();
                });
            }

            if (! Schema::hasTable('user_daily_streak_logs')) {
                Schema::create('user_daily_streak_logs', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->index();
                    $table->string('challenge_date', 10)->index();
                    $table->string('question_key', 64);
                    $table->string('selected_option', 2);
                    $table->boolean('is_correct')->default(false);
                    $table->boolean('used_freeze_recovery')->default(false);
                    $table->timestamps();

                    $table->index(['user_id', 'challenge_date']);
                });
            }
            DailyStreakQuestion::ensureInitialized();
        } catch (\Throwable) {
            // Abaikan jika DB belum siap
        }
    }

    /**
     * Evaluasi status streak user berdasarkan aturan:
     * - Aktif (simbol api) jika terakhir dikerjakan < 24 jam.
     * - Membeku / Freeze (simbol es) jika sudah >= 24 jam tidak menyalakan streak & masih punya sisa kesempatan pulihkan (maks 2x).
     * - Padam / Reset jika sudah >= 24 jam tidak menyalakan streak dan kesempatan pemulihan (2x) sudah habis.
     */
    public static function getStatusForUser(?User $user, ?string $locale = null): array
    {
        self::ensureTables();

        $isEn = ($locale ?: app()->getLocale()) === 'en';
        $nowWib = now('Asia/Jakarta');
        $todayStr = $nowWib->format('Y-m-d');
        $weekKey = $nowWib->format('o-\WW');

        $question = self::questionForDate($todayStr, 0, $isEn);
        $baseWeek = self::buildWeekStrip($nowWib, [], $isEn);

        if (! $user) {
            return [
                'logged_in' => false,
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_completed' => 0,
                'status' => 'idle',
                'is_frozen' => false,
                'completed_today' => false,
                'freeze_recovery_left' => self::MAX_FREEZE_RECOVERIES,
                'max_freeze_recoveries' => self::MAX_FREEZE_RECOVERIES,
                'hours_since_last' => null,
                'hours_until_freeze' => null,
                'week_days' => $baseWeek,
                'question' => self::publicQuestionPayload($question),
                'attempt_offset' => 0,
            ];
        }

        $record = DB::table('user_daily_streaks')->where('user_id', $user->id)->first();
        if (! $record) {
            DB::table('user_daily_streaks')->insert([
                'user_id' => $user->id,
                'current_streak' => 0,
                'longest_streak' => 0,
                'freeze_recovery_left' => self::MAX_FREEZE_RECOVERIES,
                'total_completed' => 0,
                'status' => 'idle',
                'last_completed_at' => null,
                'last_completed_date' => null,
                'weekly_days_json' => json_encode(['week' => $weekKey, 'days' => []]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $record = DB::table('user_daily_streaks')->where('user_id', $user->id)->first();
        }

        $currentStreak = (int) $record->current_streak;
        $longestStreak = (int) $record->longest_streak;
        $recoveryLeft = (int) $record->freeze_recovery_left;
        $status = (string) $record->status;
        $lastAt = $record->last_completed_at ? Carbon::parse($record->last_completed_at) : null;
        $completedToday = $record->last_completed_date === $todayStr;

        $hoursSinceLast = $lastAt ? (int) floor($lastAt->diffInSeconds(now()) / 3600) : null;
        $hoursUntilFreeze = null;

        // Evaluasi 24 jam tanpa menyalakan streak -> Freeze (2x kesempatan pulihkan) atau Padam
        if ($currentStreak > 0 && $lastAt && ! $completedToday) {
            $elapsedHours = $lastAt->diffInSeconds(now()) / 3600;
            if ($elapsedHours >= self::FREEZE_HOURS_THRESHOLD) {
                if ($recoveryLeft > 0) {
                    $status = 'frozen';
                } else {
                    // Kesempatan pemulihan 2x sudah habis -> streak padam & reset
                    $currentStreak = 0;
                    $status = 'broken';
                    $recoveryLeft = self::MAX_FREEZE_RECOVERIES;
                    DB::table('user_daily_streaks')->where('user_id', $user->id)->update([
                        'current_streak' => 0,
                        'status' => 'broken',
                        'freeze_recovery_left' => self::MAX_FREEZE_RECOVERIES,
                        'updated_at' => now(),
                    ]);
                }
            } else {
                $status = 'active';
                $hoursUntilFreeze = max(1, (int) ceil(self::FREEZE_HOURS_THRESHOLD - $elapsedHours));
            }
        } elseif ($completedToday && $currentStreak > 0) {
            $status = 'active';
            if ($lastAt) {
                $elapsedHours = $lastAt->diffInSeconds(now()) / 3600;
                $hoursUntilFreeze = max(1, (int) ceil(self::FREEZE_HOURS_THRESHOLD - $elapsedHours));
            }
        }

        // Hitung berapa kali salah hari ini agar jika salah, user mendapat soal lanjutan cadangan (tidak bisa asal tebak A-E pada soal yang sama)
        $wrongToday = DB::table('user_daily_streak_logs')
            ->where('user_id', $user->id)
            ->where('challenge_date', $todayStr)
            ->where('is_correct', false)
            ->count();

        $todayQuestion = self::questionForDate($todayStr, $wrongToday, $isEn);

        $weeklyRaw = json_decode((string) $record->weekly_days_json, true);
        $weekDaysMap = (is_array($weeklyRaw) && ($weeklyRaw['week'] ?? '') === $weekKey && is_array($weeklyRaw['days'] ?? null))
            ? $weeklyRaw['days']
            : [];

        if ($status === 'frozen') {
            $todayDow = (int) $nowWib->isoWeekday(); // 1=Mon .. 7=Sun
            $weekDaysMap[$todayDow] = 'frozen';
        }

        return [
            'logged_in' => true,
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'total_completed' => (int) $record->total_completed,
            'status' => $status,
            'is_frozen' => $status === 'frozen',
            'completed_today' => $completedToday,
            'freeze_recovery_left' => $recoveryLeft,
            'max_freeze_recoveries' => self::MAX_FREEZE_RECOVERIES,
            'hours_since_last' => $hoursSinceLast,
            'hours_until_freeze' => $hoursUntilFreeze,
            'week_days' => self::buildWeekStrip($nowWib, $weekDaysMap, $isEn),
            'question' => self::publicQuestionPayload($todayQuestion),
            'attempt_offset' => $wrongToday,
        ];
    }

    /**
     * Proses jawaban A/B/C/D/E user untuk tantangan harian / pemulihan streak beku.
     */
    public static function submitAnswer(User $user, string $selectedOption, ?string $locale = null): array
    {
        self::ensureTables();

        $isEn = ($locale ?: app()->getLocale()) === 'en';
        $option = strtoupper(trim($selectedOption));
        if (! in_array($option, ['A', 'B', 'C', 'D', 'E'], true)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => $isEn ? 'Choose an answer between A and E.' : 'Pilih jawaban antara A sampai E.',
            ];
        }

        $state = self::getStatusForUser($user, $locale);
        if ($state['completed_today']) {
            return [
                'ok' => false,
                'status' => 409,
                'message' => $isEn
                    ? 'You have already completed today\'s advanced challenge! Come back tomorrow to keep your flame burning.'
                    : 'Anda sudah menyelesaikan tantangan harian hari ini! Kembali lagi besok untuk menjaga api streak tetap menyala.',
                'streak' => $state,
            ];
        }

        $nowWib = now('Asia/Jakarta');
        $todayStr = $nowWib->format('Y-m-d');
        $weekKey = $nowWib->format('o-\WW');
        $todayDow = (int) $nowWib->isoWeekday();

        $wrongToday = (int) $state['attempt_offset'];
        $question = self::questionForDate($todayStr, $wrongToday, $isEn);
        $isCorrect = $option === $question['answer'];

        DB::table('user_daily_streak_logs')->insert([
            'user_id' => $user->id,
            'challenge_date' => $todayStr,
            'question_key' => $question['key'],
            'selected_option' => $option,
            'is_correct' => $isCorrect,
            'used_freeze_recovery' => $isCorrect && $state['is_frozen'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! $isCorrect) {
            $nextState = self::getStatusForUser($user, $locale);

            return [
                'ok' => true,
                'correct' => false,
                'correct_option' => $question['answer'],
                'explanation' => $question['explanation'],
                'message' => $isEn
                    ? 'Incorrect answer. Read the explanation below and try the next advanced question to ignite your streak!'
                    : 'Jawaban kurang tepat. Pelajari analisis teknis di bawah, lalu coba soal lanjutan berikutnya untuk menyalakan streak!',
                'streak' => $nextState,
            ];
        }

        // Jawaban BENAR -> nyalakan / pulihkan streak!
        $record = DB::table('user_daily_streaks')->where('user_id', $user->id)->first();
        $usedRecovery = (bool) $state['is_frozen'];
        $newStreak = $usedRecovery
            ? ((int) $state['current_streak'] + 1)
            : ((int) $state['current_streak'] + 1);
        $newLongest = max((int) ($record->longest_streak ?? 0), $newStreak);
        $newRecoveryLeft = $usedRecovery
            ? max(0, (int) $state['freeze_recovery_left'] - 1)
            : (int) ($record->freeze_recovery_left ?? self::MAX_FREEZE_RECOVERIES);

        $weeklyRaw = json_decode((string) ($record->weekly_days_json ?? ''), true);
        $weekDaysMap = (is_array($weeklyRaw) && ($weeklyRaw['week'] ?? '') === $weekKey && is_array($weeklyRaw['days'] ?? null))
            ? $weeklyRaw['days']
            : [];
        $weekDaysMap[$todayDow] = $usedRecovery ? 'recovered' : 'done';

        DB::table('user_daily_streaks')->where('user_id', $user->id)->update([
            'current_streak' => $newStreak,
            'longest_streak' => $newLongest,
            'freeze_recovery_left' => $newRecoveryLeft,
            'total_completed' => (int) ($record->total_completed ?? 0) + 1,
            'status' => 'active',
            'last_completed_at' => now(),
            'last_completed_date' => $todayStr,
            'weekly_days_json' => json_encode(['week' => $weekKey, 'days' => $weekDaysMap]),
            'updated_at' => now(),
        ]);

        // Tambah poin ke leaderboard minigame (skor_game)
        $bonusPts = $usedRecovery ? 60 : 40;
        if (Schema::hasTable('skor_game')) {
            try {
                $existingMax = (int) SkorGame::where('user_id', $user->id)
                    ->where('game_type', 'daily-streak')
                    ->max('skor_tertinggi');

                SkorGame::create([
                    'user_id' => $user->id,
                    'game_type' => 'daily-streak',
                    'skor' => $bonusPts,
                    'skor_tertinggi' => max($existingMax, $bonusPts),
                    'benar' => 1,
                    'salah' => 0,
                    'played_at' => now(),
                ]);
            } catch (\Throwable) {
                // Abaikan jika skema skor_game tidak tersedia di test tertentu
            }
        }

        $updatedState = self::getStatusForUser($user, $locale);

        return [
            'ok' => true,
            'correct' => true,
            'recovered' => $usedRecovery,
            'bonus_points' => $bonusPts,
            'correct_option' => $question['answer'],
            'explanation' => $question['explanation'],
            'message' => $usedRecovery
                ? ($isEn
                    ? "Streak recovered! Your flame is active again ({$newStreak} days) +{$bonusPts} pts. Recovery chances left: {$newRecoveryLeft}/2."
                    : "Streak berhasil dipulihkan! Api kembali menyala ({$newStreak} hari) +{$bonusPts} poin. Sisa kesempatan pemulihan: {$newRecoveryLeft}/2.")
                : ($isEn
                    ? "Correct! Daily streak ignited ({$newStreak} days) +{$bonusPts} pts."
                    : "Tepat sekali! Streak harian menyala ({$newStreak} hari) +{$bonusPts} poin."),
            'streak' => $updatedState,
        ];
    }

    private static function buildWeekStrip(Carbon $nowWib, array $daysMap, bool $isEn): array
    {
        $labels = $isEn
            ? [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun']
            : [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];

        $todayDow = (int) $nowWib->isoWeekday();
        $strip = [];
        for ($d = 1; $d <= 7; $d++) {
            $state = $daysMap[$d] ?? ($d === $todayDow ? 'today' : ($d < $todayDow ? 'missed' : 'upcoming'));
            $strip[] = [
                'dow' => $d,
                'label' => $labels[$d],
                'is_today' => $d === $todayDow,
                'state' => $state, // done | recovered | frozen | today | missed | upcoming
            ];
        }

        return $strip;
    }

    private static function publicQuestionPayload(array $q): array
    {
        return [
            'key' => $q['key'],
            'category' => $q['category'],
            'difficulty' => $q['difficulty'],
            'prompt' => $q['prompt'],
            'options' => $q['options'],
        ];
    }

    private static function questionForDate(string $dateStr, int $offset, bool $isEn): array
    {
        $bank = self::advancedQuestionsBank($isEn);
        $count = count($bank);
        $seed = abs(crc32('digisafe-adv-' . $dateStr));
        $idx = ($seed + $offset) % $count;

        return $bank[$idx];
    }

    /**
     * Ambil bank soal Daily Streak aktif dari database (yang bisa dikelola Admin),
     * dengan fallback aman ke bank bawaan jika database belum siap / kosong.
     */
    public static function advancedQuestionsBank(bool $isEn = false): array
    {
        try {
            DailyStreakQuestion::ensureInitialized();

            $rows = DailyStreakQuestion::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get();

            if ($rows->isNotEmpty()) {
                return $rows
                    ->map(fn (DailyStreakQuestion $q) => $q->toBankItem($isEn))
                    ->values()
                    ->all();
            }
        } catch (\Throwable) {
            // Fallback ke bank bawaan jika tabel tidak tersedia
        }

        return self::defaultQuestionsBank($isEn);
    }

    /**
     * Bank bawaan pertanyaan keamanan siber tingkat lanjut (Opsi A-E) untuk seed awal & fallback.
     */
    public static function defaultQuestionsBank(bool $isEn = false): array
    {
        if ($isEn) {
            return [
                [
                    'key' => 'adv_apk_accessibility',
                    'category' => 'Android Malware Forensics',
                    'difficulty' => 'Advanced',
                    'prompt' => 'Why do Indonesian banking trojan .APK files disguised as wedding invitations specifically request the Android Accessibility Service permission alongside READ_SMS?',
                    'options' => [
                        'A' => 'To compress SMS databases so they consume less internal phone storage',
                        'B' => 'To programmatically read screen contents, auto-click permission prompts, bypass Play Protect, and overlay fake login views',
                        'C' => 'To unlock root bootloader access without restarting the smartphone',
                        'D' => 'To encrypt SIM card IMSI identifiers inside the baseband processor',
                        'E' => 'To force the cellular modem to downgrade from 5G to 2G GSM',
                    ],
                    'answer' => 'B',
                    'explanation' => 'Abusing Accessibility Services lets malware inspect every UI element on screen, auto-approve dangerous permissions (like SMS & Notification Listener), prevent uninstallation, and inject credential-harvesting overlays on banking apps.',
                ],
                [
                    'key' => 'adv_oauth_pkce',
                    'category' => 'OAuth 2.0 & Identity',
                    'difficulty' => 'Advanced',
                    'prompt' => 'In a mobile or SPA OAuth 2.0 Authorization Code flow, what specific attack does PKCE (Proof Key for Code Exchange) mitigate that a static client_secret cannot?',
                    'options' => [
                        'A' => 'SQL Injection against the authorization server token table',
                        'B' => 'DNS cache poisoning on the identity provider apex domain',
                        'C' => 'Authorization code interception by a malicious app registered on the same custom URI scheme',
                        'D' => 'Brute-forcing user passwords via the /userinfo endpoint',
                        'E' => 'Replay of expired JWT refresh tokens after account deletion',
                    ],
                    'answer' => 'C',
                    'explanation' => 'Public clients (mobile apps/SPAs) cannot keep a static secret safely. PKCE binds each authorization code to a per-request cryptographic code_verifier/code_challenge, making intercepted codes useless to an attacker.',
                ],
                [
                    'key' => 'adv_dmarc_alignment',
                    'category' => 'Email Authentication',
                    'difficulty' => 'Advanced',
                    'prompt' => 'An organization publishes SPF ("~all") and DKIM, yet spoofed emails pretending to be its CEO still pass SPF checks using an attacker-controlled Return-Path domain. Which control specifically closes this gap?',
                    'options' => [
                        'A' => 'Enabling TLS 1.3 Opportunistic Encryption (STARTTLS) on port 587',
                        'B' => 'Enforcing DMARC (p=reject) with identifier alignment between Header From and SPF/DKIM domains',
                        'C' => 'Adding a second MX record with a higher priority number',
                        'D' => 'Blocking all incoming emails that contain HTML tables',
                        'E' => 'Switching SMTP authentication from PLAIN to CRAM-MD5',
                    ],
                    'answer' => 'B',
                    'explanation' => 'SPF alone validates only the SMTP envelope Return-Path, not the visible Header From. DMARC enforces domain alignment between the visible From header and the authenticated SPF/DKIM domain, with p=reject instructing receivers to drop unaligned spoofs.',
                ],
                [
                    'key' => 'adv_mfa_fatigue',
                    'category' => 'Authentication Defense',
                    'difficulty' => 'Advanced',
                    'prompt' => 'An attacker with stolen credentials spams dozens of MFA push notifications at 2 AM until a drowsy employee taps "Approve" (MFA Fatigue / Push Bombing). Which defense neutralizes both MFA fatigue and real-time AiTM phishing proxies?',
                    'options' => [
                        'A' => 'Extending SMS OTP validity from 5 minutes to 30 minutes',
                        'B' => 'Requiring users to change passwords every 30 days',
                        'C' => 'Replacing push approvals with phishing-resistant FIDO2 / WebAuthn Passkeys bound to the origin domain',
                        'D' => 'Hiding the login URL behind a custom port number',
                        'E' => 'Sending the same 6-digit OTP via both email and WhatsApp simultaneously',
                    ],
                    'answer' => 'C',
                    'explanation' => 'FIDO2/WebAuthn cryptographically binds authentication signatures to the exact TLS origin domain and requires local user presence, neutralizing both push bombing and Adversary-in-the-Middle (AiTM) reverse-proxy phishing.',
                ],
                [
                    'key' => 'adv_jwt_alg_confusion',
                    'category' => 'API Security',
                    'difficulty' => 'Advanced',
                    'prompt' => 'A backend verifies RS256-signed JWTs using a library that trusts the token header\'s "alg" field. How does an attacker exploit JWT Algorithm Confusion (RS256 to HS256)?',
                    'options' => [
                        'A' => 'By brute-forcing the server\'s private RSA-4096 key offline in 2 seconds',
                        'B' => 'By modifying alg to HS256 and signing a forged payload using the server\'s publicly available RSA public key as the HMAC secret',
                        'C' => 'By appending SQL comments inside the Base64URL header',
                        'D' => 'By stripping the TLS certificate from the Authorization header',
                        'E' => 'By compressing the JWT payload with gzip to bypass signature verification',
                    ],
                    'answer' => 'B',
                    'explanation' => 'If the verifier uses the same key variable for both asymmetric (RS256 public key) and symmetric (HS256 shared secret) verification based on the untrusted header, an attacker can sign forged tokens with the known public key bytes using HS256.',
                ],
                [
                    'key' => 'adv_ssrf_imds',
                    'category' => 'Cloud & Web Security',
                    'difficulty' => 'Advanced',
                    'prompt' => 'A web app fetches user-supplied image URLs server-side for avatar import. Why is blocking only "localhost" and "127.0.0.1" insufficient against SSRF on cloud infrastructure?',
                    'options' => [
                        'A' => 'Because cloud firewalls automatically rewrite HTTP URLs to FTP',
                        'B' => 'Because attackers can target link-local metadata endpoints like 169.254.169.254, IPv6 equivalents, decimal/octal IPs, or DNS rebinding to steal IAM credentials',
                        'C' => 'Because browsers ignore Content-Security-Policy on image URLs',
                        'D' => 'Because PNG files automatically execute PHP code during download',
                        'E' => 'Because HTTPS prevents the server from inspecting the hostname',
                    ],
                    'answer' => 'B',
                    'explanation' => 'Cloud metadata services (169.254.169.254), private RFC1918/IPv6 ranges, alternative IP encodings, HTTP 302 redirects, and DNS rebinding bypass naive string blacklists. Defense requires resolving DNS, validating all IPs against public-only allowlists, disabling redirects, and using IMDSv2.',
                ],
                [
                    'key' => 'adv_bcrypt_truncation',
                    'category' => 'Cryptography',
                    'difficulty' => 'Advanced',
                    'prompt' => 'Why should applications enforce a sensible maximum password length (or pre-hash carefully / use Argon2id) when hashing passwords with standard bcrypt?',
                    'options' => [
                        'A' => 'Bcrypt rejects any password containing uppercase symbols',
                        'B' => 'Standard bcrypt silently truncates inputs at 72 bytes and huge inputs can cause CPU exhaustion DoS',
                        'C' => 'Bcrypt stores passwords in reversible AES-128 ECB format above 32 bytes',
                        'D' => 'Bcrypt does not generate a unique random salt per password',
                        'E' => 'Bcrypt hashes expire automatically after 90 days',
                    ],
                    'answer' => 'B',
                    'explanation' => 'Bcrypt only processes the first 72 bytes of input (making distinct long passphrases collide if identical in the first 72 bytes) and unbounded password inputs can trigger DoS during hashing.',
                ],
                [
                    'key' => 'adv_csp_nonce',
                    'category' => 'Browser Security',
                    'difficulty' => 'Advanced',
                    'prompt' => 'Why is a Content-Security-Policy (CSP) containing "script-src \'self\' \'unsafe-inline\'" largely ineffective at stopping Reflected or Stored XSS?',
                    'options' => [
                        'A' => 'Because \'self\' allows loading scripts from any HTTPS domain on the internet',
                        'B' => 'Because \'unsafe-inline\' permits injected <script> blocks and inline event handlers (like onerror=) to execute directly',
                        'C' => 'Because modern browsers ignore CSP headers delivered over HTTPS',
                        'D' => 'Because CSP only protects CSS stylesheets and never restricts JavaScript',
                        'E' => 'Because \'unsafe-inline\' disables HttpOnly on session cookies',
                    ],
                    'answer' => 'B',
                    'explanation' => 'Allowing \'unsafe-inline\' lets attacker-injected inline scripts run unrestricted. Effective XSS mitigation uses per-request cryptographic nonces (script-src \'nonce-...\') or strict hashes without unsafe-inline.',
                ],
                [
                    'key' => 'adv_session_fixation',
                    'category' => 'Session Management',
                    'difficulty' => 'Advanced',
                    'prompt' => 'Immediately after a user successfully authenticates or resets their password, what two server-side session actions prevent Session Fixation and persistent account takeover?',
                    'options' => [
                        'A' => 'Base64-encoding the username in localStorage and disabling CSRF tokens',
                        'B' => 'Regenerating the session ID on privilege change and invalidating all other active sessions/API tokens for that account',
                        'C' => 'Setting SameSite=None without Secure and extending cookie lifetime to 10 years',
                        'D' => 'Sending the raw session ID in the URL query string for every redirect',
                        'E' => 'Allowing concurrent sessions to share the same unrotated CSRF token',
                    ],
                    'answer' => 'B',
                    'explanation' => 'Session ID regeneration defeats Session Fixation, while revoking all other stored sessions and API tokens immediately evicts any attacker who previously compromised the account.',
                ],
                [
                    'key' => 'adv_subdomain_takeover',
                    'category' => 'Infrastructure Security',
                    'difficulty' => 'Advanced',
                    'prompt' => 'A school removes its old cloud landing page hosted on a third-party SaaS platform but forgets to delete the DNS CNAME record "promo.school.sch.id -> old-app.saas-host.net". What risk arises?',
                    'options' => [
                        'A' => 'All students lose internet access on the school Wi-Fi',
                        'B' => 'Subdomain Takeover: an attacker claims "old-app.saas-host.net", serving phishing pages with valid HTTPS under the trusted school domain and potentially reading wildcard cookies',
                        'C' => 'The school\'s primary MySQL database is automatically dropped',
                        'D' => 'DNSSEC automatically transfers ownership of the .sch.id TLD',
                        'E' => 'Browsers automatically block the apex domain school.sch.id',
                    ],
                    'answer' => 'B',
                    'explanation' => 'A dangling CNAME allows anyone to provision the unclaimed target resource on the SaaS provider, inheriting the trusted subdomain, TLS certificate issuance, and parent-domain cookies scoped to *.school.sch.id.',
                ],
                [
                    'key' => 'adv_idor_authz',
                    'category' => 'Access Control (OWASP A01)',
                    'difficulty' => 'Advanced',
                    'prompt' => 'An API endpoint GET /api/invoices/1042 checks only that the user has a valid Bearer token, then fetches Invoice::find(1042). Changing 1042 to 1043 reveals another citizen\'s record. What is this vulnerability and its proper fix?',
                    'options' => [
                        'A' => 'XSS; fix by escaping HTML in JSON responses',
                        'B' => 'CSRF; fix by adding an X-Frame-Options header',
                        'C' => 'BOLA / IDOR (Broken Object Level Authorization); fix by scoping queries to Auth::id() or enforcing object-level Policy authorization on every request',
                        'D' => 'Clickjacking; fix by switching from sequential IDs to MD5 hashes without authorization checks',
                        'E' => 'Open Redirect; fix by validating the Referer header',
                    ],
                    'answer' => 'C',
                    'explanation' => 'Authentication proves who you are, NOT what objects you may access. BOLA/IDOR must be fixed via server-side object ownership/policy checks on every read/write operation (UUIDs alone are defense-in-depth, never a substitute for authorization).',
                ],
                [
                    'key' => 'adv_ransomware_32110',
                    'category' => 'Incident Resilience',
                    'difficulty' => 'Advanced',
                    'prompt' => 'Why does modern ransomware defense upgrade the classic 3-2-1 backup rule to the 3-2-1-1-0 rule?',
                    'options' => [
                        'A' => 'To compress backups using 0% CPU overhead on local USB drives',
                        'B' => 'To add at least 1 offline/air-gapped or immutable (WORM) copy so ransomware with admin credentials cannot encrypt backups, plus 0 unverified recovery errors',
                        'C' => 'To store all backups inside the same SMB network share as production servers',
                        'D' => 'To disable encryption on offsite backups for faster restoration',
                        'E' => 'To guarantee zero downtime without ever testing restore procedures',
                    ],
                    'answer' => 'B',
                    'explanation' => 'Modern ransomware actively targets online backups and shadow copies. An immutable (Write-Once-Read-Many) or air-gapped copy combined with verified restore tests (0 errors) ensures guaranteed recovery without paying ransom.',
                ],
            ];
        }

        return [
            [
                'key' => 'adv_apk_accessibility',
                'category' => 'Forensik Malware Android',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Mengapa file .APK penipuan berkedok undangan nikah atau resi kurir di Indonesia secara spesifik meminta izin Android Accessibility Service selain izin READ_SMS?',
                'options' => [
                    'A' => 'Untuk mengompresi riwayat SMS agar memori internal ponsel korban tidak penuh',
                    'B' => 'Agar malware dapat membaca isi layar, mengklik otomatis persetujuan izin berbahaya, mencegah uninstall, dan memasang overlay login palsu di aplikasi m-Banking',
                    'C' => 'Untuk membuka kunci bootloader root tanpa perlu me-restart ponsel Android',
                    'D' => 'Untuk mengenkripsi nomor IMSI kartu SIM langsung di dalam chip modem baseband',
                    'E' => 'Untuk memaksa sinyal seluler turun otomatis dari jaringan 5G ke 2G GSM',
                ],
                'answer' => 'B',
                'explanation' => 'Penyalahgunaan Accessibility Service memberi malware kendali penuh atas antarmuka layar: membaca PIN/OTP, menyetujui izin secara diam-diam, memblokir tombol Uninstall, hingga menyisipkan layar login palsu di atas aplikasi perbankan.',
            ],
            [
                'key' => 'adv_oauth_pkce',
                'category' => 'OAuth 2.0 & Identitas',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Pada alur OAuth 2.0 Authorization Code untuk aplikasi mobile/SPA, serangan spesifik apa yang dicegah oleh PKCE (Proof Key for Code Exchange) yang tidak bisa ditutup oleh static client_secret?',
                'options' => [
                    'A' => 'SQL Injection pada tabel token di server autentikasi',
                    'B' => 'DNS Cache Poisoning pada domain utama penyedia identitas',
                    'C' => 'Penyadapan (interception) authorization code oleh aplikasi berbahaya yang mendaftarkan custom URI scheme yang sama',
                    'D' => 'Brute-force kata sandi user melalui endpoint /userinfo',
                    'E' => 'Pemakaian ulang refresh token JWT yang sudah kedaluwarsa setelah akun dihapus',
                ],
                'answer' => 'C',
                'explanation' => 'Aplikasi mobile/SPA adalah public client yang tidak aman menyimpan static secret. PKCE mengikat authorization code ke code_verifier & code_challenge unik per sesi, sehingga kode yang disadap aplikasi jahat tidak dapat ditukar menjadi access token.',
            ],
            [
                'key' => 'adv_dmarc_alignment',
                'category' => 'Keamanan Email & Spoofing',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Sebuah instansi sudah memasang SPF (~all) dan DKIM, namun email penipuan yang mengatasnamakan pimpinan tetap lolos validasi SPF karena penyerang memakai domain Return-Path miliknya sendiri. Kontrol apa yang secara tepat menutup celah ini?',
                'options' => [
                    'A' => 'Mengaktifkan enkripsi oportunistik STARTTLS pada port 587',
                    'B' => 'Menerapkan DMARC (p=reject) dengan alignment domain antara Header From yang tampil dan domain terautentikasi SPF/DKIM',
                    'C' => 'Menambahkan MX record cadangan dengan angka prioritas lebih tinggi',
                    'D' => 'Memblokir semua email masuk yang mengandung elemen tabel HTML',
                    'E' => 'Mengganti mekanisme login SMTP dari PLAIN ke CRAM-MD5',
                ],
                'answer' => 'B',
                'explanation' => 'SPF hanya memverifikasi alamat envelope Return-Path, bukan Header From yang dilihat korban. DMARC memastikan keselarasan (alignment) domain Header From dengan SPF/DKIM, serta kebijakan p=reject menolak email spoofing secara otomatis.',
            ],
            [
                'key' => 'adv_mfa_fatigue',
                'category' => 'Pertahanan Autentikasi',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Penyerang yang mencuri password membombardir puluhan notifikasi push MFA pukul 02.00 dini hari sampai korban yang mengantuk menekan "Approve" (MFA Fatigue / Push Bombing). Mekanisme mana yang menetralisir baik MFA fatigue maupun proxy phishing AiTM?',
                'options' => [
                    'A' => 'Memperpanjang masa berlaku OTP SMS dari 5 menit menjadi 30 menit',
                    'B' => 'Mewajibkan pergantian password rutin setiap 30 hari sekali',
                    'C' => 'Menggunakan autentikasi FIDO2 / WebAuthn Passkey yang terikat kriptografis pada origin domain asli',
                    'D' => 'Menyembunyikan halaman login di balik nomor port non-standar',
                    'E' => 'Mengirimkan 6 digit OTP yang sama lewat email dan WhatsApp secara bersamaan',
                ],
                'answer' => 'C',
                'explanation' => 'FIDO2/WebAuthn Passkey mengikat tanda tangan kriptografis ke domain TLS asli secara ketat dan menuntut verifikasi lokal perangkat, sehingga kebal terhadap Push Bombing maupun reverse-proxy Adversary-in-the-Middle (AiTM).',
            ],
            [
                'key' => 'adv_jwt_alg_confusion',
                'category' => 'Keamanan API & Token',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Sebuah backend memverifikasi token JWT bertanda tangan RS256 tetapi library-nya mempercayai field "alg" dari header token secara langsung. Bagaimana penyerang mengeksploitasi JWT Algorithm Confusion (RS256 ke HS256)?',
                'options' => [
                    'A' => 'Memecahkan private key RSA-4096 server secara offline dalam 2 detik',
                    'B' => 'Mengubah header alg menjadi HS256 lalu menandatangani payload palsu memakai public key RSA server yang bersifat publik sebagai HMAC secret',
                    'C' => 'Menyisipkan komentar SQL di dalam string Base64URL header JWT',
                    'D' => 'Menghapus sertifikat TLS dari header HTTP Authorization',
                    'E' => 'Mengompresi payload JWT dengan gzip agar validasi signature dilewati',
                ],
                'answer' => 'B',
                'explanation' => 'Jika verifikator memakai variabel key yang sama untuk RS256 dan HS256 mengikuti header token yang tidak dipercaya, penyerang cukup menandatangani token palsu secara simetris (HS256) memakai file public key server.',
            ],
            [
                'key' => 'adv_ssrf_imds',
                'category' => 'Keamanan Cloud & Web',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Aplikasi web mengambil gambar avatar dari URL input pengguna di sisi server (server-side fetch). Mengapa sekadar memblokir string "localhost" dan "127.0.0.1" tidak cukup mencegah SSRF di infrastruktur cloud?',
                'options' => [
                    'A' => 'Karena firewall cloud otomatis mengubah URL HTTP menjadi protokol FTP',
                    'B' => 'Karena penyerang dapat menargetkan endpoint metadata cloud 169.254.169.254, alamat IPv6, format IP desimal/oktal, HTTP redirect 302, atau DNS rebinding untuk mencuri kredensial IAM',
                    'C' => 'Karena browser mengabaikan header Content-Security-Policy pada URL gambar',
                    'D' => 'Karena file PNG secara otomatis mengeksekusi kode PHP saat diunduh',
                    'E' => 'Karena HTTPS mencegah server membaca nama hostname tujuan',
                ],
                'answer' => 'B',
                'explanation' => 'Blacklist string mudah ditembus via metadata 169.254.169.254, IP privat RFC1918/IPv6, redirect 302, dan DNS rebinding. Pertahanan wajib me-resolve IP terlebih dahulu, memblokir seluruh rentang privat/link-local, menonaktifkan redirect, dan mengaktifkan IMDSv2.',
            ],
            [
                'key' => 'adv_bcrypt_truncation',
                'category' => 'Kriptografi Aplikasi',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Mengapa aplikasi web perlu membatasi panjang maksimum input password (atau beralih ke Argon2id) ketika menggunakan algoritma hashing bcrypt standar?',
                'options' => [
                    'A' => 'Bcrypt menolak seluruh password yang mengandung simbol huruf kapital',
                    'B' => 'Bcrypt standar diam-diam memotong input di atas 72 byte (truncation) dan input super panjang tanpa batas dapat memicu serangan CPU DoS',
                    'C' => 'Bcrypt menyimpan password dalam bentuk AES-128 ECB yang bisa didekripsi bila lebih dari 32 byte',
                    'D' => 'Bcrypt tidak menghasilkan random salt unik untuk setiap akun pengguna',
                    'E' => 'Hash bcrypt otomatis kedaluwarsa setelah 90 hari',
                ],
                'answer' => 'B',
                'explanation' => 'Bcrypt hanya memproses 72 byte pertama sehingga dua passphrase panjang dengan awalan sama menghasilkan hash identik, sementara input ratusan kilobyte dapat membebani CPU server (Denial of Service).',
            ],
            [
                'key' => 'adv_csp_nonce',
                'category' => 'Keamanan Browser',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Mengapa header Content-Security-Policy (CSP) yang berisi "script-src \'self\' \'unsafe-inline\'" sebagian besar gagal menghentikan serangan Stored maupun Reflected XSS?',
                'options' => [
                    'A' => 'Karena \'self\' mengizinkan pemuatan skrip dari seluruh domain HTTPS publik',
                    'B' => 'Karena \'unsafe-inline\' tetap mengizinkan blok <script> dan atribut event seperti onerror= hasil injeksi penyerang untuk dieksekusi langsung',
                    'C' => 'Karena browser modern mengabaikan CSP jika situs sudah memakai HTTPS',
                    'D' => 'Karena CSP hanya melindungi file CSS dan tidak pernah membatasi JavaScript',
                    'E' => 'Karena \'unsafe-inline\' menonaktifkan flag HttpOnly pada cookie sesi',
                ],
                'answer' => 'B',
                'explanation' => 'Direktif \'unsafe-inline\' membuka pintu penuh bagi payload XSS inline. Mitigasi CSP yang kuat memanfaatkan cryptographic nonce unik per request (script-src \'nonce-...\') atau hash ketat tanpa unsafe-inline.',
            ],
            [
                'key' => 'adv_session_fixation',
                'category' => 'Manajemen Sesi',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Tepat setelah pengguna berhasil login atau melakukan reset password via OTP, dua tindakan manajemen sesi sisi server apa yang wajib dilakukan untuk mencegah Session Fixation dan pembajakan persisten?',
                'options' => [
                    'A' => 'Meng-encode username dengan Base64 di localStorage dan mematikan CSRF token',
                    'B' => 'Meregenerasi Session ID saat eskalasi/perubahan autentikasi serta mencabut seluruh sesi perangkat lain dan token API lama milik akun tersebut',
                    'C' => 'Mengatur SameSite=None tanpa atribut Secure serta masa aktif cookie 10 tahun',
                    'D' => 'Menampilkan Session ID mentah di parameter query URL pada setiap redirect',
                    'E' => 'Mengizinkan seluruh perangkat berbagi satu CSRF token statis yang sama',
                ],
                'answer' => 'B',
                'explanation' => 'Regenerasi Session ID mematahkan Session Fixation, sedangkan pencabutan seluruh sesi perangkat lain dan token API segera menendang keluar penyerang yang sempat masuk sebelumnya.',
            ],
            [
                'key' => 'adv_subdomain_takeover',
                'category' => 'Keamanan Infrastruktur',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Sebuah sekolah menghapus halaman kampanye lamanya di penyedia SaaS pihak ketiga tetapi lupa menghapus DNS CNAME "event.sekolah.sch.id -> app-lama.saas-host.net". Risiko kritis apa yang muncul?',
                'options' => [
                    'A' => 'Seluruh siswa kehilangan koneksi internet di jaringan Wi-Fi sekolah',
                    'B' => 'Subdomain Takeover: penyerang mengklaim "app-lama.saas-host.net", menyajikan situs phishing ber-HTTPS sah di bawah domain sekolah, dan berpotensi mencuri cookie wildcard',
                    'C' => 'Database MySQL utama milik sekolah terhapus otomatis oleh DNS',
                    'D' => 'DNSSEC otomatis memindahkan kepemilikan domain .sch.id ke pihak lain',
                    'E' => 'Browser otomatis memblokir domain utama sekolah.sch.id',
                ],
                'answer' => 'B',
                'explanation' => 'Dangling CNAME memungkinkan siapa pun mendaftarkan ulang target resource yang kosong di platform SaaS, mewarisi reputasi subdomain resmi, sertifikat HTTPS valid, serta akses cookie yang di-scope ke *.sekolah.sch.id.',
            ],
            [
                'key' => 'adv_idor_authz',
                'category' => 'Kontrol Akses (OWASP A01)',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Endpoint API GET /api/dokumen/1042 hanya mengecek apakah Bearer token valid lalu menjalankan Dokumen::find(1042). Mengubah 1042 ke 1043 menampilkan data warga lain. Apa nama celah ini dan perbaikan utamanya?',
                'options' => [
                    'A' => 'XSS; perbaiki dengan melakukan HTML escape pada output JSON',
                    'B' => 'CSRF; perbaiki dengan menambahkan header X-Frame-Options',
                    'C' => 'BOLA / IDOR (Broken Object Level Authorization); perbaiki dengan membatasi query berdasarkan kepemilikan user (Auth::id()) atau otorisasi Policy pada setiap request',
                    'D' => 'Clickjacking; perbaiki hanya dengan mengganti ID angka ke hash MD5 tanpa cek kepemilikan',
                    'E' => 'Open Redirect; perbaiki dengan memvalidasi header Referer',
                ],
                'answer' => 'C',
                'explanation' => 'Autentikasi hanya membuktikan identitas, bukan hak akses atas objek tertentu. Celah BOLA/IDOR wajib ditutup dengan validasi otorisasi/kepemilikan di sisi server pada setiap akses baca maupun ubah.',
            ],
            [
                'key' => 'adv_ransomware_32110',
                'category' => 'Ketahanan Insiden',
                'difficulty' => 'Lanjutan',
                'prompt' => 'Mengapa strategi pertahanan ransomware modern meningkatkan aturan backup klasik 3-2-1 menjadi aturan 3-2-1-1-0?',
                'options' => [
                    'A' => 'Agar kompresi backup berjalan dengan 0% beban CPU di flashdisk lokal',
                    'B' => 'Untuk menambahkan minimal 1 salinan offline/air-gapped atau immutable (WORM) agar ransomware berhak admin tidak dapat mengenkripsi backup, serta 0 error saat uji pemulihan',
                    'C' => 'Untuk menyimpan seluruh salinan backup di folder share SMB yang sama dengan server produksi',
                    'D' => 'Untuk mematikan enkripsi pada backup offsite supaya proses restore lebih cepat',
                    'E' => 'Untuk menjamin uptime 100% tanpa perlu pernah menguji proses restore backup',
                ],
                'answer' => 'B',
                'explanation' => 'Ransomware modern secara aktif mencari dan mengenkripsi backup online serta Volume Shadow Copies. Salinan immutable (Write-Once-Read-Many) / air-gapped ditambah verifikasi restore bebas error menjamin pemulihan tanpa membayar tebusan.',
            ],
        ];
    }
}
