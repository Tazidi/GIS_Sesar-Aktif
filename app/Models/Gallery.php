<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'title',
        'description',
        'category',
        'approved_by', 
        'last_edited_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lastEditedBy()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }
}