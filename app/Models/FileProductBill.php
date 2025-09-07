<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileProductBill extends Model
{
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
