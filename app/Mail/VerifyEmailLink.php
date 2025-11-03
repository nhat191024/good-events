<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailLink extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $verifyUrl;

    public string $userLocale;

    public function __construct(User $user, string $verifyUrl, string $locale = 'vi')
    {
        $this->user = $user;
        $this->verifyUrl = $verifyUrl;
        $this->userLocale = $locale;
    }

    public function envelope(): Envelope
    {
        $subject = $this->userLocale === 'en'
            ? 'Verify Your Email Address'
            : 'Xác thực địa chỉ email của bạn';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email-link',
            with: [
                'user' => $this->user,
                'verifyUrl' => $this->verifyUrl,
                'locale' => $this->userLocale,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
