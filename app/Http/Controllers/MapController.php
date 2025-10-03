<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use App\Models\Layer;
use App\Models\MapFeature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $query = Map::with('layers');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('maps.name', 'like', "%$search%")
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
                
                if ($map->geometry) {
                    $geometry = json_decode($map->geometry, true);
                    $center = $this->extractCenterFromGeometry($geometry);
                    if ($center) {
                        $map->lat = $center['lat'];
                        $map->lng = $center['lng'];
                    }
                }
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
            'layer_ids' => 'nullable|array', // Diubah menjadi nullable
            'layer_ids.*' => 'exists:layers,id',           
            'image_path' => 'nullable|image|max:2048',
            'kategori' => 'required|in:Ya,Tidak',
        ]);

        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_images'), $filename);
            $data['image_path'] = $filename;
        }
        
        $data['kategori'] = $data['kategori'];
        $data['map_type'] = 'multi_layer';

        $map = Map::create($data);

        // Attach layer ke map hanya jika layer_ids ada
        if (!empty($data['layer_ids'])) {
            $map->layers()->attach($data['layer_ids']);
        }

        return redirect()->route('maps.geometries.index', $map)
            ->with('success', 'Map berhasil dibuat! Sekarang Anda bisa menambahkan geometri.');
    }

    public function edit(Map $map)
    {
        $layers = Layer::all();
        $map->load('features');

        $firstLayer = $map->layers->first();
        if ($firstLayer) {
            $map->layer_type = $firstLayer->pivot->layer_type;
            $map->stroke_color = $firstLayer->pivot->stroke_color;
            $map->fill_color = $firstLayer->pivot->fill_color;
            $map->weight = $firstLayer->pivot->weight;
            $map->opacity = $firstLayer->pivot->opacity;
            $map->radius = $firstLayer->pivot->radius;
            $map->icon_url = $firstLayer->pivot->icon_url;
            
            if ($firstLayer->pivot->lat && $firstLayer->pivot->lng) {
                $map->lat = $firstLayer->pivot->lat;
                $map->lng = $firstLayer->pivot->lng;
            } elseif ($map->geometry) {
                $geometry = json_decode($map->geometry, true);
                $center = $this->extractCenterFromGeometry($geometry);
                if ($center) {
                    $map->lat = $center['lat'];
                    $map->lng = $center['lng'];
                }
            }
        }
        
        return view('maps.form', compact('map', 'layers'));
    }

    public function update(Request $request, Map $map)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'layer_ids' => 'nullable|array', // Diubah menjadi nullable
            'layer_ids.*' => 'exists:layers,id',
            'image_path' => 'nullable|image|max:2048',
            'layer_type' => 'nullable|string|max:50',
            'geometry' => 'nullable|json',
            'kategori' => 'required|in:Ya,Tidak',
            'fill_color' => 'nullable|string|max:7',
            'weight' => 'nullable|numeric',
            'opacity' => 'nullable|numeric|min:0|max:1',
            'radius' => 'nullable|numeric',
            'icon_url' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image_path')) {
            // Hapus gambar lama jika ada
            if ($map->image_path && File::exists(public_path('map_images/' . $map->image_path))) {
                File::delete(public_path('map_images/' . $map->image_path));
            }
            
            $file = $request->file('image_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_images'), $filename);
            $data['image_path'] = $filename;
        }

        $map->update($data);

        // Handle layer sync hanya jika layer_ids ada
        if ($request->has('layer_ids')) {
            $pivotData = [];
            $styleData = [
                'layer_type'   => $request->layer_type,
                'stroke_color' => $request->stroke_color,
                'fill_color'   => $request->fill_color,
                'weight'       => $request->weight,
                'opacity'      => $request->opacity,
                'radius'       => $request->radius,
                'icon_url'     => $request->icon_url,
            ];

            if ($request->filled('geometry')) {
                $geometry = json_decode($request->geometry, true);
                $center = $this->extractCenterFromGeometry($geometry);
                if ($center) {
                    $styleData['lat'] = $center['lat'];
                    $styleData['lng'] = $center['lng'];
                }
            } else {
                $firstLayer = $map->layers->first();
                if ($firstLayer && $firstLayer->pivot->lat && $firstLayer->pivot->lng) {
                    $styleData['lat'] = $firstLayer->pivot->lat;
                    $styleData['lng'] = $firstLayer->pivot->lng;
                }
            }

            foreach ($request->input('layer_ids') as $layerId) {
                $pivotData[$layerId] = $styleData;
            }

            $map->layers()->sync($pivotData);
        } else {
            // Jika tidak ada layer_ids, detach semua layer yang ada
            $map->layers()->detach();
        }
        
        if ($request->filled('geometry')) {
            $geojson = json_decode($request->geometry, true);

            if (isset($geojson['features'])) {
                foreach ($geojson['features'] as $index => $feature) {
                    $featureId = $request->input("feature_ids.$index"); 

                    if ($featureId) {
                        $mapFeature = $map->features()->find($featureId);
                    } else {
                        $mapFeature = new MapFeature(['map_id' => $map->id]);
                    }

                    $imagePath = $mapFeature->image_path;
                    if ($request->hasFile("feature_images.$index")) {
                        $imageFile = $request->file("feature_images.$index");
                        $imagePath = time() . "_{$index}_" . $imageFile->getClientOriginalName();
                        $imageFile->move(public_path('map_features'), $imagePath);
                    }

                    $technicalInfo = null;
                    if ($request->has("feature_properties.$index")) {
                        $technicalInfo = json_encode($request->input("feature_properties.$index"));
                    }

                    $mapFeature->geometry = $feature['geometry'] ?? null;
                    $mapFeature->properties = $feature['properties'] ?? [];
                    $mapFeature->image_path = $imagePath;
                    $mapFeature->caption = $request->input("feature_captions.$index") 
                                        ?? ($feature['properties']['caption'] ?? null);
                    $mapFeature->technical_info = $technicalInfo 
                                                ?? ($feature['properties']['technical_info'] ?? null);

                    $mapFeature->save();
                }

                $validIds = collect($request->input('feature_ids', []))->filter()->all();
                $map->features()->whereNotIn('id', $validIds)->delete();
            }
        } else {
            if ($request->has('feature_ids')) {
                foreach ($request->input('feature_ids') as $index => $featureId) {
                    $mapFeature = $map->features()->find($featureId);
                    if (!$mapFeature) continue;

                    $imagePath = $mapFeature->image_path;
                    if ($request->hasFile("feature_images.$index")) {
                        $imageFile = $request->file("feature_images.$index");
                        $imagePath = time() . "_{$index}_" . $imageFile->getClientOriginalName();
                        $imageFile->move(public_path('map_features'), $imagePath);
                    }

                    $technicalInfo = null;
                    if ($request->has("feature_properties.$index")) {
                        $technicalInfo = json_encode($request->input("feature_properties.$index"));
                    }

                    $mapFeature->image_path = $imagePath;
                    $mapFeature->caption = $request->input("feature_captions.$index", $mapFeature->caption);
                    $mapFeature->technical_info = $technicalInfo ?? $mapFeature->technical_info;

                    $mapFeature->save();
                }
            }
        }
        
        return redirect()->route('maps.index')->with('success', 'Peta berhasil diperbarui!');
    }

    public function show(Map $map)
    {
        $map->load('features', 'layers');
        
        $firstLayer = $map->layers->first();
        if ($firstLayer) {
            $map->layer_type = $firstLayer->pivot->layer_type;
            $map->stroke_color = $firstLayer->pivot->stroke_color;
            $map->fill_color = $firstLayer->pivot->fill_color;
            $map->weight = $firstLayer->pivot->weight;
            $map->opacity = $firstLayer->pivot->opacity;
            $map->radius = $firstLayer->pivot->radius;
            $map->icon_url = $firstLayer->pivot->icon_url;
        }
        
        return view('maps.show', compact('map'));
    }

    public function destroy(Map $map)
    {
        $map->layers()->detach();
        $map->features()->delete();

        if ($map->image_path && File::exists(public_path('map_images/' . $map->image_path))) {
            File::delete(public_path('map_images/' . $map->image_path));
        }

        $map->delete();
        return redirect()->route('maps.index')->with('success', 'Peta berhasil dihapus!');
    }

    public function visualisasi(Request $request)
    {
        $mapsForLegend = Map::with('layers')->where('kategori', 'Ya')->get();

        $activeLayerIds = $mapsForLegend->pluck('layers')->flatten()->pluck('id')->unique();

        $features = MapFeature::with('layers')
            ->whereHas('layers', function ($query) use ($activeLayerIds) {
                $query->whereIn('layers.id', $activeLayerIds);
            })
            ->get()
            ->filter(function ($feature) {
                $geometry = is_string($feature->geometry) ? json_decode($feature->geometry, true) : $feature->geometry;
                return $geometry && isset($geometry['type']) && isset($geometry['coordinates']);
            })
            ->map(function ($feature) {
                $geometry = is_string($feature->geometry) ? json_decode($feature->geometry, true) : $feature->geometry;
                $properties = is_string($feature->properties) ? json_decode($feature->properties, true) : ($feature->properties ?? []);
                $technical_info = is_string($feature->technical_info) ? json_decode($feature->technical_info, true) : ($feature->technical_info ?? []);

                // **PERBAIKAN: Ikuti pola gallery_maps/show – langsung asset jika relative path**
                // Asumsi DB sudah simpan dengan folder 'map_features/' (seperti contoh DB)
                $imagePath = $feature->image_path;
                if ($imagePath && !filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    // Jika relative (dari DB), langsung asset (tanpa append lagi)
                    $imagePath = asset($imagePath);
                    
                    // Fallback opsional: Jika DB simpan tanpa folder (data lama), append
                    // if (strpos($imagePath, 'map_features/') !== 0) {
                    //     $imagePath = asset('map_features/' . basename($imagePath));
                    // }
                }

                // Optional: Log untuk debug (hapus setelah test)
                // \Log::info('Processed Feature ID ' . $feature->id . ': Image Path = ' . ($imagePath ?? 'null'));

                return [
                    'type' => 'Feature',
                    'geometry' => $geometry,
                    'properties' => $properties,
                    'image_path' => $imagePath,
                    'caption' => $feature->caption ?? '',
                    'technical_info' => $technical_info,
                    'layer_ids' => $feature->layers->pluck('id')->toArray(),
                ];
            });

        return view('visualisasi.index', [
            'maps' => $mapsForLegend,
            'allFeatures' => $features,
        ]);
    }
    
    public function geojson(Map $map)
    {
        if ($map->features()->exists()) {
            $features = $map->features->map(function ($feature) {
                // pastikan properties array
                $properties = $feature->properties ?? [];
                if (is_string($properties)) {
                    $decoded = json_decode($properties, true);
                    $properties = is_array($decoded) ? $decoded : [];
                }

                $properties['image_path'] = $feature->image_path ? asset($feature->image_path) : null;
                $properties['caption'] = $feature->caption;
                $properties['technical_info'] = $feature->technical_info;
                $properties['layer_ids'] = $feature->layers->pluck('id')->toArray();

                // pastikan geometry valid
                $geometry = $feature->geometry;
                if (is_string($geometry)) {
                    $geometry = json_decode($geometry, true);
                }
                if (isset($geometry['lat'], $geometry['lng'])) {
                    $geometry = [
                        'type' => 'Point',
                        'coordinates' => [(float) $geometry['lng'], (float) $geometry['lat']],
                    ];
                }

                return [
                    'type' => 'Feature',
                    'geometry' => $geometry,
                    'properties' => $properties,
                ];
            });

            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $features,
            ]);
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => [],
        ]);
    }

    public function updateKategori(Request $request, Map $map)
    {
        $request->validate([
            'kategori' => 'required|in:Ya,Tidak'
        ]);

        $map->update([
            'kategori' => $request->kategori
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui',
            'kategori' => $map->kategori
        ]);
    }
    
    private function extractCenterFromGeometry($geometry)
    {
        if (!$geometry || !isset($geometry['coordinates'])) {
            return null;
        }

        $coordinates = $geometry['coordinates'];
        $lats = [];
        $lngs = [];
        
        $flattenCoords = function ($arr) use (&$lats, &$lngs, &$flattenCoords) {
            foreach ($arr as $item) {
                if (is_array($item) && count($item) === 2 && is_numeric($item[0]) && is_numeric($item[1])) {
                    $lngs[] = $item[0];
                    $lats[] = $item[1];
                } elseif (is_array($item)) {
                    $flattenCoords($item);
                }
            }
        };
        
        $flattenCoords($coordinates);
        
        if (!empty($lats) && !empty($lngs)) {
            return [
                'lat' => array_sum($lats) / count($lats),
                'lng' => array_sum($lngs) / count($lngs)
            ];
        }
        
        return null;
    }
}