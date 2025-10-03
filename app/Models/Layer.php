<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layer extends Model
{
    protected $fillable = ['nama_layer', 'deskripsi'];
    
    public function maps()
    {
        return $this->belongsToMany(Map::class, 'layer_map')
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
    public function mapFeatures()
{
    return $this->belongsToMany(MapFeature::class, 'feature_layer', 'layer_id', 'feature_id')
        ->withPivot([
            'layer_type',
            'stroke_color',
            'fill_color',
            'weight',
            'opacity',
            'radius',
            'icon_url'
        ]);
}
}
