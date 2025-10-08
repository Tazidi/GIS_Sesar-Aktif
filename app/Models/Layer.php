<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layer extends Model
{
    protected $fillable = ['nama_layer', 'deskripsi'];

    // Relasi ini tetap (Layer bisa ada di banyak Map)
    public function maps()
    {
        return $this->belongsToMany(Map::class, 'layer_map')
            ->withPivot([
                // styling default bisa tetap disini
                'layer_type', 'stroke_color', 'fill_color', 'weight', 'opacity', 'radius', 'icon_url'
            ]);
    }

    // Relasi ini berubah menjadi hasMany
    public function mapFeatures()
    {
        // Sebuah Layer memiliki banyak MapFeature
        return $this->hasMany(MapFeature::class);
    }
}