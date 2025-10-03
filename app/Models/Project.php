<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $casts = [
        'show_in_gallery' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'show_in_gallery',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function surveyLocations(): HasMany
    {
        return $this->hasMany(SurveyLocation::class);
    }
}