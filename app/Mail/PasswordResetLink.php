<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class PasswordResetLink extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $resetToken;
    public string $userLocale;

    public function __construct(
        User $user,
        string $resetToken,
        string $locale = 'vi'
    ) {
        $this->user = $user;
        $this->resetToken = $resetToken;
        $this->userLocale = $locale;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->locale === 'en' 
            ? 'Reset Your Password'
            : 'Đặt lại mật khẩu của bạn';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $resetUrl = route('password.reset', [
            'token' => $this->resetToken,
            'email' => $this->user->email,
        ]);

        return new Content(
            view: 'emails.password-reset-link',
            with: [
                'user' => $this->user,
                'resetUrl' => $resetUrl,
                'locale' => $this->locale,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}