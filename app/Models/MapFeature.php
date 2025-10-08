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
        'layer_id', // PENTING: map_id diganti menjadi layer_id
        'geometry',
        'properties',
        'image_path',
        'caption',
        'technical_info',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     *
     * @var array
     */
    protected $casts = [
        'geometry' => 'array',
        'properties' => 'array',
        'technical_info' => 'array',
    ];

    /**
     * Mendapatkan data layer yang memiliki fitur ini.
     * Setiap fitur sekarang dimiliki oleh SATU layer.
     */
    public function layer()
    {
        return $this->belongsTo(Layer::class);
    }

    // Fungsi map() dan layers() yang lama dihapus.
}