<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'author', 'content', 'thumbnail', 'user_id', 'status', 'visit_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
