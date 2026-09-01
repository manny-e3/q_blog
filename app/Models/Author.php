<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'email',
        'title',
        'bio',
        'expertise',
        'linkedin_url',
        'twitter_url',
        'facebook_url',
        'instagram_url',
        'website_url',
    ];

    protected $casts = [
        'expertise' => 'array',
    ];
}
