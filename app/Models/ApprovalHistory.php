<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
{
    use HasFactory;

    protected $table = 'approval_history';

    protected $fillable = [
        'article_id',
        'authoriser_id',
        'action',
        'reason',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }


}
