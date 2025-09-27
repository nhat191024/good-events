<?php

namespace App\Mail;

use App\Models\PartnerBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class PartnerBillReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PartnerBill $partnerBill;
    public string $recipientType;
    public string $userLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(PartnerBill $partnerBill, string $recipientType, ?string $locale = null)
    {
        $this->partnerBill = $partnerBill;
        $this->recipientType = $recipientType;
        $this->userLocale = $locale ?? $this->determineUserLocale();
    }

    /**
     * Determine user locale based on recipient
     */
    private function determineUserLocale(): string
    {
        // Try to get locale from user preference (if available)
        $user = $this->recipientType === 'client' ? $this->partnerBill->client : $this->partnerBill->partner;

        // For now, use app locale. In future, can add language field to User model
        return $user && property_exists($user, 'language') ? $user->language : config('app.locale', 'vi');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Set locale for this email
        App::setLocale($this->userLocale);

        $subject = __('emails.partner_bill_reminder.subject', ['code' => $this->partnerBill->code]);

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Ensure locale is set for email content
        App::setLocale($this->userLocale);

        return new Content(
            view: 'emails.partner-bill-reminder',
            with: [
                'partnerBill' => $this->partnerBill,
                'recipientType' => $this->recipientType,
                'locale' => $this->userLocale,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
