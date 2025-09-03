<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\MapFeature;
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
                'properties'     => $decodedProperties,
                'geometry'       => $decodedGeometry,
                'caption'        => $request->filled('caption') ? $request->caption : $mapFeature->caption,
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
                $data['image_path'] = $filename;
            }

            $mapFeature->update($data);

            return redirect()->route('map-features.index', $mapFeature->map_id)
                        ->with('success', 'Fitur berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Gagal memperbarui fitur: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui fitur.');
        }
    }
}