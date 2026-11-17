<?php

namespace App\Models;

use App\Enum\FileProductBillStatus;
use App\Enum\PaymentMethod;
use App\Mail\AssetPurchaseInvoice;
use App\Mail\WalletTopUpInvoice;
use Bavix\Wallet\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Read-only revenue ledger row, built on the fly from confirmed wallet top-ups and paid design asset bills.
 *
 * @property string $record_key
 * @property string $source
 * @property int $source_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $paid_at
 * @property int|null $buyer_id
 * @property string|null $buyer_name
 * @property string|null $buyer_email
 * @property string|null $buyer_phone
 * @property string|null $description
 * @property int|null $product_id
 * @property int $subtotal
 * @property int $vat_rate
 * @property int $vat_amount
 * @property int $total
 * @property string|null $payment_method
 * @property string|null $payment_reference
 * @property string|null $order_code
 * @property string|null $bank_reference
 * @property string|null $gateway_time
 * @property-read string $invoice_number
 * @property-read string $reference_code
 */
class RevenueRecord extends Model
{
    public const SOURCE_DEPOSIT = 'deposit';

    public const SOURCE_ASSET = 'asset';

    /**
     * Reason stored on wallet deposits made by partners through the QR payment flow. Admin top-ups,
     * sign-up bonuses and voucher refunds are also deposits but are not revenue, so they are excluded.
     */
    public const QR_TOP_UP_REASON = 'Nạp tiền vào ví qua QR';

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'revenue_records';

    protected $primaryKey = 'record_key';

    protected $keyType = 'string';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'paid_at' => 'datetime',
            'source_id' => 'integer',
            'product_id' => 'integer',
            'subtotal' => 'integer',
            'vat_rate' => 'integer',
            'vat_amount' => 'integer',
            'total' => 'integer',
        ];
    }

    /**
     * Eloquent query over the union of every revenue source.
     *
     * @return Builder<static>
     */
    public static function unionQuery(): Builder
    {
        return static::query()->fromSub(
            static::depositsQuery()->unionAll(static::assetBillsQuery()),
            (new static)->getTable(),
        );
    }

    /**
     * Confirmed wallet top-ups. Top-ups carry no VAT.
     */
    protected static function depositsQuery(): QueryBuilder
    {
        $meta = fn (string $path): string => static::json('t.meta', $path);

        return DB::table('transactions as t')
            ->leftJoin('users as u', 'u.id', '=', 't.payable_id')
            ->where('t.type', Transaction::TYPE_DEPOSIT)
            ->where('t.confirmed', true)
            ->where('t.meta->reason', self::QR_TOP_UP_REASON)
            ->whereNull('t.deleted_at')
            ->selectRaw(implode(', ', [
                "CONCAT('".self::SOURCE_DEPOSIT."-', t.id) as record_key",
                "'".self::SOURCE_DEPOSIT."' as source",
                't.id as source_id',
                't.created_at as created_at',
                't.updated_at as paid_at',
                'u.id as buyer_id',
                'u.name as buyer_name',
                'u.email as buyer_email',
                'u.phone as buyer_phone',
                $meta('reason').' as description',
                'CAST(NULL AS UNSIGNED) as product_id',
                'CAST(t.amount AS DECIMAL(20,0)) as subtotal',
                'CAST(0 AS DECIMAL(5,0)) as vat_rate',
                'CAST(0 AS DECIMAL(20,0)) as vat_amount',
                'CAST(t.amount AS DECIMAL(20,0)) as total',
                "'".PaymentMethod::QR_TRANSFER->value."' as payment_method",
                $meta('payos.payment_link_id').' as payment_reference',
                'CAST('.$meta('payos.order_code').' AS CHAR) as order_code',
                $meta('payos.webhook.reference').' as bank_reference',
                $meta('payos.webhook.transactionDateTime').' as gateway_time',
            ]));
    }

    /**
     * Design asset bills that have been paid. Buyer details prefer what was entered at checkout.
     */
    protected static function assetBillsQuery(): QueryBuilder
    {
        $payos = fn (string $path): string => static::json('f.payos_data', $path);

        return DB::table('file_product_bills as f')
            ->leftJoin('users as u', 'u.id', '=', 'f.client_id')
            ->leftJoin('file_products as p', 'p.id', '=', 'f.file_product_id')
            ->where('f.status', FileProductBillStatus::PAID->value)
            ->selectRaw(implode(', ', [
                "CONCAT('".self::SOURCE_ASSET."-', f.id) as record_key",
                "'".self::SOURCE_ASSET."' as source",
                'f.id as source_id',
                'f.created_at as created_at',
                'f.updated_at as paid_at',
                'u.id as buyer_id',
                'COALESCE(NULLIF('.$payos('request.buyerName').", ''), u.name) as buyer_name",
                'COALESCE(NULLIF('.$payos('request.buyerEmail').", ''), u.email) as buyer_email",
                'COALESCE(NULLIF('.$payos('request.buyerPhone').", ''), u.phone) as buyer_phone",
                'p.name as description',
                'f.file_product_id as product_id',
                'CAST(f.total AS DECIMAL(20,0)) as subtotal',
                'CAST(CASE WHEN f.total > 0 THEN ROUND(COALESCE(f.tax, 0) / f.total * 100) ELSE 0 END AS DECIMAL(5,0)) as vat_rate',
                'CAST(COALESCE(f.tax, 0) AS DECIMAL(20,0)) as vat_amount',
                'CAST(COALESCE(f.final_total, f.total + COALESCE(f.tax, 0)) AS DECIMAL(20,0)) as total',
                'f.payment_method as payment_method',
                'f.payos_payment_link_id as payment_reference',
                'CAST(f.payos_order_code AS CHAR) as order_code',
                $payos('webhook.reference').' as bank_reference',
                $payos('webhook.transactionDateTime').' as gateway_time',
            ]));
    }

    /**
     * Wrap a JSON path (dot notation) of a JSON column into a driver-specific scalar extraction.
     */
    protected static function json(string $column, string $path): string
    {
        return DB::getQueryGrammar()->wrap($column.'->'.str_replace('.', '->', $path));
    }

    /**
     * Invoice number as sent to the buyer by email, so accounting can match both.
     */
    public function getInvoiceNumberAttribute(): string
    {
        return $this->source === self::SOURCE_ASSET
            ? AssetPurchaseInvoice::formatInvoiceNumber($this->source_id, $this->created_at)
            : WalletTopUpInvoice::formatInvoiceNumber($this->source_id, $this->created_at);
    }

    /**
     * Human readable reference of the underlying record: wallet transaction id or asset order code.
     */
    public function getReferenceCodeAttribute(): string
    {
        return $this->source === self::SOURCE_ASSET
            ? 'FPB-'.$this->source_id
            : '#'.$this->source_id;
    }
}
