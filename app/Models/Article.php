<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'summary',
        'status',
        'reject_reason',
        'is_featured',
        'featured_image',
        'inputter_id',
        'authoriser_id',
        'category_id',
        'views_count',
        'shares_count',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'shares_count' => 'integer',
    ];



    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class);
    }
}
