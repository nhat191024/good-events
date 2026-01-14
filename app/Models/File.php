<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model_id',
        'model_type',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'size',
        'path',
        'cached_zip_path',
        'cached_zip_generated_at',
        'cached_zip_hash',
    ];

    // Polymorphic relationship
    public function model()
    {
        return $this->morphTo();
    }
}
