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
use Illuminate\Support\Facades\Log;

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
 * @property int|null $voucher_id
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
 * @property-read \App\Models\Voucher|null $voucher
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerBill whereVoucherId($value)
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
        'voucher_id',
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
                PartnerBillStatus::PENDING => static::handlePendingStatus($partnerBill),
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
        $superAdmin = User::whereName('Super Admin')->first();
        $admin = User::whereName('Admin')->first();
        $mailService = new PartnerBillMailService();
        $mailService->sendOrderReceivedNotification($partnerBill);

        $clientId = $partnerBill->client_id;

        $stats = Statistical::whereUserId($clientId)
            ->whereMetricsName(StatisticType::ORDERS_PLACED->value)
            ->first();

        // Update client statistics
        if ($stats) {
            $stats->metrics_value = (int)$stats->metrics_value + 1;
            $stats->save();
        }

        //create thread for communication
        $partnerCategoryName = $partnerBill->category ? $partnerBill->category->name : 'General';
        $thread = Thread::create([
            'subject' => "$partnerBill->code - $partnerCategoryName"
        ]);

        try {
            Participant::create([
                'thread_id' => $thread->id,
                'user_id' => $admin->id, //system user
                'last_read' => now(),
            ]);

            Participant::create([
                'thread_id' => $thread->id,
                'user_id' => $partnerBill->client_id,
                'last_read' => null
            ]);
        } catch (\Throwable $th) {
            Log::error('From PartnerBill.php',$th->getMessage());
        }

        $partnerBill->thread_id = $thread->id;
        $partnerBill->saveQuietly();
    }

    /**
     * Handle pending bill status
     */
    protected static function handlePendingStatus(PartnerBill $partnerBill): void
    {
        //if admin rollback to pending from confirmed or in_job
        if ($partnerBill->thread_id) {
            $thread = Thread::find($partnerBill->thread_id);
            if ($thread) {
                $thread->delete();
                $partnerBill->thread_id = null;
            }

            $partnerBill->total = null;
            $partnerBill->final_total = null;
            $partnerBill->partner_id = null;
            $partnerBill->saveQuietly();
        }
    }

    /**
     * Handle completed bill status
     */
    protected static function handleCompletedStatus(PartnerBill $partnerBill): void
    {
        $partnerId = $partnerBill->partner_id;
        $clientId = $partnerBill->client_id;
        $finalTotal = $partnerBill->final_total;

        // Fetch all relevant statistics in one query for partner
        $partnerStats = Statistical::where('user_id', $partnerId)
            ->whereIn('metrics_name', [
                StatisticType::REVENUE_GENERATED->value,
                StatisticType::NUMBER_CUSTOMER->value,
                StatisticType::COMPLETED_ORDERS->value,
            ])
            ->get()
            ->keyBy('metrics_name');

        // Update partner statistics
        if ($stat = $partnerStats->get(StatisticType::REVENUE_GENERATED->value)) {
            $stat->metrics_value = (float)$stat->metrics_value + (float)$finalTotal;
            $stat->save();
        }

        if ($stat = $partnerStats->get(StatisticType::NUMBER_CUSTOMER->value)) {
            $stat->metrics_value = (int)$stat->metrics_value + 1;
            $stat->save();
        }

        if ($stat = $partnerStats->get(StatisticType::COMPLETED_ORDERS->value)) {
            $stat->metrics_value = (int)$stat->metrics_value + 1;
            $stat->save();
        }

        // Fetch all relevant statistics in one query for client
        $clientStats = Statistical::where('user_id', $clientId)
            ->whereIn('metrics_name', [
                StatisticType::TOTAL_SPENT->value,
                StatisticType::COMPLETED_ORDERS->value,
            ])
            ->get()
            ->keyBy('metrics_name');

        // Update client statistics
        if ($stat = $clientStats->get(StatisticType::TOTAL_SPENT->value)) {
            $stat->metrics_value = (float)$stat->metrics_value + (float)$finalTotal;
            $stat->save();
        }

        if ($stat = $clientStats->get(StatisticType::COMPLETED_ORDERS->value)) {
            $stat->metrics_value = (int)$stat->metrics_value + 1;
            $stat->save();
        }

        $thread = Thread::find($partnerBill->thread_id);
        if ($thread) {
            $thread->delete();
        }
    }

    /**
     * Handle cancelled bill status
     */
    protected static function handleCancelledStatus(PartnerBill $partnerBill): void
    {
        $partnerId = $partnerBill->partner_id;
        $clientId = $partnerBill->client_id;

        // Fetch all relevant statistics for both partner and client in one query
        $allStats = Statistical::whereIn('user_id', [$partnerId, $clientId])
            ->whereIn('metrics_name', [
                StatisticType::ORDERS_PLACED->value,
                StatisticType::COMPLETED_ORDERS->value,
                StatisticType::CANCELLED_ORDERS_PERCENTAGE->value,
            ])
            ->get()
            ->groupBy('user_id');

        // Update cancelled orders percentage statistic for both partner and client
        foreach ([$partnerId, $clientId] as $userId) {
            $userStats = $allStats->get($userId);
            if (!$userStats) {
                continue;
            }

            $userStatsKeyed = $userStats->keyBy('metrics_name');

            $totalOrdersStat = $userStatsKeyed->get(StatisticType::ORDERS_PLACED->value);
            $completedOrdersStat = $userStatsKeyed->get(StatisticType::COMPLETED_ORDERS->value);
            $cancelledOrdersPercentageStat = $userStatsKeyed->get(StatisticType::CANCELLED_ORDERS_PERCENTAGE->value);

            if ($cancelledOrdersPercentageStat) {
                $totalOrders = $totalOrdersStat ? (float)$totalOrdersStat->metrics_value : 0;
                $completedOrders = $completedOrdersStat ? (float)$completedOrdersStat->metrics_value : 0;

                $cancelledOrders = max($totalOrders - $completedOrders, 0);
                $cancelledPercentage = $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 2) : 0;

                $cancelledOrdersPercentageStat->metrics_value = $cancelledPercentage;
                $cancelledOrdersPercentageStat->save();
            }
        }

        $thread = Thread::find($partnerBill->thread_id);
        if ($thread) {
            $thread->delete();
        }
    }

    /**
     * Handle confirmed bill status
     */
    protected static function handleConfirmedStatus(PartnerBill $partnerBill): void
    {
        Participant::create([
            'thread_id' => $partnerBill->thread_id,
            'user_id' => $partnerBill->partner_id,
            'last_read' => null
        ]);

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

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
