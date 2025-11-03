<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Taggable extends Model
{
    protected $table = 'taggables';

    /**
     * Get all tags that belongs to a model type. 
     * EXAMPLE: $tags = Taggable::getModelTags('FileProduct');
     * @param string $modelName - model name, eg: 'User', 'Category', 'Product'
     * @return Collection
     */
    public static function getModelTags(string $modelName)
    {
        $tagIds = DB::table('taggables')
            ->where('taggable_type', 'App\Models\\'.$modelName)
            ->select('taggable_id')
            ->distinct()
            ->get()
            ->pluck('taggable_id');

        $tags = Tag::whereIn('id', $tagIds)->get();

        return $tags;
    }
}
