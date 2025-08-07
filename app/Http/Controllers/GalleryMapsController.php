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

}
