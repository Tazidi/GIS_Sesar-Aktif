<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $fillable = [
        'name',
        'description',
        'layer_id',       // relasi foreign key ke tabel layers
        'lat',
        'lng',
        'distance',
        'image_path',
        'icon_url',
        'stroke_color',
        'fill_color',
        'opacity',
        'weight',
        'radius',
        'file_path',
        'layer_type',
    ];

    public $timestamps = false; // Ubah ke true jika ingin pakai created_at & updated_at

    /**
     * Relasi: Map milik satu Layer
     */
    public function layers()
    {
        return $this->belongsToMany(Layer::class, 'layer_map');
    }
    
    public function layer()
    {
        return $this->belongsTo(\App\Models\Layer::class);
    }

}
