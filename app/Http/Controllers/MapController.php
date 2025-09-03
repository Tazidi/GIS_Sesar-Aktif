<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use App\Models\Layer;
use App\Models\MapFeature;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage; // Tidak lagi digunakan untuk image_path
use Illuminate\Support\Facades\File; // Ganti Storage dengan File untuk operasi file di direktori public

class MapController extends Controller
{
    // ... (method index, create tidak ada perubahan) ...
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
            'layers' => 'required|array', 
            'layers.*' => 'exists:layers,id',
            'image_path' => 'nullable|image|max:2048',
            'layer_type' => 'nullable|string|max:50',
            'geometry' => 'nullable|json',
            'kategori' => 'required|in:Ya,Tidak',
            'stroke_color' => 'nullable|string|max:7',
            'fill_color' => 'nullable|string|max:7',
            'weight' => 'nullable|numeric',
            'opacity' => 'nullable|numeric|min:0|max:1',
            'radius' => 'nullable|numeric',
            'icon_url' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_images'), $filename);
            $data['image_path'] = $filename; // Simpan nama filenya saja
        }
        
        $data['kategori'] = $data['kategori']; // simpan langsung "Ya" atau "Tidak"

        $map = Map::create($data);

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
        }

        foreach ($request->input('layers') as $layerId) {
            $pivotData[$layerId] = $styleData;
        }

        $map->layers()->attach($pivotData);

        if ($request->filled('geometry')) {
            $geojson = json_decode($request->geometry, true);
            if (isset($geojson['features'])) {
                foreach ($geojson['features'] as $index => $feature) {
                    // handle upload file per feature
                    $imagePath = null;
                    if ($request->hasFile("feature_images.$index")) {
                        $imageFile = $request->file("feature_images.$index");
                        $imagePath = time() . "_{$index}_" . $imageFile->getClientOriginalName();
                        $imageFile->move(public_path('map_features'), $imagePath);
                    }

                    // ambil properti teknis dari form (array) kalau ada
                    $technicalInfo = null;
                    if ($request->has("feature_properties.$index")) {
                        $technicalInfo = json_encode($request->input("feature_properties.$index"));
                    }

                    MapFeature::create([
                        'map_id' => $map->id,
                        'geometry' => $feature['geometry'] ?? null,
                        'properties' => $feature['properties'] ?? [],
                        'image_path' => $imagePath ?? ($feature['properties']['image_path'] ?? null),
                        'caption' => $request->input("feature_captions.$index") 
                                    ?? ($feature['properties']['caption'] ?? null),
                        'technical_info' => $technicalInfo 
                                            ?? ($feature['properties']['technical_info'] ?? null),
                    ]);
                }
            }
        }

        return redirect()->route('maps.index')->with('success', 'Peta berhasil ditambahkan!');
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
            'layers' => 'required|array',
            'layers.*' => 'exists:layers,id',
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

        // === PERUBAHAN UNGGAHAN & HAPUS FILE DIMULAI DI SINI ===
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
        // === PERUBAHAN SELESAI ===

        $map->update($data);

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

        foreach ($request->input('layers') as $layerId) {
            $pivotData[$layerId] = $styleData;
        }

        $map->layers()->sync($pivotData);
        
        // === PERUBAHAN UNTUK UPDATE FEATURES ===
        if ($request->filled('geometry')) {
            $geojson = json_decode($request->geometry, true);

            if (isset($geojson['features'])) {
                foreach ($geojson['features'] as $index => $feature) {
                    $featureId = $request->input("feature_ids.$index"); 

                    // kalau ada id, update; kalau tidak ada id berarti fitur baru
                    if ($featureId) {
                        $mapFeature = $map->features()->find($featureId);
                    } else {
                        $mapFeature = new MapFeature(['map_id' => $map->id]);
                    }

                    // handle upload gambar baru
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

                // opsional: hapus fitur yang tidak dikirim di form
                $validIds = collect($request->input('feature_ids', []))->filter()->all();
                $map->features()->whereNotIn('id', $validIds)->delete();
            }
        } else {
            // === BLOK BARU: update fitur lama tanpa geojson baru ===
            if ($request->has('feature_ids')) {
                foreach ($request->input('feature_ids') as $index => $featureId) {
                    $mapFeature = $map->features()->find($featureId);
                    if (!$mapFeature) continue;

                    // gambar lama
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

                    // update hanya caption/gambar/info teknis
                    $mapFeature->image_path = $imagePath;
                    $mapFeature->caption = $request->input("feature_captions.$index", $mapFeature->caption);
                    $mapFeature->technical_info = $technicalInfo ?? $mapFeature->technical_info;

                    $mapFeature->save();
                }
            }
        }
        
        return redirect()->route('maps.index')->with('success', 'Peta berhasil diperbarui!');
    }

    // ... (method show tidak ada perubahan) ...
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

        // === PERUBAHAN HAPUS FILE DIMULAI DI SINI ===
        if ($map->image_path && File::exists(public_path('map_images/' . $map->image_path))) {
            File::delete(public_path('map_images/' . $map->image_path));
        }
        // === PERUBAHAN SELESAI ===

        $map->delete();
        return redirect()->route('maps.index')->with('success', 'Peta berhasil dihapus!');
    }

    public function visualisasi(Request $request)
    {
        $query = Map::with(['layers', 'features']);
        $query->where('kategori', 'Ya');

        // Tambahkan filter kategori kalau ada di query string (?kategori=...)
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $maps = $query->get();

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

        return view('visualisasi.index', compact('maps'));
    }
    
    public function geojson(Map $map)
    {
        if ($map->features()->exists()) {
            $features = $map->features->map(function ($feature) {
                $properties = $feature->properties ?? [];
                $properties['image_path'] = $feature->image_path;
                $properties['caption'] = $feature->caption;
                $properties['technical_info'] = $feature->technical_info;
                return [
                    'type' => 'Feature',
                    'geometry' => $feature->geometry,
                    'properties' => $properties
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