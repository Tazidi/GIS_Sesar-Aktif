<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id', 
        'nama',
        'deskripsi',
        'images',     
        'geometry'
    ];

    protected $casts = [
        'geometry' => 'array',
        'images' => 'array', 
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function primaryImage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->images[0] ?? null,
        );
    }

    public function additionalImages(): Attribute
    {
        return Attribute::make(
            get: fn () => array_slice($this->images, 1),
        );
    }
}
