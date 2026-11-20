<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode,
        public string $purpose,
        public string $recipientName,
        public int $expiryMinutes = 10,
        public string $localeCode = 'id',
    ) {}

    public function envelope(): Envelope
    {
        $isEn = $this->localeCode === 'en';
        $isDelete = $this->purpose === 'delete_account';

        $subject = match (true) {
            $isDelete && $isEn => 'Account Deletion Code - Digi-Safe Purwakarta',
            $isDelete => 'Kode OTP Hapus Akun - Digi-Safe Purwakarta',
            $isEn => 'Password Reset Code - Digi-Safe Purwakarta',
            default => 'Kode OTP Reset Password - Digi-Safe Purwakarta',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-otp',
        );
    }
}
