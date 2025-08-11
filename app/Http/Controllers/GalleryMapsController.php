<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;

class GalleryMapsController extends Controller
{
    public function galeriPeta()
    {
        $maps = Map::with('kategori')
            ->whereHas('kategori', function ($query) {
                $query->where('nama_kategori', 'Galeri Peta');
            })
            ->get();

        return view('gallery_maps.index', compact('maps'));
    }

    public function show($id)
    {
        // Ambil 1 peta sesuai ID
        $map = Map::with('kategori')
            ->whereHas('kategori', function ($query) {
                $query->where('nama_kategori', 'Galeri Peta');
            })
            ->findOrFail($id);

        // Biar script JS tetap bisa pakai $maps->toJson(), bungkus jadi collection
        $maps = collect([$map]);

        // Decode geometry/features kalau masih string
        foreach ($maps as $m) {
            if (is_string($m->geometry)) {
                $m->geometry = json_decode($m->geometry);
            }
            if (isset($m->features) && is_string($m->features)) {
                $m->features = json_decode($m->features);
            }
        }

        return view('gallery_maps.show', compact('map', 'maps'));
    }


}
