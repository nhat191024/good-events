<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enum\ReportStatus;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $reported_user_id
 * @property int|null $reported_bill_id
 * @property int|null $thread_id
 * @property string $title
 * @property string $description
 * @property ReportStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PartnerBill|null $reportedBill
 * @property-read \App\Models\User|null $reportedUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportedBillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereUserId($value)
 * @mixin \Eloquent
 */
class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reported_bill_id',
        'thread_id',
        'title',
        'description',
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
            'status' => ReportStatus::class,
        ];
    }

    // Relationships
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id')->withTrashed();
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id')->withTrashed();
    }

    public function reportedBill()
    {
        return $this->belongsTo(PartnerBill::class, 'reported_bill_id');
    }
}
