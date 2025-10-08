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
        $query = Map::with('layers'); // Cukup load relasi layers

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $maps = $query->latest()->get();
        
        // Logika loop untuk mengambil style dari pivot DIHAPUS.
        // Tampilan di-handle oleh view masing-masing.
        
        return view('maps.index', compact('maps'));
    }

    public function create()
    {
        $map = new Map();
        $layers = Layer::orderBy('nama_layer')->get();
        return view('maps.form', compact('map', 'layers'));
    }

    public function store(Request $request)
    {
        // Hapus 'kategori' dari validasi
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'layer_ids'   => 'nullable|array',
            'layer_ids.*' => 'exists:layers,id',
            'image_path'  => 'nullable|image|max:2048',
        ]);

        $data['kategori'] = 'Tidak';

        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_images'), $filename);
            $data['image_path'] = $filename;
        }
        
        $map = Map::create($data);

        if (!empty($data['layer_ids'])) {
            $map->layers()->sync($data['layer_ids']);
        }

        return redirect()->route('maps.index')->with('success', 'Peta baru berhasil dibuat!');
    }

    public function edit(Map $map)
    {
        $layers = Layer::orderBy('nama_layer')->get();
        $map->load('layers'); // Cukup load relasi layers
        
        return view('maps.form', compact('map', 'layers'));
    }

    public function update(Request $request, Map $map)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'layer_ids'   => 'nullable|array',
            'layer_ids.*' => 'exists:layers,id',
            'image_path'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image_path')) {
            if ($map->image_path && File::exists(public_path('map_images/' . $map->image_path))) {
                File::delete(public_path('map_images/' . $map->image_path));
            }
            
            $file = $request->file('image_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_images'), $filename);
            $data['image_path'] = $filename;
        }

        $map->update($data);

        $map->layers()->sync($request->input('layer_ids', []));

        return redirect()->route('maps.index')->with('success', 'Peta berhasil diperbarui!');
    }

    public function show(Map $map)
    {
        // Load relasi layers, dan di dalam setiap layer, load mapFeatures-nya.
        $map->load('layers.mapFeatures');
        
        return view('maps.show', compact('map'));
    }

    public function destroy(Map $map)
    {
        // Hapus relasi di tabel pivot
        $map->layers()->detach();

        // Hapus thumbnail peta
        if ($map->image_path && File::exists(public_path('map_images/' . $map->image_path))) {
            File::delete(public_path('map_images/' . $map->image_path));
        }

        // Hapus peta itu sendiri
        $map->delete();

        // TIDAK ADA LAGI ->features()->delete()

        return redirect()->route('maps.index')->with('success', 'Peta berhasil dihapus!');
    }

    public function visualisasi(Request $request)
    {
        $mapsForLegend = Map::with('layers')->where('kategori', 'Ya')->get();

        $activeLayerIds = $mapsForLegend->pluck('layers')->flatten()->pluck('id')->unique();

        // --- PERBAIKAN DIMULAI DI SINI ---
        $features = MapFeature::with('layer') // Ganti ke singular: 'layer'
            ->whereHas('layer', function ($query) use ($activeLayerIds) { // Ganti ke singular: 'layer'
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

                $imagePath = $feature->image_path;
                if ($imagePath && !filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $imagePath = asset($imagePath);
                }

                // Karena fitur sekarang hanya milik 1 layer, kita ambil ID-nya
                // dan masukkan ke dalam array agar struktur data untuk JS tetap sama.
                $layerIds = $feature->layer_id ? [$feature->layer_id] : [];

                return [
                    'type' => 'Feature',
                    'geometry' => $geometry,
                    'properties' => $properties,
                    'image_path' => $imagePath,
                    'caption' => $feature->caption ?? '',
                    'technical_info' => $technical_info,
                    'layer_ids' => $layerIds, // Gunakan variabel yang sudah diperbaiki
                ];
            });
        // --- AKHIR DARI PERBAIKAN ---

        return view('visualisasi.index', [
            'maps' => $mapsForLegend,
            'allFeatures' => $features,
        ]);
    }
    
    public function geojson(Map $map)
    {
        $map->load('layers.mapFeatures');

        $allFeatures = $map->layers->flatMap(function ($layer) {
            return $layer->mapFeatures;
        });

        $features = $allFeatures->map(function ($feature) {
            $properties = $feature->properties ?? [];
            if (is_string($properties)) $properties = json_decode($properties, true) ?: [];

            $properties['image_path'] = $feature->image_path ? asset($feature->image_path) : null;
            $properties['caption'] = $feature->caption;
            $properties['technical_info'] = $feature->technical_info;
            $properties['layer_id'] = $feature->layer_id; // Kirim layer_id agar frontend tahu asalnya

            $geometry = $feature->geometry;
            if (is_string($geometry)) $geometry = json_decode($geometry, true);
            
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

    public function setActive(Request $request, Map $map)
    {
        try {
            // Gunakan transaksi database untuk memastikan kedua operasi berhasil
            DB::transaction(function () use ($map) {
                // 1. Set semua peta menjadi 'Tidak'
                Map::query()->update(['kategori' => 'Tidak']);
                
                // 2. Set peta yang dipilih menjadi 'Ya'
                $map->update(['kategori' => 'Ya']);
            });

            return response()->json(['success' => true, 'message' => 'Peta utama berhasil diatur.']);

        } catch (\Exception $e) {
            // Kirim response error jika transaksi gagal
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui peta.'], 500);
        }
    }
}