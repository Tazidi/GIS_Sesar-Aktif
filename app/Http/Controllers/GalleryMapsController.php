<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;

class GalleryMapsController extends Controller
{
    public function index(Request $request)
    {
        $maps = Map::where('layer_type', '!=', 'sesar indonesia')->get();

        if ($request->ajax()) {
            return view('gallery_maps._partial', compact('maps'));
        }

        return view('gallery_maps.index', compact('maps'));
    }
}
