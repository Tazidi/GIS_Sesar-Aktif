<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\Layer;
use App\Models\MapFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MapGeometryController extends Controller
{
    /**
     * Menampilkan halaman untuk mengelola geometri dalam map
     */
    public function index(Map $map)
    {
        $map->load(['features.layers']);
        $layers = Layer::all();

        return view('maps.geometries.index', compact('map', 'layers'));
    }

    /**
     * Menampilkan form untuk menambah geometri ke map
     */
    public function create(Map $map)
    {
        $layers = Layer::all();
        return view('maps.geometries.create', compact('map', 'layers'));
    }

    /**
     * Menyimpan geometri baru ke map.
     * Dapat menangani GeoJSON tunggal atau FeatureCollection.
     */
    public function store(Request $request, Map $map)
    {
        $data = $request->validate([
            'layer_ids' => 'required|array',
            'layer_ids.*' => 'exists:layers,id',
            'geometry_type' => 'required|in:marker,circle,polyline,polygon',
            'geometry' => 'required|json',
            'stroke_color' => 'nullable|string|max:7',
            'fill_color' => 'nullable|string|max:7',
            'weight' => 'nullable|numeric',
            'opacity' => 'nullable|numeric|min:0|max:1',
            'radius' => 'nullable|numeric',
            'icon_url' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'feature_image' => 'nullable|image|max:2048',
            'caption' => 'nullable|string|max:255',
            'properties' => 'nullable|json',
            'technical_info' => 'nullable',
        ]);

        $geojson = json_decode($data['geometry'], true);
        
        if ($data['geometry_type'] === 'circle' && isset($geojson['features'][0])) {
            $geojson['features'][0]['properties']['radius'] = $data['radius'] ?? 200;
        }
        $center = $this->extractCenterFromGeometry(
            $geojson['type'] === 'Feature' ? $geojson['geometry'] : $geojson
        );

        foreach ($request->layer_ids as $layerId) {
            $lat = $center['lat'] ?? null;
            $lng = $center['lng'] ?? null;
            // Jika geometry_type adalah circle dan geojson memiliki fitur, ambil center dari fitur pertama
            if ($data['geometry_type'] === 'circle' && isset($geojson['features'][0])) {
                $featureGeom = $geojson['features'][0]['geometry'] ?? null;
                if ($featureGeom && $featureGeom['type'] === 'Point') {
                    $lat = $featureGeom['coordinates'][1];
                    $lng = $featureGeom['coordinates'][0];
                }
            }
            $map->layers()->attach($layerId, [
                'layer_type'   => $request->input('geometry_type'),
                'lat'          => $lat,
                'lng'          => $lng,
                'stroke_color' => $request->input('stroke_color'),
                'fill_color'   => $request->input('fill_color'),
                'weight'       => $request->input('weight'),
                'opacity'      => $request->input('opacity'),
                'radius'       => $request->input('radius'),
                'icon_url'     => $request->input('icon_url'),
            ]);
        }

        $properties = json_decode($request->properties, true) ?? [];
        $rawTechnicalInfo = $request->input('technical_info', []);
        if (is_string($rawTechnicalInfo)) {
            $decodedTech = json_decode($rawTechnicalInfo, true);
            $rawTechnicalInfo = is_array($decodedTech) ? $decodedTech : [];
        }
        // Handle upload gambar fitur
        $imagePath = null;
        if ($request->hasFile('feature_image')) {
            $file = $request->file('feature_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_features'), $filename);
            $imagePath = 'map_features/'.$filename; // simpan path relatif ke public
        }

        DB::beginTransaction();
        try {
            // Cek apakah ini adalah FeatureCollection
            if (isset($geojson['type']) && $geojson['type'] === 'FeatureCollection' && isset($geojson['features'])) {    
                foreach ($geojson['features'] as $index => $feature) {
                    $featureProperties = $feature['properties'] ?? [];
                    $technicalKeys = ['panjang_sesar','lebar_sesar','tipe','mmax'];
                    $geojsonTechnicalInfo = [];
                    foreach ($technicalKeys as $key) {
                        if (array_key_exists($key, $featureProperties)) {
                            $geojsonTechnicalInfo[$key] = $featureProperties[$key];
                            unset($featureProperties[$key]); // supaya properties ori tetap bersih
                        }
                    }
                    $finalName = $featureProperties['name'] ?? $data['name'] ?? "Fitur #".($index+1);
                    $finalDescription = $featureProperties['description'] ?? $data['description'] ?? '';

                    // Handle foto per feature
                    $imagePath = $imagePath ?? null;
                    if ($request->hasFile("feature_images.$index")) {
                        $file = $request->file("feature_images.$index");
                        $filename = time() . "_{$index}_" . $file->getClientOriginalName();
                        $file->move(public_path('map_features'), $filename);
                        $imagePath = 'map_features/'.$filename;

                    }

                    $caption = $request->feature_captions[$index] ?? $finalName;
                    $reqTechnicalInfo = [];
                    if (is_array($rawTechnicalInfo)) {
                        if (array_keys($rawTechnicalInfo) === range(0, count($rawTechnicalInfo) - 1)) {
                            $reqTechnicalInfo = $rawTechnicalInfo[$index] ?? [];
                        } else {
                            $reqTechnicalInfo = $rawTechnicalInfo;
                        }
                    }

                    $featureTechnicalInfo = $this->prepareTechnicalInfo(
                        $data['geometry_type'],
                        $data,
                        $reqTechnicalInfo
                    );

                    $customProps = $request->input("feature_properties.$index", []);

                    $mergedTechnicalInfo = array_merge(
                        $geojsonTechnicalInfo,
                        is_array($reqTechnicalInfo) ? $reqTechnicalInfo : [],
                        is_array($customProps) ? $customProps : [],    // <-- **DITAMBAHKAN** agar input feature_properties tidak hilang
                        is_array($featureTechnicalInfo) ? $featureTechnicalInfo : [],
                        [
                            'name'          => $finalName,
                            'description'   => $finalDescription,
                            'geometry_type' => $data['geometry_type'],
                        ]
                    );

                    $feature = MapFeature::create([
                        'map_id'       => $map->id,
                        'geometry'     => $feature['geometry'],
                        'name'         => $finalName,           
                        'description'  => $finalDescription,    
                        'caption'      => $caption, 
                        'properties'   => $featureProperties,
                        'image_path'   => $imagePath,
                        'caption'      => $caption,
                        'technical_info' => json_encode($mergedTechnicalInfo)
                    ]);

                    $feature->layers()->sync($request->layer_ids);
                }

            } else {
                // Jika bukan FeatureCollection (geometri tunggal)
                $featureProperties = ($geojson['type'] === 'Feature' && isset($geojson['properties'])) 
                    ? $geojson['properties'] 
                    : [];
                    
                // Prioritaskan nama dari GeoJSON, jika tidak ada gunakan dari form
                $finalName = $featureProperties['name'] ?? $data['name'] ?? 'Geometri Tanpa Nama';
                
                // Prioritaskan deskripsi dari GeoJSON, jika tidak ada gunakan dari form
                $finalDescription = $featureProperties['description'] ?? $data['description'] ?? '';
                
                // Siapkan technical_info (jika ada)
                $rawTechnicalInfo = $request->input('technical_info', []);

                $featureTechnicalInfo = $this->prepareTechnicalInfo(
                    $data['geometry_type'], 
                    $data,
                    $rawTechnicalInfo
                );
                
                // Gabungkan properti lainnya dari GeoJSON dengan data dari form
                $mergedProperties = array_merge($featureProperties, [
                    'name' => $finalName,
                    'description' => $finalDescription,
                    'geometry_type' => $data['geometry_type'],
                ]);
                
                $feature = MapFeature::create([
                    'map_id'      => $map->id,
                    'geometry'    => ($geojson['type'] === 'Feature') ? $geojson['geometry'] : $geojson,
                    'name'        => $finalName,        
                    'description' => $finalDescription, 
                    'caption'     => $data['caption'] ?? $finalName,
                    'properties'  => $mergedProperties,
                    'image_path'  => $imagePath,
                    'technical_info' => json_encode($featureTechnicalInfo),
                ]);

                $feature->layers()->sync($request->layer_ids);
            }
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            if ($imagePath && file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan geometri: ' . $e->getMessage())
                ->withInput();
        }

        return redirect()->route('maps.geometries.index', $map)
            ->with('success', 'Geometri berhasil ditambahkan ke map!');
    }

    private function prepareTechnicalInfo($geometryType, $data, $customInfo = [])
    {
        $technicalInfo = [];
        
        // Tambahkan info kustom jika ada nilai
        if (!empty($customInfo)) {
            foreach ($customInfo as $key => $value) {
                if (!empty($value) || $value === '0') {
                    $technicalInfo[$key] = $value;
                }
            }
        }
        
        // Tambahkan info styling sesuai dengan tipe geometri
        if ($geometryType === 'marker') {
            if (!empty($data['icon_url'])) {
                $technicalInfo['icon_url'] = $data['icon_url'];
            }
        } else if ($geometryType === 'polyline') {
            if (!empty($data['stroke_color'])) $technicalInfo['stroke_color'] = $data['stroke_color'];
            if (!empty($data['weight'])) $technicalInfo['weight'] = $data['weight'];
            if (!empty($data['opacity'])) $technicalInfo['opacity'] = $data['opacity'];
        } else if ($geometryType === 'polygon') {
            if (!empty($data['stroke_color'])) $technicalInfo['stroke_color'] = $data['stroke_color'];
            if (!empty($data['fill_color'])) $technicalInfo['fill_color'] = $data['fill_color'];
            if (!empty($data['weight'])) $technicalInfo['weight'] = $data['weight'];
            if (!empty($data['opacity'])) $technicalInfo['opacity'] = $data['opacity'];
        } else if ($geometryType === 'circle') {
            if (!empty($data['radius'])) $technicalInfo['radius'] = $data['radius'];
            if (!empty($data['stroke_color'])) $technicalInfo['stroke_color'] = $data['stroke_color'];
            if (!empty($data['fill_color'])) $technicalInfo['fill_color'] = $data['fill_color'];
            if (!empty($data['weight'])) $technicalInfo['weight'] = $data['weight'];
            if (!empty($data['opacity'])) $technicalInfo['opacity'] = $data['opacity'];
        }
        
        return $technicalInfo;
    }

    /**
     * Menampilkan form edit untuk geometri dalam map
     */
    public function edit(Map $map, MapFeature $geometry)
    {
        if ($geometry->map_id !== $map->id) {
            return redirect()->route('maps.geometries.index', $map)
                ->with('error', 'Geometri tidak ditemukan dalam map ini');
        }

        // Ambil semua layer, bukan hanya yang terkait dengan geometry
        $layers = Layer::all();

        return view('maps.geometries.edit', compact('map', 'geometry', 'layers'));
    }

    /**
     * Update geometri dalam map
     */
    public function update(Request $request, Map $map, MapFeature $geometry)
    {
        if ($geometry->map_id !== $map->id) {
            return redirect()->route('maps.geometries.index', $map)
                ->with('error', 'Geometri tidak ditemukan dalam map ini');
        }
    
        $data = $request->validate([
            'layer_ids' => 'required|array',
            'layer_ids.*' => 'exists:layers,id',
            'geometry_type' => 'required|in:marker,circle,polyline,polygon',
            'geometry' => 'required|json',             
            'fill_color' => 'nullable|string|max:7',
            'weight' => 'nullable|numeric',
            'opacity' => 'nullable|numeric|min:0|max:1',
            'radius' => 'nullable|numeric',
            'icon_url' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'feature_images.*'   => 'nullable|image|max:2048',
            'feature_captions.*' => 'nullable|string|max:255',
            'feature_properties' => 'nullable|array',
            'feature_properties.*' => 'nullable|array',
            'properties' => 'nullable|json',
            'technical_info' => 'nullable',
            'caption' => 'nullable|string|max:255',
        ]);
        
        $geometry->layers()->detach();
        $geometryArray   = json_decode($data['geometry'], true);

        foreach ($request->layer_ids as $layerId) {
            $geometry->layers()->attach($layerId, [
                'layer_type'   => $request->input('geometry_type'),
                'lat'          => $geometryArray['coordinates'][1] ?? null,
                'lng'          => $geometryArray['coordinates'][0] ?? null,
                'stroke_color' => $request->input('stroke_color'),
                'fill_color'   => $request->input('fill_color'),
                'weight'       => $request->input('weight'),
                'opacity'      => $request->input('opacity'),
                'radius'       => $request->input('radius'),
                'icon_url'     => $request->input('icon_url'),
            ]);
        }

        // Ambil properties dari request (default array kosong)
        $propertiesArray = [];
        $rawProps = $request->input('properties');
        if ($rawProps) {
            $decoded = json_decode($rawProps, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $propertiesArray = $decoded;
            }
        }

        // Properti lama dari DB
        $existingProperties = is_array($geometry->properties)
            ? $geometry->properties
            : (json_decode($geometry->properties ?? '[]', true) ?: []);

        // Merge: request > existing > fallback ke kolom name/desc
        $mergedProperties = array_merge(
            $existingProperties,
            $propertiesArray,
            [
                'name'          => $data['name'] ?? ($existingProperties['name'] ?? $geometry->name),
                'description'   => $data['description'] ?? ($existingProperties['description'] ?? $geometry->description),
                'geometry_type' => $data['geometry_type'],
            ]
        );
        
        $imagePath = $geometry->image_path;
        if ($request->hasFile('feature_image')) {
            // Hapus gambar lama
            if ($imagePath && file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }
            $file = $request->file('feature_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_features'), $filename);
            $imagePath = 'map_features/'.$filename; // simpan path relatif ke public
        }

        // Ambil info teknis dari request (hanya jika ada nilai)
        $featureTechnicalInfo = [];
        foreach (['panjang_sesar','lebar_sesar','tipe','mmax'] as $field) {
            $val = $request->input($field);
            if ($val !== null && $val !== '') {
                $featureTechnicalInfo[$field] = $val;
            }
        }

        // Tambah styling info
        $featureTechnicalInfo = array_merge(
            $featureTechnicalInfo,
            $this->prepareTechnicalInfo($data['geometry_type'], $data)
        );

        $geometry->update([
            'geometry'       => $geometryArray,
            'properties'     => $mergedProperties ?: new \stdClass(), // jangan null, pakai object kosong
            'image_path'     => $imagePath ?? $geometry->image_path,
            'caption'        => $data['caption'] ?? $geometry->caption,
            'technical_info' => json_encode($featureTechnicalInfo),
        ]);

        $geometry->layers()->sync($request->layer_ids);

        return redirect()->route('maps.geometries.index', $map)
            ->with('success', 'Geometri berhasil diperbarui!');
    }

    /**
     * Hapus geometri dari map
     */
    public function destroy(Map $map, MapFeature $geometry, Request $request)
    {
        if ($geometry->map_id !== $map->id) {
            return redirect()->route('maps.geometries.index', $map)
                ->with('error', 'Geometri tidak ditemukan dalam map ini');
        }

        // Hapus relasi layer_map terlebih dahulu
        $geometry->layers()->detach();

        // Hapus file gambar terkait jika ada
        if ($geometry->image_path && file_exists(public_path($geometry->image_path))) {
            unlink(public_path($geometry->image_path));
        }

        $geometry->delete();

        return redirect()->route('maps.geometries.index', $map)
            ->with('success', 'Geometri berhasil dihapus dari map!');
    }

    public function bulkDestroy(Request $request, Map $map)
    {
        $request->validate([
            'feature_ids' => 'required|array',
            'feature_ids.*' => 'exists:map_features,id', 
        ]);

        // Hapus relasi layer_map untuk semua fitur yang akan dihapus
        foreach ($request->feature_ids as $featureId) {
            $feature = MapFeature::find($featureId);
            if ($feature && $feature->map_id === $map->id) {
                $feature->layers()->detach();
            }
        }

        // Hapus fitur-fitur
        MapFeature::where('map_id', $map->id)
            ->whereIn('id', $request->feature_ids)
            ->delete();

        return redirect()->route('maps.geometries.index', $map)
                        ->with('success', 'Grup geometri berhasil dihapus.');
    }

    /**
     * Helper method untuk mengekstrak center point dari geometry
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
            case 'Circle':
                if (isset($geometry['coordinates']) && is_array($geometry['coordinates'])) {
                    return [
                        'lat' => $geometry['coordinates'][1],
                        'lng' => $geometry['coordinates'][0]
                    ];
                }
                return null;
            default:
                return null;
        }
    }
}
