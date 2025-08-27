<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;

    public $timestamps = false;

    
    protected $guarded = ['id'];

    
    protected $casts = [
        'geometry' => 'array',
    ];

    
    public function layers()
    {
        return $this->belongsToMany(Layer::class, 'layer_map', 'map_id', 'layer_id')
                ->withPivot([
                    'layer_type',
                    'lat',
                    'lng',
                    'stroke_color',
                    'fill_color',
                    'weight',
                    'opacity',
                    'radius',
                    'icon_url'
                ]);
    }

   
    public function features()
    {
        return $this->hasMany(MapFeature::class);
    }
}
