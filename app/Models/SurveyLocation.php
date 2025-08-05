<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyLocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nama',
        'deskripsi',
        'image', // Tambahkan 'image' di sini
        'geometry'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'geometry' => 'array',
    ];

    /**
     * Get the user that owns the survey location.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
