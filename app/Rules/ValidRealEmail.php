<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ValidRealEmail implements ValidationRule
{
    /**
     * Daftar domain email sementara (disposable), typo umum, dan domain asal-asalan.
     */
    private const BLOCKED_DOMAINS = [
        // Disposable / temporary email providers
        'mailinator.com',
        'yopmail.com',
        'yopmail.fr',
        'yopmail.net',
        '10minutemail.com',
        '10minutemail.net',
        'guerrillamail.com',
        'guerrillamail.net',
        'guerrillamail.org',
        'guerrillamailblock.com',
        'sharklasers.com',
        'grr.la',
        'pokemail.net',
        'spam4.me',
        'tempmail.com',
        'temp-mail.org',
        'temp-mail.io',
        'tempmailo.com',
        'tempail.com',
        'throwawaymail.com',
        'trashmail.com',
        'trashmail.net',
        'getnada.com',
        'nada.email',
        'maildrop.cc',
        'fakeinbox.com',
        'fakemail.net',
        'emailfake.com',
        'mohmal.com',
        'dispostable.com',
        'mailnesia.com',
        'emailondeck.com',
        'mintemail.com',
        'mytemp.email',
        'moakt.com',
        'inboxbear.com',
        'burner.kiwi',
        'harakirimail.com',
        'mailcatch.com',
        'dropmail.me',
        'getairmail.com',
        'tmail.ws',
        'tmpmail.org',
        'tmpmail.net',

        // Placeholder / fake domains
        'example.com',
        'example.org',
        'example.net',
        'test.com',
        'testing.com',
        'fake.com',
        'dummy.com',
        'asdf.com',
        'qwerty.com',
        'abc.com',
        'xyz.com',
        'coba.com',
        'asal.com',
        'ngasal.com',
        'email.com',
        'mail.local',
        'localhost',

        // Common domain typos
        'gmail.con',
        'gmail.co',
        'gmail.cm',
        'gamil.com',
        'gmial.com',
        'gmal.com',
        'gnail.com',
        'yaho.com',
        'yahoo.con',
        'hotmal.com',
        'outlok.com',
    ];

    public function __construct(private readonly bool $checkDns = true)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isEn = app()->getLocale() === 'en';
        $email = Str::lower(trim((string) $value));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fail($isEn
                ? 'Please enter a valid email address.'
                : 'Format alamat email tidak valid.');

            return;
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            $fail($isEn
                ? 'Please enter a valid email address.'
                : 'Format alamat email tidak valid.');

            return;
        }

        [$local, $domain] = $parts;

        // Tolak local-part terlalu pendek (misal a@b.co) atau pola berulang ekstrem (aaaa@...)
        if (strlen($local) < 2 || str_contains($local, '..') || str_starts_with($local, '.') || str_ends_with($local, '.')) {
            $fail($isEn
                ? 'Please use a valid personal or institutional email address.'
                : 'Gunakan alamat email asli yang valid (bukan email asal-asalan).');

            return;
        }

        // Validasi struktur domain & TLD minimal 2 huruf
        if (
            ! str_contains($domain, '.')
            || str_contains($domain, '..')
            || ! preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,24}$/', $domain)
        ) {
            $fail($isEn
                ? 'Email domain format is invalid.'
                : 'Format domain email tidak valid.');

            return;
        }

        // Tolak domain disposable / sementara / typo / placeholder
        foreach (self::BLOCKED_DOMAINS as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.' . $blocked)) {
                $fail($isEn
                    ? 'Temporary, disposable, or invalid email domains are not allowed. Please use an active email address.'
                    : 'Email sementara, typo, atau domain asal-asalan tidak diperbolehkan. Gunakan email aktif seperti Gmail, Yahoo, Outlook, atau email sekolah/instansi.');

                return;
            }
        }

        // Verifikasi DNS MX agar hanya domain email sungguhan yang bisa mendaftar (non-unit-test)
        if ($this->checkDns && ! app()->runningUnitTests()) {
            $hasMx = @checkdnsrr($domain, 'MX');
            if (! $hasMx) {
                $fail($isEn
                    ? 'This email domain cannot receive emails. Please use a real, active email address.'
                    : 'Domain email ini tidak aktif atau tidak dapat menerima email. Gunakan alamat email yang valid dan aktif.');
            }
        }
    }
}
