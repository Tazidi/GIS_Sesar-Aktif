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
        ]);

        try {
            $data = [
                // Konversi string JSON dari form ke array sebelum disimpan
                'properties' => json_decode($request->properties, true),
                'geometry' => json_decode($request->geometry, true),
                'caption' => $request->caption,
            ];

            // Handle upload gambar jika ada
            if ($request->hasFile('feature_image')) {
                // Hapus gambar lama jika ada
                if ($mapFeature->image_path && file_exists(public_path($mapFeature->image_path))) {
                    unlink(public_path($mapFeature->image_path));
                }

                // Simpan gambar baru secara manual ke folder public/map_feature_images
                $file = $request->file('feature_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('map_feature_images');
                
                // Pastikan foldernya ada
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);

                // Simpan path relatif (untuk digunakan di view)
                $data['image_path'] = 'map_feature_images/' . $filename;
            }

            $mapFeature->update($data);

            return redirect()->route('map-features.index', $mapFeature->map_id)
                         ->with('success', 'Fitur berhasil diperbarui.');

        } catch (\Exception $e) {
            // Catat error untuk debugging
            Log::error('Gagal memperbarui fitur: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui fitur.');
        }
    }
}