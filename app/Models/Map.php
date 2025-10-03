<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'description', 
        'map_type',
        'is_active',
        'image_path',
        'geometry',
        'kategori'
    ];
    
    protected $casts = [
        'geometry' => 'array',
        'is_active' => 'boolean',
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

    /**
     * Scope untuk map yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk map multi-layer
     */
    public function scopeMultiLayer($query)
    {
        return $query->where('map_type', 'multi_layer');
    }

    /**
     * Scope untuk map single-layer
     */
    public function scopeSingleLayer($query)
    {
        return $query->where('map_type', 'single_layer');
    }

    /**
     * Cek apakah map ini multi-layer
     */
    public function isMultiLayer()
    {
        return $this->map_type === 'multi_layer';
    }

    /**
     * Cek apakah map ini single-layer
     */
    public function isSingleLayer()
    {
        return $this->map_type === 'single_layer';
    }
}
