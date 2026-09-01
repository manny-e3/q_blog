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
        'views_count',
        'shares_count',
        'category_ids',
        'tag_ids',
        'pending_changes',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'shares_count' => 'integer',
        'category_ids' => 'array',
        'tag_ids' => 'array',
        'pending_changes' => 'array',
    ];

    protected $appends = [
        'category',
        'categories',
        'tags',
    ];

    public function getCategoryAttribute()
    {
        return $this->categories->first();
    }

    public function getCategoriesAttribute()
    {
        $ids = $this->category_ids ?? [];
        return Category::whereIn('id', $ids)->get();
    }

    public function getTagsAttribute()
    {
        $ids = $this->tag_ids ?? [];
        return Tag::whereIn('id', $ids)->get();
    }

    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class);
    }
}
