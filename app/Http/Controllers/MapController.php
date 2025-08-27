<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use App\Models\Layer;
use App\Models\MapFeature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $query = Map::with('layers');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('maps.name', 'like', "%$search%")
                  ->orWhere('maps.layer_type', 'like', "%$search%")
                  ->orWhereHas('layers', function ($layerQuery) use ($search) {
                      $layerQuery->where('nama_layer', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $maps = $query->latest()->get();
        
    foreach ($maps as $map) {
        
        $firstLayer = $map->layers->first();

        
        if ($firstLayer) {
            $map->layer_type   = $firstLayer->pivot->layer_type;
            $map->stroke_color = $firstLayer->pivot->stroke_color;
            $map->fill_color   = $firstLayer->pivot->fill_color;
            $map->weight       = $firstLayer->pivot->weight;
            $map->opacity      = $firstLayer->pivot->opacity;
            $map->radius       = $firstLayer->pivot->radius;
            $map->icon_url     = $firstLayer->pivot->icon_url;
        }
    }
        return view('maps.index', compact('maps'));
    }

    public function create()
    {
        $map = new Map();
        $layers = Layer::all();
        return view('maps.form', compact('map', 'layers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'layers' => 'required|array', 
            'layers.*' => 'exists:layers,id',
            'image_path' => 'nullable|image|max:2048',
            'layer_type' => 'nullable|string|max:50',
            'geometry' => 'nullable|json',
            'kategori' => 'required|in:Peta SISIRAJA,Galeri Peta,Peta SISIRAJA & Galeri Peta',
            'feature_properties' => 'nullable|array',
        ]);

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('map_images', 'public');
        }

        $map = Map::create($data);

        $pivotData = [];
    $styleData = [
        'layer_type'   => $request->layer_type,
        'lat'          => $request->lat,
        'lng'          => $request->lng,
        'stroke_color' => $request->stroke_color,
        'fill_color'   => $request->fill_color,
        'weight'       => $request->weight,
        'opacity'      => $request->opacity,
        'radius'       => $request->radius,
        'icon_url'     => $request->icon_url,
    ];

    foreach ($request->input('layers') as $layerId) {
        $pivotData[$layerId] = $styleData;
    }

    $map->layers()->attach($pivotData);

        if ($request->filled('geometry')) {
            $geojson = json_decode($request->geometry, true);
            if (isset($geojson['features'])) {
                foreach ($geojson['features'] as $index => $feature) {
                    MapFeature::create([
                        'map_id' => $map->id,
                        'geometry' => $feature['geometry'] ?? null,
                        'properties' => $feature['properties'] ?? [],
                    ]);
                }
            }
        }

        return redirect()->route('maps.index')->with('success', 'Peta berhasil ditambahkan!');
    }

    public function edit(Map $map)
    {
        $layers = Layer::all();
        return view('maps.form', compact('map', 'layers'));
    }

    public function update(Request $request, Map $map)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'layers' => 'required|array',
            'layers.*' => 'exists:layers,id',
            'image_path' => 'nullable|image|max:2048',
            'layer_type' => 'nullable|string|max:50',
            'geometry' => 'nullable|json',
            'kategori' => 'required|in:Peta SISIRAJA,Galeri Peta,Peta SISIRAJA & Galeri Peta',
            'lat' => 'nullable|numeric',
    'lng' => 'nullable|numeric',
    'radius' => 'nullable|numeric',
    'weight' => 'nullable|numeric',
    'opacity' => 'nullable|numeric|min:0|max:1',
    'stroke_color' => 'nullable|string|max:7',
    'fill_color' => 'nullable|string|max:7',
    'icon_url' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image_path')) {
            if ($map->image_path) {
                Storage::disk('public')->delete($map->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('map_images', 'public');
        }

        $map->update($data);

        $pivotData = [];
    $styleData = [
        'layer_type'   => $request->layer_type,
        'lat'          => $request->lat,
        'lng'          => $request->lng,
        'stroke_color' => $request->stroke_color,
        'fill_color'   => $request->fill_color,
        'weight'       => $request->weight,
        'opacity'      => $request->opacity,
        'radius'       => $request->radius,
        'icon_url'     => $request->icon_url,
    ];

    foreach ($request->input('layers') as $layerId) {
        $pivotData[$layerId] = $styleData;
    }

    $map->layers()->sync($pivotData);
        
        return redirect()->route('maps.index')->with('success', 'Peta berhasil diperbarui!');
    }

    public function show(Map $map)
    {
        $map->load('features', 'layers');
        return view('maps.show', compact('map'));
    }

    public function destroy(Map $map)
    {
        $map->layers()->detach();
        $map->features()->delete();

        if ($map->image_path) {
            Storage::disk('public')->delete($map->image_path);
        }

        $map->delete();
        return redirect()->route('maps.index')->with('success', 'Peta berhasil dihapus!');
    }

    public function visualisasi()
    {
        $maps = Map::with(['layers', 'features'])->get();
        return view('visualisasi.index', compact('maps'));
    }
    
    public function geojson(Map $map)
    {
        if ($map->features()->exists()) {
            $features = $map->features->map(function ($feature) {
                return [
                    'type' => 'Feature',
                    'geometry' => $feature->geometry,
                    'properties' => $feature->properties
                ];
            });

            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $features
            ]);
        }
        
        if ($map->geometry) {
             return response()->json(json_decode($map->geometry));
        }

        return response()->json(['error' => 'Tidak ada data GeoJSON yang tersedia'], 404);
    }

    public function updateKategori(Request $request, Map $map)
    {
        $request->validate([
            'kategori' => 'required|in:Peta SISIRAJA,Galeri Peta,Peta SISIRAJA & Galeri Peta'
        ]);

        $map->update(['kategori' => $request->kategori]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui',
            'kategori' => $map->kategori
        ]);
    }
}
