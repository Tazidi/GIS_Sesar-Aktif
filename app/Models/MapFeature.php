<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapFeature extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'map_features';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'map_id',
        'geometry',
        'properties',
        'image_path',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     * Ini sangat penting untuk menangani kolom JSON.
     *
     * @var array
     */
    protected $casts = [
        'geometry' => 'array',   // Otomatis konversi JSON string ke array/object
        'properties' => 'array', // Otomatis konversi JSON string ke array/object
    ];

    /**
     * Mendapatkan data peta (map) yang memiliki fitur ini.
     */
    public function map()
    {
        return $this->belongsTo(Map::class);
    }
}