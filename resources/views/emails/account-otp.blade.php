<!DOCTYPE html>
<html lang="{{ $localeCode }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digi-Safe Purwakarta</title>
</head>
<body style="margin:0; padding:0; background-color:#F8FAFC; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#0F172A; -webkit-font-smoothing:antialiased;">
    @php
        $isEn = $localeCode === 'en';
        $isDelete = $purpose === 'delete_account';
        $isRegister = $purpose === 'register';

        $appUrl = rtrim((string) config('app.url', ''), '/');
        $isPublicHttps = str_starts_with($appUrl, 'https://')
            && ! str_contains($appUrl, 'localhost')
            && ! str_contains($appUrl, '127.0.0.1');
        $logoUrl = ($isPublicHttps ? $appUrl : 'https://digisafepurwakarta.web.id') . '/images/logo.png';

        $headingText = match (true) {
            $isDelete && $isEn => 'Confirm account deletion',
            $isDelete => 'Konfirmasi penghapusan akun',
            $isRegister && $isEn => 'Verify your registration',
            $isRegister => 'Verifikasi pendaftaran akun',
            $isEn => 'Reset your password',
            default => 'Atur ulang kata sandi',
        };

        $introText = match (true) {
            $isDelete && $isEn => 'We received a request to permanently delete your Digi-Safe Purwakarta account. Enter the verification code below to confirm this action.',
            $isDelete => 'Kami menerima permintaan penghapusan permanen untuk akun Digi-Safe Purwakarta Anda. Masukkan kode verifikasi berikut untuk mengonfirmasi tindakan ini.',
            $isRegister && $isEn => 'Thank you for registering with Digi-Safe Purwakarta. Enter the 6-digit verification code below on the registration page to activate your new account.',
            $isRegister => 'Terima kasih telah mendaftar di Digi-Safe Purwakarta. Masukkan kode verifikasi 6 digit berikut pada halaman pendaftaran untuk mengaktifkan akun baru Anda.',
            $isEn => 'We received a request to reset the password for your Digi-Safe Purwakarta account. Enter the verification code below on the password reset page.',
            default => 'Kami menerima permintaan untuk mengatur ulang kata sandi akun Digi-Safe Purwakarta Anda. Masukkan kode verifikasi berikut pada halaman reset password.',
        };

        $noteText = match (true) {
            $isDelete && $isEn => 'This action is permanent. Once confirmed, your profile, scores, forum discussions, and messages will be removed and cannot be restored. If you did not request this, change your password immediately.',
            $isDelete => 'Tindakan ini bersifat permanen. Setelah dikonfirmasi, profil, skor minigame, diskusi forum, dan pesan Anda akan dihapus dan tidak dapat dipulihkan. Jika Anda tidak meminta hal ini, segera ubah kata sandi akun Anda.',
            $isRegister && $isEn => 'Do not share this code with anyone. If you did not attempt to register a Digi-Safe Purwakarta account, you can safely ignore this email.',
            $isRegister => 'Jangan bagikan kode ini kepada siapa pun. Jika Anda tidak merasa mendaftar akun Digi-Safe Purwakarta, abaikan email ini.',
            $isEn => 'Do not share this code with anyone. If you did not request a password reset, you can ignore this email and your password will remain unchanged.',
            default => 'Jangan bagikan kode ini kepada siapa pun. Jika Anda tidak mengajukan permintaan reset kata sandi, abaikan email ini. Kata sandi akun Anda tetap aman.',
        };

        $codeBorder = $isDelete ? '#FCA5A5' : ($isRegister ? '#93C5FD' : '#CBD5E1');
        $noteBorder = $isDelete ? '#DC2626' : ($isRegister ? '#1E63E9' : '#CBD5E1');
        $noteColor = $isDelete ? '#991B1B' : '#475569';
    @endphp

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#F8FAFC; padding:36px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px; background-color:#FFFFFF; border-radius:14px; border:1px solid #E2E8F0;">
                    <!-- Brand Row (Clean white, no colored banner) -->
                    <tr>
                        <td style="padding:24px 32px 20px 32px; border-bottom:1px solid #F1F5F9;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td valign="middle" style="padding-right:12px;">
                                        <img src="{{ $logoUrl }}" alt="Digi-Safe Purwakarta" width="32" height="32" style="display:block; width:32px; height:32px; object-fit:contain; border:0;">
                                    </td>
                                    <td valign="middle">
                                        <span style="font-size:15px; font-weight:700; color:#0F172A; letter-spacing:-0.01em;">
                                            Digi-Safe Purwakarta
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Main Body -->
                    <tr>
                        <td style="padding:28px 32px 24px 32px;">
                            <h1 style="margin:0 0 16px 0; font-size:20px; line-height:1.3; font-weight:700; color:#0F172A; letter-spacing:-0.02em;">
                                {{ $headingText }}
                            </h1>

                            <p style="margin:0 0 12px 0; font-size:14px; line-height:1.65; color:#334155;">
                                {{ $isEn ? 'Hello' : 'Halo' }} <strong style="color:#0F172A;">{{ $recipientName }}</strong>,
                            </p>

                            <p style="margin:0 0 24px 0; font-size:14px; line-height:1.65; color:#334155;">
                                {{ $introText }}
                            </p>

                            <!-- OTP Code Box -->
                            <div style="background-color:#F8FAFC; border:1.5px dashed {{ $codeBorder }}; border-radius:12px; padding:22px 20px; text-align:center; margin-bottom:24px;">
                                <div style="font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px;">
                                    {{ $isEn ? 'Verification Code' : 'Kode Verifikasi' }}
                                </div>
                                <div style="font-family:'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace; font-size:34px; font-weight:800; letter-spacing:10px; color:#0F172A; padding-left:10px; line-height:1.2;">
                                    {{ $otpCode }}
                                </div>
                                <div style="margin-top:12px; font-size:12px; color:#64748B;">
                                    {{ $isEn
                                        ? "Valid for {$expiryMinutes} minutes."
                                        : "Berlaku selama {$expiryMinutes} menit." }}
                                </div>
                            </div>

                            <!-- Concise Security Note -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="border-left:3px solid {{ $noteBorder }}; padding:2px 0 2px 14px; font-size:12.5px; line-height:1.6; color:{{ $noteColor }};">
                                        {{ $noteText }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Minimal Footer -->
                    <tr>
                        <td style="padding:18px 32px; border-top:1px solid #F1F5F9; font-size:11.5px; line-height:1.5; color:#94A3B8;">
                            &copy; {{ date('Y') }} Digi-Safe Purwakarta &middot; {{ $isEn ? 'Automated security email, please do not reply.' : 'Email keamanan otomatis, mohon tidak membalas pesan ini.' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
