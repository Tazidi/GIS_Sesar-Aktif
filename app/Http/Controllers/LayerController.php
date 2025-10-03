<?php

namespace App\Http\Controllers;

use App\Models\Layer;
use App\Models\Map;
use App\Models\MapFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class LayerController extends Controller
{
    public function index()
    {
        $layers = Layer::withCount('mapFeatures')->with('mapFeatures')->paginate(9); // Paginate dengan 9 item per halaman
        $totalFeatures = MapFeature::count();
        $activeLayersCount = Layer::count();

        return view('layers.index', compact('layers', 'totalFeatures', 'activeLayersCount'));
    }

    public function show(Layer $layer)
    {
        $layer->load('mapFeatures');
        return view('layers.show', compact('layer'));
    }

    public function create()
    {
        $maps = Map::all();
        return view('layers.create', compact('maps'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_layer' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'map_id' => 'required|exists:maps,id',
            'geometry' => 'required|json',
            'geometry_type' => 'required|in:marker,circle,polyline,polygon',
            'geojson_file' => 'nullable|file|mimes:json,geojson',
            'feature_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'feature_captions.*' => 'nullable|string|max:255',
            'feature_properties.*' => 'nullable|array',
            'technical_info.*' => 'nullable|array',
            'stroke_color' => 'nullable|string|max:7',
            'fill_color' => 'nullable|string|max:7',
            'weight' => 'nullable|numeric',
            'opacity' => 'nullable|numeric|min:0|max:1',
            'radius' => 'nullable|numeric',
            'icon_url' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 1. Simpan Layer
            $layer = Layer::create([
                'nama_layer' => $request->nama_layer,
                'deskripsi' => $request->deskripsi,
            ]);

            // 2. Simpan Map Features
            // Cukup ambil data dari input 'geometry'. Sumbernya (manual/upload) sudah ditangani frontend.
            $geometryData = json_decode($request->geometry, true);
            
            // Jika karena suatu hal $geometryData kosong, coba fallback ke file (opsional, tapi aman)
            if (empty($geometryData) && $request->hasFile('geojson_file')) {
                $geometryData = json_decode(file_get_contents($request->file('geojson_file')->getPathname()), true);
            }

            // Jika masih kosong juga, throw error
            if (empty($geometryData) || !isset($geometryData['type'])) {
                throw new \Exception('Data geometri tidak valid atau kosong.');
            }

            $properties = [
                'name' => $request->nama_layer,
                'description' => $request->deskripsi,
            ];

            // Add styling properties based on geometry type
            if ($request->geometry_type === 'marker') {
                $properties['icon_url'] = $request->icon_url;
            } elseif ($request->geometry_type === 'polyline') {
                $properties['stroke_color'] = $request->stroke_color;
                $properties['weight'] = $request->weight;
                $properties['opacity'] = $request->opacity;
            } elseif ($request->geometry_type === 'polygon') {
                $properties['stroke_color'] = $request->stroke_color;
                $properties['fill_color'] = $request->fill_color;
                $properties['weight'] = $request->weight;
                $properties['opacity'] = $request->opacity;
            } elseif ($request->geometry_type === 'circle') {
                $properties['radius'] = $request->radius;
                $properties['stroke_color'] = $request->stroke_color;
                $properties['fill_color'] = $request->fill_color;
                $properties['weight'] = $request->weight;
                $properties['opacity'] = $request->opacity;
            }

            // Handle FeatureCollection
            if (isset($geometryData['type']) && $geometryData['type'] === 'FeatureCollection' && isset($geometryData['features'])) {
                foreach ($geometryData['features'] as $index => $feature) {
                    $featureProperties = $feature['properties'] ?? [];
                    
                    // Handle technical info dari GeoJSON properties
                    $technicalKeys = ['panjang_sesar', 'lebar_sesar', 'tipe', 'mmax'];
                    $geojsonTechnicalInfo = [];
                    foreach ($technicalKeys as $key) {
                        if (array_key_exists($key, $featureProperties)) {
                            $geojsonTechnicalInfo[$key] = $featureProperties[$key];
                            unset($featureProperties[$key]);
                        }
                    }

                    $finalName = $featureProperties['name'] ?? $request->nama_layer . " - Fitur #" . ($index + 1);
                    $finalDescription = $featureProperties['description'] ?? $request->deskripsi ?? '';

                    // Handle feature image
                    $imagePath = null;
                    if ($request->hasFile("feature_images.{$index}")) {
                        $file = $request->file("feature_images.{$index}");
                        $filename = time() . "_{$index}_" . $file->getClientOriginalName();
                        $file->move(public_path('map_features'), $filename);
                        $imagePath = 'map_features/' . $filename;
                    }

                    $caption = $request->input("feature_captions.{$index}") ?? $finalName;

                    // Handle technical info dari form
                    $formTechnicalInfo = $request->input("technical_info.{$index}", []);
                    $featurePropertiesInput = $request->input("feature_properties.{$index}", []);

                    // Gabungkan semua technical info
                    $mergedTechnicalInfo = array_merge(
                        $geojsonTechnicalInfo,
                        is_array($formTechnicalInfo) ? $formTechnicalInfo : [],
                        is_array($featurePropertiesInput) ? $featurePropertiesInput : [],
                        [
                            'geometry_type' => $request->geometry_type,
                        ]
                    );

                    // Gabungkan properties
                    $mergedProperties = array_merge(
                        $properties,
                        $featureProperties,
                        [
                            'name' => $finalName,
                            'description' => $finalDescription,
                        ]
                    );

                    // Create map feature
                    $mapFeature = MapFeature::create([
                        'map_id' => $request->map_id,
                        'geometry' => json_encode($feature['geometry']),
                        'properties' => json_encode($mergedProperties),
                        'image_path' => $imagePath,
                        'caption' => $caption,
                        'technical_info' => !empty($mergedTechnicalInfo) ? json_encode($mergedTechnicalInfo) : null,
                    ]);

                    // Attach layer to feature
                    $mapFeature->layers()->attach($layer->id, [
                        'layer_type' => $request->geometry_type,
                        'stroke_color' => $request->stroke_color,
                        'fill_color' => $request->fill_color,
                        'weight' => $request->weight,
                        'opacity' => $request->opacity,
                        'radius' => $request->radius,
                        'icon_url' => $request->icon_url,
                    ]);
                }
            } else {
                // Handle single geometry
                $geometry = $geometryData;
                
                // Jika geometry adalah Feature, extract geometry dan properties
                if (isset($geometryData['type']) && $geometryData['type'] === 'Feature') {
                    $geometry = $geometryData['geometry'];
                    $featureProperties = $geometryData['properties'] ?? [];
                } else {
                    $featureProperties = [];
                }

                $finalName = $featureProperties['name'] ?? $request->nama_layer;
                $finalDescription = $featureProperties['description'] ?? $request->deskripsi ?? '';

                // Handle feature image untuk single geometry
                $imagePath = null;
                if ($request->hasFile('feature_image')) {
                    $file = $request->file('feature_image');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('map_features'), $filename);
                    $imagePath = 'map_features/' . $filename;
                }

                $caption = $request->caption ?? $finalName;

                // Handle technical info untuk single geometry
                $formTechnicalInfo = $request->input('technical_info.0', []);
                $featurePropertiesInput = $request->input('feature_properties.0', []);

                // Gabungkan semua technical info
                $mergedTechnicalInfo = array_merge(
                    $this->extractTechnicalInfoFromProperties($featureProperties),
                    is_array($formTechnicalInfo) ? $formTechnicalInfo : [],
                    is_array($featurePropertiesInput) ? $featurePropertiesInput : [],
                    [
                        'geometry_type' => $request->geometry_type,
                    ]
                );

                // Gabungkan properties
                $mergedProperties = array_merge(
                    $properties,
                    $featureProperties,
                    [
                        'name' => $finalName,
                        'description' => $finalDescription,
                    ]
                );

                // Create map feature
                $mapFeature = MapFeature::create([
                    'map_id' => $request->map_id,
                    'geometry' => json_encode($geometry),
                    'properties' => json_encode($mergedProperties),
                    'image_path' => $imagePath,
                    'caption' => $caption,
                    'technical_info' => !empty($mergedTechnicalInfo) ? json_encode($mergedTechnicalInfo) : null,
                ]);

                // Attach layer to feature
                $mapFeature->layers()->attach($layer->id, [
                    'layer_type' => $request->geometry_type,
                    'stroke_color' => $request->stroke_color,
                    'fill_color' => $request->fill_color,
                    'weight' => $request->weight,
                    'opacity' => $request->opacity,
                    'radius' => $request->radius,
                    'icon_url' => $request->icon_url,
                ]);
            }

            DB::commit();

            return redirect()->route('layers.index')
                ->with('success', 'Layer dan fitur peta berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Layer $layer)
    {
        // Eager load relasi mapFeatures agar bisa diakses di view
        $layer->load('mapFeatures');
        
        // Ambil semua map untuk dropdown pilihan
        $maps = Map::all();
        
        return view('layers.edit', compact('layer', 'maps'));
    }

    public function update(Request $request, Layer $layer)
    {
        $validated = $request->validate([
            'nama_layer' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*.id' => 'required|exists:map_features,id',
            'features.*.name' => 'nullable|string|max:255',
            'features.*.description' => 'nullable|string',
            'features.*.geometry' => 'nullable|string', // json geometry
            'features.*.stroke_color' => 'nullable|string|max:7',
            'features.*.fill_color' => 'nullable|string|max:7',
            'features.*.weight' => 'nullable|numeric',
            'features.*.opacity' => 'nullable|numeric|min:0|max:1',
            'features.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'features.*.remove_image' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // update layer utama
            $layer->update([
                'nama_layer' => $validated['nama_layer'],
                'deskripsi' => $validated['deskripsi'] ?? null,
            ]);

            if ($request->has('features')) {
                foreach ($request->features as $featureId => $featureData) {
                    $mapFeature = \App\Models\MapFeature::findOrFail($featureId);

                    // update properties
                    $props = json_decode($mapFeature->properties, true) ?: [];
                    $props['name'] = $featureData['name'] ?? $props['name'] ?? '';
                    $props['description'] = $featureData['description'] ?? $props['description'] ?? '';

                    // update geometry
                    if (!empty($featureData['geometry'])) {
                        $mapFeature->geometry = $featureData['geometry'];
                    }

                    // handle image
                    if (isset($featureData['remove_image']) && $featureData['remove_image']) {
                        if ($mapFeature->image_path && file_exists(public_path($mapFeature->image_path))) {
                            unlink(public_path($mapFeature->image_path));
                        }
                        $mapFeature->image_path = null;
                    } elseif (isset($featureData['image']) && $featureData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $file = $featureData['image'];
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('map_features'), $filename);
                        $mapFeature->image_path = 'map_features/' . $filename;
                    }

                    $mapFeature->properties = json_encode($props);
                    $mapFeature->save();

                    // update pivot styling
                    $layer->mapFeatures()->updateExistingPivot($mapFeature->id, [
                        'stroke_color' => $featureData['stroke_color'] ?? '#3388ff',
                        'fill_color' => $featureData['fill_color'] ?? '#3388ff',
                        'weight' => $featureData['weight'] ?? 3,
                        'opacity' => $featureData['opacity'] ?? 0.5,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('layers.index')->with('success', 'Layer berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage())->withInput();
        }
    }

    private function createOrUpdateFeature(Request $request, Layer $layer, array $featureData, int $index, array $defaultProperties)
    {
        $geometry = $featureData['geometry'] ?? $featureData; // Handle single geometry vs feature
        $featureProperties = $featureData['properties'] ?? [];

        $finalName = $featureProperties['name'] ?? $request->nama_layer . " - Fitur #" . ($index + 1);
        $finalDescription = $featureProperties['description'] ?? $request->deskripsi ?? '';

        $imagePath = null;
        if ($request->hasFile("feature_images.{$index}")) {
            $file = $request->file("feature_images.{$index}");
            $filename = time() . "_{$index}_" . $file->getClientOriginalName();
            $path = $file->storeAs('map_features', $filename, 'public');
            $imagePath = 'storage/' . $path;
        }

        $caption = $request->input("feature_captions.{$index}") ?? $finalName;

        $formTechnicalInfo = $request->input("technical_info.{$index}", []);
        $featurePropertiesInput = $request->input("feature_properties.{$index}", []);

        $mergedTechnicalInfo = array_merge(
            $this->extractTechnicalInfoFromProperties($featureProperties),
            is_array($formTechnicalInfo) ? $formTechnicalInfo : [],
            is_array($featurePropertiesInput) ? $featurePropertiesInput : [],
            ['geometry_type' => $request->geometry_type]
        );

        $mergedProperties = array_merge(
            $defaultProperties,
            $featureProperties,
            ['name' => $finalName, 'description' => $finalDescription]
        );

        $mapFeature = MapFeature::create([
            'map_id' => $request->map_id,
            'geometry' => json_encode($geometry),
            'properties' => json_encode($mergedProperties),
            'image_path' => $imagePath,
            'caption' => $caption,
            'technical_info' => !empty($mergedTechnicalInfo) ? json_encode($mergedTechnicalInfo) : null,
        ]);

        $mapFeature->layers()->attach($layer->id, [
            'layer_type' => $request->geometry_type,
            'stroke_color' => $request->stroke_color,
            'fill_color' => $request->fill_color,
            'weight' => $request->weight,
            'opacity' => $request->opacity,
            'radius' => $request->radius,
            'icon_url' => $request->icon_url,
        ]);
    }

    public function destroy(Layer $layer)
    {
        DB::transaction(function () use ($layer) {
            // Muat relasi mapFeatures untuk memastikan kita bisa mengaksesnya
            $layer->load('mapFeatures');

            // Lakukan loop pada setiap fitur yang terhubung dengan layer ini
            foreach ($layer->mapFeatures as $feature) {
                // 1. Hapus file gambar yang terkait dengan fitur ini, jika ada.
                if ($feature->image_path && file_exists(public_path($feature->image_path))) {
                    unlink(public_path($feature->image_path));
                }

                // 2. Hapus record MapFeature dari database.
                // Ini akan secara otomatis menghapus relasi di pivot table juga.
                $feature->delete();
            }
            
            // 3. Setelah semua fitur terkait berhasil dihapus, hapus layer itu sendiri.
            $layer->delete();
        });

        // Redirect kembali ke halaman index dengan pesan sukses yang lebih jelas.
        return redirect()->route('layers.index')
               ->with('success', 'Layer dan semua fitur petanya berhasil dihapus.');
    }

    /**
     * Helper method untuk extract technical info dari properties
     */
    private function extractTechnicalInfoFromProperties($properties)
    {
        $technicalKeys = ['panjang_sesar', 'lebar_sesar', 'tipe', 'mmax'];
        $technicalInfo = [];
        
        foreach ($technicalKeys as $key) {
            if (array_key_exists($key, $properties)) {
                $technicalInfo[$key] = $properties[$key];
            }
        }
        
        return $technicalInfo;
    }

    /**
     * Helper method untuk extract center dari geometry
     */
    private function extractCenterFromGeometry($geometry)
    {
        if (!$geometry) return null;

        switch ($geometry['type']) {
            case 'Point':
                return [
                    'lat' => $geometry['coordinates'][1],
                    'lng' => $geometry['coordinates'][0]
                ];
            case 'Polygon':
            case 'LineString':
                $coordinates = $geometry['type'] === 'Polygon' 
                    ? $geometry['coordinates'][0] 
                    : $geometry['coordinates'];
                
                $latSum = 0;
                $lngSum = 0;
                $count = count($coordinates);
                
                foreach ($coordinates as $coord) {
                    $lngSum += $coord[0];
                    $latSum += $coord[1];
                }
                
                return [
                    'lat' => $latSum / $count,
                    'lng' => $lngSum / $count
                ];
            default:
                return null;
        }
    }
}