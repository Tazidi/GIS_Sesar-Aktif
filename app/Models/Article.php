<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'author', 'content', 'category', 'tags', 'thumbnail', 'user_id', 'status', 'visit_count', 'approved_by', 'last_edited_by'
    ];
    
    /**
     * Accessor untuk mendapatkan tags sebagai array.
     * Penggunaan: $article->tags_array
     */
    public function getTagsAsArrayAttribute()
    {
        if ($this->tags) {
            // Menghapus spasi ekstra dan mengubah menjadi array
            return array_map('trim', explode(',', $this->tags));
        }
        return [];
    }

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
