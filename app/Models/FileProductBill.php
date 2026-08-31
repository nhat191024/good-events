<?php

namespace App\Models;

use App\Enum\FileProductBillStatus;
use App\Enum\PaymentMethod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $file_product_id
 * @property int $client_id
 * @property float $total
 * @property float|null $tax
 * @property float|null $final_total
 * @property int|null $tax_number
 * @property string|null $company_name
 * @property string|null $note
 * @property FileProductBillStatus $status
 * @property PaymentMethod $payment_method
 * @property int|null $payos_order_code
 * @property string|null $payos_payment_link_id
 * @property array<string, mixed>|null $payos_data
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $client
 * @property-read FileProduct|null $fileProduct
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereFileProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereFinalTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FileProductBill extends Model
{
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'file_product_id',
        'client_id',
        'total',
        'tax',
        'final_total',
        'tax_number',
        'company_name',
        'note',
        'status',
        'payment_method',
        'payos_order_code',
        'payos_payment_link_id',
        'payos_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => FileProductBillStatus::class,
            'payment_method' => PaymentMethod::class,
            'payos_order_code' => 'integer',
            'payos_data' => 'array',
        ];
    }

    /**
     * @param  array<string, mixed>  $paymentRequest
     * @param  array<string, mixed>|null  $paymentResponse
     */
    public function recordPayOSPayment(array $paymentRequest, ?array $paymentResponse = null): void
    {
        $payOSData = ['request' => $paymentRequest];

        if ($paymentResponse !== null) {
            $payOSData['response'] = $paymentResponse;
        }

        $this->forceFill([
            'payos_order_code' => $paymentRequest['billId'],
            'payos_payment_link_id' => $paymentResponse['paymentLinkId'] ?? null,
            'payos_data' => $payOSData,
        ])->save();
    }

    /**
     * Summary of getActivitylogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    // model relationships
    public function fileProduct()
    {
        return $this->belongsTo(FileProduct::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class);
    }
}
