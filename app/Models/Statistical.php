<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistical extends Model
{
    protected $table = 'statistics';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'metrics_name',
        'metrics_value',
        'metadata',
    ];

    //model relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
