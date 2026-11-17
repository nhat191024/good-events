<?php

namespace App\Mail;

use App\Enum\PaymentMethod;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class WalletTopUpInvoice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $userLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(public Transaction $transaction, ?string $locale = null)
    {
        $this->userLocale = $locale ?? config('app.locale', 'vi');
    }

    /**
     * Build the invoice number shown on the email.
     */
    public static function invoiceNumber(Transaction $transaction): string
    {
        return self::formatInvoiceNumber($transaction->getKey(), $transaction->created_at);
    }

    /**
     * Format an invoice number from a wallet transaction id and its creation date.
     */
    public static function formatInvoiceNumber(int|string $transactionId, ?DateTimeInterface $createdAt): string
    {
        return 'HD'.$createdAt?->format('Ymd').'-'.str_pad((string) $transactionId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        App::setLocale($this->userLocale);

        return new Envelope(
            subject: __('emails.wallet_top_up_invoice.subject', ['code' => self::invoiceNumber($this->transaction)]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        App::setLocale($this->userLocale);

        $buyer = $this->transaction->payable;
        $meta = $this->transaction->meta ?? [];
        $amount = (int) data_get($meta, 'payos.request.amount', $this->transaction->amount);

        return new Content(
            view: 'emails.wallet-top-up-invoice',
            with: [
                'transaction' => $this->transaction,
                'buyer' => $buyer instanceof User ? $buyer : null,
                'invoiceNumber' => self::invoiceNumber($this->transaction),
                'issuedAt' => $this->transaction->updated_at ?? $this->transaction->created_at,
                'amount' => $amount,
                'paymentMethod' => PaymentMethod::QR_TRANSFER->label(),
                'paymentReference' => data_get($meta, 'payos.payment_link_id'),
                'oldBalance' => data_get($meta, 'old_balance'),
                'newBalance' => data_get($meta, 'new_balance'),
                'locale' => $this->userLocale,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
