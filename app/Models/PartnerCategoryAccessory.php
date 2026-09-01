<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerCategoryAccessory extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'partner_category_id',
        'name',
        'surcharge',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'surcharge' => 'float',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PartnerCategory::class, 'partner_category_id');
    }
}
