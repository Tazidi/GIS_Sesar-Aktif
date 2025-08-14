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
        $query = Map::with('layer')->leftJoin('layers', 'maps.layer_id', '=', 'layers.id')
            ->select('maps.*');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('maps.name', 'like', "%$search%")
                ->orWhere('maps.layer_type', 'like', "%$search%")
                ->orWhere('layers.nama_layer', 'like', "%$search%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $maps = $query->get();
        return view('maps.index', compact('maps'));
    }

    public function create()
    {
        $map = new Map();
        $layers = Layer::all(); // ambil dari model Layer, bukan dari kolom Map
        return view('maps.create', compact('map', 'layers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'layer_id' => 'nullable|exists:layers,id',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'distance' => 'nullable|numeric',
            'image_path' => 'nullable|image|max:2048',
            'icon_url' => 'nullable|string|max:255',
            'layer_type' => 'nullable|string|max:50',
            'stroke_color' => 'nullable|string|max:10',
            'fill_color' => 'nullable|string|max:10',
            'opacity' => 'nullable|numeric|between:0,1',
            'weight' => 'nullable|integer|min:0',
            'radius' => 'nullable|numeric|min:0',
            'geometry' => 'nullable|json',
            'file' => 'nullable|file|mimetypes:application/json,text/plain,text/json,text/geojson,text/csv,application/octet-stream|max:4096',
            'kategori' => 'required|in:Peta SISIRAJA,Galeri Peta,Peta SISIRAJA & Galeri Peta',
            // Tambahkan validasi untuk array properti jika diperlukan
            'feature_properties' => 'nullable|array',
        ]);

        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('map_images'), $imageName);
            $data['image_path'] = 'map_images/' . $imageName;
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_files'), $fileName);
            $data['file_path'] = 'map_files/' . $fileName;
        }

        $geojson = null;
        // Cek jika ada data geometri (dari file upload atau gambar manual)
        if ($request->filled('geometry')) {
            // Decode string JSON dari form menjadi array PHP
            $geojson = json_decode($request->geometry, true);
            $featureProperties = $request->input('feature_properties', []);

            // Pastikan format GeoJSON benar dan ada fitur di dalamnya
            if (isset($geojson['type']) && $geojson['type'] === 'FeatureCollection' && isset($geojson['features'])) {
                
                // Loop melalui setiap feature DENGAN REFERENSI (&) agar bisa diubah
                foreach ($geojson['features'] as $index => &$feature) {
                    // Cek apakah ada data properti untuk feature ini
                    if (isset($featureProperties[$index])) {
                        // Pastikan 'properties' ada, jika tidak, buat array kosong
                        if (!isset($feature['properties'])) {
                            $feature['properties'] = [];
                        }

                        // Gabungkan properti yang ada dengan properti teknis baru
                        // array_filter untuk menghapus nilai kosong (null, empty string) dari form
                        $newProperties = array_filter($featureProperties[$index]); 
                        if (!empty($newProperties)) {
                        $feature['properties'] = array_merge($feature['properties'], $newProperties);
                        }
                    }
                }
                // unset referensi setelah loop selesai (praktik terbaik)
                unset($feature);

                // Encode kembali array PHP yang sudah diperkaya menjadi string JSON
                // untuk disimpan di kolom 'geometry' tabel 'maps'
                $data['geometry'] = json_encode($geojson);
            }
        }
        // --- AKHIR BLOK LOGIKA BARU ---

        // Buat record Map dengan data yang sudah diperbarui (termasuk geometri yang sudah diperkaya)
        $map = Map::create($data);

        // Ambil file gambar dan caption dari request
        $featureImages = $request->file('feature_images', []);
        $featureCaptions = $request->input('feature_captions', []);

        // Proses penyimpanan untuk setiap MapFeature (menggunakan $geojson yang sudah diperbarui)
        if ($geojson && isset($geojson['features'])) {
            foreach ($geojson['features'] as $index => $feature) {
                if (!isset($feature['geometry'])) continue;

                $imagePath = null;
                if (isset($featureImages[$index]) && $featureImages[$index]->isValid()) {
                    $image = $featureImages[$index];
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('map_feature_images'), $imageName);
                    $imagePath = 'map_feature_images/' . $imageName;
                }

                $caption = $featureCaptions[$index] ?? null;

                // Buat MapFeature. Perhatikan 'properties' diambil langsung dari $feature yang sudah diperbarui.
                // Kolom 'technical_info' tidak lagi digunakan/diisi.
                MapFeature::create([
                    'map_id' => $map->id,
                    'geometry' => $feature['geometry'],
                    'properties' => $feature['properties'] ?? [], // <-- Menggunakan properti yang sudah diperkaya
                    'image_path' => $imagePath,
                    'caption' => $caption,
                ]);
            }
        }

        return redirect()->route('maps.index')->with('success', 'Peta berhasil ditambahkan!');
    }

    public function edit(Map $map)
    {
        $layers = Layer::all();
        return view('maps.edit', compact('map', 'layers'));
    }

    public function update(Request $request, Map $map)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'layer_id' => 'nullable|exists:layers,id',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'distance' => 'nullable|numeric',
            'image_path' => 'nullable|image|max:2048',
            'icon_url' => 'nullable|string|max:255',
            'layer_type' => 'nullable|string|max:50',
            'stroke_color' => 'nullable|string|max:10',
            'fill_color' => 'nullable|string|max:10',
            'opacity' => 'nullable|numeric|between:0,1',
            'weight' => 'nullable|integer|min:0',
            'radius' => 'nullable|numeric|min:0',
            'geometry' => 'nullable|json',
            'file' => 'nullable|file|mimes:json,csv,zip,geojson|max:4096',
            'kategori' => 'required|in:Peta SISIRAJA,Galeri Peta,Peta SISIRAJA & Galeri Peta',        
            'technical_info' => 'nullable|string',
        ]);

        if ($request->hasFile('image_path')) {
            if ($map->image_path && file_exists(public_path($map->image_path))) {
                unlink(public_path($map->image_path));
            }
            $image = $request->file('image_path');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('map_images'), $imageName);
            $data['image_path'] = 'map_images/' . $imageName;
        }

        if ($request->hasFile('file')) {
            if ($map->file_path && file_exists(public_path($map->file_path))) {
                unlink(public_path($map->file_path));
            }
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('map_files'), $fileName);
            $data['file_path'] = 'map_files/' . $fileName;
        }

        $map->update($data);

        return redirect()->route('maps.index')->with('success', 'Peta berhasil diperbarui!');
    }

    public function show(Map $map)
    {
        // === BARIS KODE YANG DITAMBAHKAN ===
        // Memuat relasi 'features' sebelum mengirimkan objek peta ke view.
        $map->load('features');

        // Mengirimkan objek peta yang sekarang berisi fitur-fitur terkait.
        return view('maps.show', compact('map'));
    }

    public function destroy(Map $map)
    {
        if ($map->image_path && file_exists(public_path($map->image_path))) {
            unlink(public_path($map->image_path));
        }

        if ($map->file_path && file_exists(public_path($map->file_path))) {
            unlink(public_path($map->file_path));
        }

        $map->delete();
        return redirect()->route('maps.index')->with('success', 'Peta berhasil dihapus!');
    }

    public function geojson(Map $map)
    {
        if ($map->geometry) {
            return response()->json([
                'type' => 'Feature',
                'geometry' => json_decode($map->geometry, true),
                'properties' => [
                    'name' => $map->name,
                    'description' => $map->description,
                    'layer_type' => $map->layer_type,
                    'photo' => $map->image_path ? asset($map->image_path) : null,
                ]
            ]);
        }

        if ($map->file_path && file_exists(public_path($map->file_path))) {
            $content = file_get_contents(public_path($map->file_path));

            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'File bukan JSON yang valid'], 400);
            }

            return response($content, 200)->header('Content-Type', 'application/json');
        }

        return response()->json(['error' => 'Tidak ada data GeoJSON yang tersedia'], 404);
    }

    public function visualisasi()
    {
        $maps = Map::with(['layer', 'features'])
            ->whereIn('kategori', ['Peta SISIRAJA', 'Peta SISIRAJA & Galeri Peta'])
            ->get();

        return view('visualisasi.index', compact('maps'));
    }

    public function updateKategori(Request $request, Map $map)
    {
        $request->validate([
            'kategori' => 'required|in:Peta SISIRAJA,Galeri Peta,Peta SISIRAJA & Galeri Peta'
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

}