<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'consent_given',
        'organisation',
        'role',
        'topics',
        'frequency',
        'status',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
        'topics' => 'array',
    ];
}
