<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'deactivation_reason',
    ];

    public function getArticlesAttribute()
    {
        return Article::whereJsonContains('category_ids', $this->id)->get();
    }
}
