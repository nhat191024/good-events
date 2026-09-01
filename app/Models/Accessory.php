<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accessory extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];
}
