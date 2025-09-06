<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerProfile extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'partner_name',
        'identity_card_number',
    ];

    //model relationship

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
