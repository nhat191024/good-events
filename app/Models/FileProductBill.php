<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Enum\FileProductBillStatus;

/**
 * @property int $id
 * @property int $file_product_id
 * @property int $client_id
 * @property float $total
 * @property float|null $final_total
 * @property FileProductBillStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User $client
 * @property-read \App\Models\FileProduct $fileProduct
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereFileProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereFinalTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProductBill whereUpdatedAt($value)
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
        'final_total',
        'status',
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
        ];
    }

    /**
     * Summary of getActivitylogOptions
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    //model relationships
    public function fileProduct()
    {
        return $this->belongsTo(FileProduct::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class);
    }
}
