<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerMedia extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'partner_service_id',
        'name',
        'url',
        'description',
    ];
}
