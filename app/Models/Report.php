<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enum\ReportStatus;

class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'reported_user_id',
        'reported_bill_id',
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reportedBill()
    {
        return $this->belongsTo(PartnerBill::class, 'reported_bill_id');
    }
}
