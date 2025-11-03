<?php

namespace App\Models;

use App\Enum\PartnerBillStatus;
use App\Enum\StatisticType;

use App\Services\PartnerWidgetCacheService;
use App\Services\PartnerBillMailService;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Events\PartnerBillCreated;
use App\Events\NewThreadCreated;
use App\Events\PartnerBillStatusChanged;

use App\Jobs\SendPartnerReminder;

use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;

/**
 * @property int $id
 * @property string $code
 * @property string $address
 * @property string $phone
 * @property \Illuminate\Support\Carbon|null $date
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property float|null $total
 * @property float|null $final_total
 * @property int|null $event_id
 * @property string|null $custom_event
 * @property int|null $client_id
 * @property int|null $partner_id
 * @property int|null $category_id
 * @property string|null $note
 * @property PartnerBillStatus $status
 * @property int|null $thread_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\PartnerCategory|null $category
 * @property-read \App\Models\User|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerBillDetail> $details
 * @property-read int|null $details_count
 * @property-read \App\Models\Event|null $event
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\User|null $partner
 * @property-read Thread|null $thread
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereCustomEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereFinalTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill wherePartnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PartnerBill extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'address',
        'phone',
        'date',
        'start_time',
        'end_time',
        'total',
        'final_total',
        'event_id',
        'custom_event',
        'client_id',
        'partner_id',
        'category_id',
        'note',
        'status',
        'thread_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total' => 'float',
        'final_total' => 'float',
        'status' => PartnerBillStatus::class,
    ];

    /**
     * Summary of getActivitylogOptions
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('arrival_photo')
            ->singleFile() // Only allow 1 file
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
    }

    //model boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($partnerBill) {
            $partnerBill->code = 'PB' . date('Ymd') . rand(1000, 9999);
        });

        static::created(function ($partnerBill) {
            static::handleBillCreated($partnerBill);
        });

        static::updated(function ($partnerBill) {
            match ($partnerBill->status) {
                PartnerBillStatus::COMPLETED => static::handleCompletedStatus($partnerBill),
                PartnerBillStatus::CANCELLED => static::handleCancelledStatus($partnerBill),
                PartnerBillStatus::CONFIRMED => static::handleConfirmedStatus($partnerBill),
                default => null,
            };

            // Clear widget caches when bill is updated
            if ($partnerBill->partner_id) {
                PartnerWidgetCacheService::clearPartnerCaches($partnerBill->partner_id);
            }

            if ($partnerBill->isDirty('status')) {
                PartnerBillStatusChanged::dispatch($partnerBill);
            }
        });
    }

    /**
     * Handle bill created event
     * @param \App\Models\PartnerBill $partnerBill
     * @return void
     */
    protected static function handleBillCreated(PartnerBill $partnerBill): void
    {
        $mailService = new PartnerBillMailService();
        $mailService->sendOrderReceivedNotification($partnerBill);

        $partnerId = $partnerBill->partner_id;
        $clientId = $partnerBill->client_id;

        //update number of customer statistic for partner
        $existingStat = Statistical::where('user_id', $partnerId)
            ->where('metrics_name', StatisticType::NUMBER_CUSTOMER->value)
            ->first();

        if ($existingStat) {
            $existingStat->metrics_value = (int)$existingStat->metrics_value + 1;
            $existingStat->save();
        }

        //update orders placed statistic for both partner and client
        foreach ([$partnerId, $clientId] as $userId) {
            $existingClientStat = Statistical::where('user_id', $userId)
                ->where('metrics_name', StatisticType::ORDERS_PLACED->value)
                ->first();

            if ($existingClientStat) {
                $existingClientStat->metrics_value = (int)$existingClientStat->metrics_value + 1;
                $existingClientStat->save();
            }
        }

        // Clear widget caches
        PartnerWidgetCacheService::clearPartnerCaches($partnerId);
    }

    /**
     * Handle completed bill status
     */
    protected static function handleCompletedStatus(PartnerBill $partnerBill): void
    {
        $partnerId = $partnerBill->partner_id;
        $clientId = $partnerBill->client_id;
        $finalTotal = $partnerBill->final_total;

        //update total spent statistic for client
        $existingClientStat = Statistical::where('user_id', $clientId)
            ->where('metrics_name', StatisticType::TOTAL_SPENT->value)
            ->first();

        if ($existingClientStat) {
            $existingClientStat->metrics_value = (float)$existingClientStat->metrics_value + (float)$finalTotal;
            $existingClientStat->save();
        }

        //update completed orders statistic for both partner and client
        foreach ([$partnerId, $clientId] as $userId) {
            $existingCompletedOrdersStat = Statistical::where('user_id', $userId)
                ->where('metrics_name', StatisticType::COMPLETED_ORDERS->value)
                ->first();

            if ($existingCompletedOrdersStat) {
                $existingCompletedOrdersStat->metrics_value = (int)$existingCompletedOrdersStat->metrics_value + 1;
                $existingCompletedOrdersStat->save();
            }
        }
    }

    /**
     * Handle cancelled bill status
     */
    protected static function handleCancelledStatus(PartnerBill $partnerBill): void
    {
        $partnerId = $partnerBill->partner_id;
        $clientId = $partnerBill->client_id;

        //update cancelled orders percentage statistic for both partner and client
        foreach ([$partnerId, $clientId] as $userId) {
            $totalOrdersStat = Statistical::where('user_id', $userId)
                ->where('metrics_name', StatisticType::ORDERS_PLACED->value)
                ->first();

            $completedOderStat = Statistical::where('user_id', $userId)
                ->where('metrics_name', StatisticType::COMPLETED_ORDERS->value)
                ->first();

            $existingCancelledOrdersStat = Statistical::where('user_id', $userId)
                ->where('metrics_name', StatisticType::CANCELLED_ORDERS_PERCENTAGE->value)
                ->first();

            if ($existingCancelledOrdersStat) {
                $totalOrders = $totalOrdersStat ? (float)$totalOrdersStat->metrics_value : 0;
                $completedOrders = $completedOderStat ? (float)$completedOderStat->metrics_value : 0;

                $cancelledOrders = max($totalOrders - $completedOrders, 0);
                $cancelledPercentage = $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 2) : 0;

                $existingCancelledOrdersStat->metrics_value = $cancelledPercentage;
                $existingCancelledOrdersStat->save();
            }
        }
    }

    /**
     * Handle confirmed bill status
     */
    protected static function handleConfirmedStatus(PartnerBill $partnerBill): void
    {
        $partnerCategoryName = $partnerBill->category ? $partnerBill->category->name : 'General';
        $thread = Thread::create([
            'subject' => "$partnerBill->code - $partnerCategoryName"
        ]);

        Participant::create([
            'thread_id' => $thread->id,
            'user_id' => 1, //system user
            'last_read' => now(),
        ]);

        Participant::create([
            'thread_id' => $thread->id,
            'user_id' => $partnerBill->client_id,
            'last_read' => null
        ]);

        Participant::create([
            'thread_id' => $thread->id,
            'user_id' => $partnerBill->partner_id,
            'last_read' => null
        ]);

        Message::create([
            'thread_id' => $thread->id,
            'user_id' => 1, //system user
            'body' => __("global.system_message.order_confirmed", ['code' => $partnerBill->code])
        ]);

        $partnerBill->thread_id = $thread->id;
        $partnerBill->saveQuietly();

        $mailService = new PartnerBillMailService();
        $mailService->sendOrderConfirmedNotification($partnerBill);

        NewThreadCreated::dispatch($partnerBill);

        if ($partnerBill->date && $partnerBill->start_time) {
            $eventDateTime = $partnerBill->date->copy()->setTimeFrom($partnerBill->start_time);
            $reminderTime = $eventDateTime->copy()->subHours(2);

            if ($reminderTime->isFuture()) {
                SendPartnerReminder::dispatch($partnerBill)->delay($reminderTime);
            }
        }
    }

    //model helpers method
    public function isPaid(): bool
    {
        return $this->status === PartnerBillStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === PartnerBillStatus::CANCELLED;
    }

    public function isPending(): bool
    {
        return $this->status === PartnerBillStatus::PENDING;
    }

    //model relationship
    public function details()
    {
        return $this->hasMany(PartnerBillDetail::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function category()
    {
        return $this->belongsTo(PartnerCategory::class, 'category_id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id');
    }
}
