<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layer extends Model
{
    protected $fillable = ['nama_layer', 'deskripsi'];
    
    public function maps()
    {
        return $this->belongsToMany(Map::class, 'layer_map');
    }
}
