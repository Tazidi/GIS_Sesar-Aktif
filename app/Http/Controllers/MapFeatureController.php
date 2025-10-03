<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\MapFeature;
use App\Models\Layer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Tambahkan Log

class MapFeatureController extends Controller
{
    /**
     * Menampilkan daftar semua fitur untuk peta tertentu.
     */
    public function index(Map $map)
    {
        // Load fitur-fitur yang berelasi dengan peta
        $features = $map->features()->paginate(10); // Gunakan pagination

        return view('map_features.index', compact('map', 'features'));
    }

    /**
     * Menampilkan form untuk mengedit fitur tertentu.
     */
    public function edit(MapFeature $mapFeature)
    {
        // Load relasi map untuk navigasi (misal: tombol kembali)
        $mapFeature->load('map');
        $feature = $mapFeature;
        return view('map_features.edit', compact('mapFeature'));
    }

    /**
     * Memperbarui fitur yang ada di database.
     */
    public function update(Request $request, MapFeature $mapFeature)
    {
        $request->validate([
            'properties' => 'nullable|string', // Validasi sebagai string JSON
            'geometry' => 'required|json',
            'feature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'caption' => 'nullable|string|max:255',
            'technical_info' => 'nullable|array', // ubah ke array
            'technical_info.*' => 'nullable|string|max:500', // setiap elemen array string
            'layer_ids' => 'required|array',
            'layer_ids.*' => 'exists:layers,id',
        ]);

        try {
            // Decode properties dengan fallback
            $decodedProperties = $mapFeature->properties;
            if ($request->filled('properties')) {
                $tmp = json_decode($request->properties, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $decodedProperties = $tmp;
                }
            }

            // Decode geometry dengan fallback
            $decodedGeometry = $mapFeature->geometry;
            if ($request->filled('geometry')) {
                $tmp = json_decode($request->geometry, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $decodedGeometry = $tmp;
                }
            }

            $data = [
                    'properties' => $request->properties ?? $mapFeature->properties,
                    'geometry' => $request->geometry ?? $mapFeature->geometry,
                    'caption' => $request->caption ?? $mapFeature->caption,
                ];

                // Handle technical_info (array ke JSON)
                $technicalInfo = $request->input('technical_info', []);
                $technicalInfo = array_filter($technicalInfo, fn($v) => $v !== null && $v !== '');
                $data['technical_info'] = !empty($technicalInfo)
                    ? json_encode($technicalInfo, JSON_UNESCAPED_UNICODE)
                    : $mapFeature->technical_info;

            // Handle upload gambar jika ada
            if ($request->hasFile('feature_image')) {
                // Hapus gambar lama jika ada
                if ($mapFeature->image_path && file_exists(public_path($mapFeature->image_path))) {
                    unlink(public_path($mapFeature->image_path));
                }

                $file = $request->file('feature_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('map_features');
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $data['image_path'] = 'map_features/' . $filename;
            }

            $mapFeature->update($data);
            $mapFeature->layers()->sync($request->layer_ids);

            return redirect()->route('map-features.index', $mapFeature->map_id)
                        ->with('success', 'Fitur berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Gagal memperbarui fitur: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui fitur.');
        }
    }

    public function create(Map $map)
    {
        $layers = Layer::all(); // Ambil semua layer
        return view('map_features.create', compact('map', 'layers'));
    }

    public function store(Request $request, Map $map)
    {
        $validated = $request->validate([
            'geometry' => 'required|json',
            'properties' => 'nullable|string',
            'feature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'caption' => 'nullable|string|max:255',
            'technical_info' => 'nullable|array',
            'technical_info.*' => 'nullable|string|max:500',
            'layer_ids' => 'required|array',
            'layer_ids.*' => 'exists:layers,id',
        ]);

        $data = [
            'properties' => $validated['properties'] ?? null,
            'geometry' => $validated['geometry'],
            'caption' => $validated['caption'] ?? null,
            'technical_info' => $request->technical_info ? json_encode($request->technical_info) : null,
        ];

        // Upload image kalau ada
        if ($request->hasFile('feature_image')) {
            $file = $request->file('feature_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('map_features');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $data['image_path'] = 'map_features/' . $filename;
        }

        $feature = $map->features()->create($data);

        // Simpan multilayer ke pivot
        $feature->layers()->sync($validated['layer_ids']);

        return redirect()->route('map-features.index', $map)
            ->with('success', 'Fitur berhasil ditambahkan.');
    }

}