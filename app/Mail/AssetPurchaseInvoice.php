<?php

namespace App\Mail;

use App\Models\FileProductBill;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class AssetPurchaseInvoice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $userLocale;

    /**
     * Create a new message instance.
     */
    public function __construct(public FileProductBill $bill, ?string $locale = null)
    {
        $this->userLocale = $locale ?? config('app.locale', 'vi');
    }

    /**
     * Build the invoice number shown on the email.
     */
    public static function invoiceNumber(FileProductBill $bill): string
    {
        return self::formatInvoiceNumber($bill->getKey(), $bill->created_at);
    }

    /**
     * Format an invoice number from a file product bill id and its creation date.
     */
    public static function formatInvoiceNumber(int|string $billId, ?DateTimeInterface $createdAt): string
    {
        return 'HDA'.$createdAt?->format('Ymd').'-'.str_pad((string) $billId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Resolve the email address the invoice should be sent to: the buyer email entered at checkout, else the account email.
     */
    public static function recipientEmail(FileProductBill $bill): ?string
    {
        $email = data_get($bill->payos_data, 'request.buyerEmail') ?: $bill->client?->email;

        return filled($email) ? (string) $email : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        App::setLocale($this->userLocale);

        return new Envelope(
            subject: __('emails.asset_purchase_invoice.subject', ['code' => self::invoiceNumber($this->bill)]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        App::setLocale($this->userLocale);

        $this->bill->loadMissing(['fileProduct', 'client']);

        $subtotal = (float) $this->bill->total;
        $tax = (float) $this->bill->tax;

        return new Content(
            view: 'emails.asset-purchase-invoice',
            with: [
                'bill' => $this->bill,
                'invoiceNumber' => self::invoiceNumber($this->bill),
                'issuedAt' => $this->bill->updated_at ?? $this->bill->created_at,
                'buyerName' => data_get($this->bill->payos_data, 'request.buyerName') ?: $this->bill->client?->name,
                'buyerEmail' => self::recipientEmail($this->bill),
                'buyerPhone' => data_get($this->bill->payos_data, 'request.buyerPhone') ?: $this->bill->client?->phone,
                'productName' => $this->bill->fileProduct?->name,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'vatPercentage' => $this->bill->vatPercentage(),
                'grandTotal' => (float) ($this->bill->final_total ?? $subtotal + $tax),
                'paymentMethod' => $this->bill->payment_method?->label(),
                'paymentReference' => $this->bill->payos_payment_link_id,
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
