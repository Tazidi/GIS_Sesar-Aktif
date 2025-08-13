<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;

class GalleryMapsController extends Controller
{
    public function galeriPeta()
    {
        // ... (metode galeriPeta tidak berubah)
        $maps = Map::with(['layer', 'features'])
            ->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
            ->get();
        return view('gallery_maps.index', compact('maps'));
    }

    /**
     * Menampilkan detail peta tunggal.
     */
    public function show($id)
    {
        $map = Map::with(['layer', 'features'])
            ->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
            ->findOrFail($id);

        // Tambahkan URL publik untuk setiap feature
        $map->features->transform(function ($feature) {
            $feature->feature_image_path = $feature->image_path 
                ? asset($feature->image_path) 
                : null;
            $feature->caption = $feature->caption ?? null;
            $feature->technical_info = $feature->technical_info ?? null;
            return $feature;
        });

        // Bungkus jadi collection biar struktur sama seperti di index
        $maps = collect([$map]);

        return view('gallery_maps.show', compact('map', 'maps'));
    }

}