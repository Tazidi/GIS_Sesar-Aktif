<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;

    // Anda bisa menggunakan $guarded atau $fillable, pilih salah satu. 
    // $fillable lebih eksplisit dan direkomendasikan.
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'image_path',
        'kategori',
        // 'geometry' dihapus dari sini karena geometri sekarang ada di MapFeature
    ];

    // Properti ini sudah tidak diperlukan jika menggunakan $fillable
    // protected $guarded = ['id']; 
    
    // Properti ini bisa diaktifkan jika Anda menambahkan timestamps ke tabel maps
    // public $timestamps = false;

    protected $casts = [
        'is_active' => 'boolean',
        // 'geometry' cast dihapus karena kolomnya sudah tidak relevan di tabel ini
    ];

    /**
     * Mendefinisikan relasi Many-to-Many ke model Layer.
     * Ini adalah relasi yang BENAR untuk arsitektur baru.
     */
    public function layers()
    {
        return $this->belongsToMany(Layer::class, 'layer_map', 'map_id', 'layer_id');
        
        // Pivot data bisa dihapus dari sini karena styling sekarang ada di level Fitur,
        // kecuali Anda ingin menyimpan styling default untuk layer di peta tertentu.
        // Untuk saat ini, kita biarkan untuk fleksibilitas.
        /*
            ->withPivot([
                'layer_type', 'lat', 'lng', 'stroke_color', 'fill_color',
                'weight', 'opacity', 'radius', 'icon_url'
            ]);
        */
    }

    /**
     * RELASI INI DIHAPUS KARENA MENJADI PENYEBAB ERROR.
     * Tabel 'map_features' tidak lagi memiliki kolom 'map_id'.
     * Untuk mendapatkan fitur, akses melalui relasi layers().
     */
    // public function features()
    // {
    //     return $this->hasMany(MapFeature::class);
    // }

    /**
     * Scope untuk map yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Metode dan scope lainnya di bawah ini sudah benar dan bisa dipertahankan.
    // ...
}