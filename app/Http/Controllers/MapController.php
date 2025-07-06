<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use Illuminate\Support\Facades\File;

class MapController extends Controller
{
    public function index()
    {
        $maps = Map::all();
        return view('maps.index', compact('maps'));
    }

    public function create()
    {
        $map = new Map(); // Tambahkan ini untuk konsistensi
        return view('maps.create', compact('map'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string', // Tambahkan validasi untuk description
            'file' => 'required|mimes:json,csv,zip',
            'layer_type' => 'required|string', // Ubah dari nullable ke required
            'stroke_color' => 'nullable|string',
            'fill_color' => 'nullable|string',
            'opacity' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'radius' => 'nullable|numeric',
            'icon_url' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $destination = public_path('map_files');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $file->move($destination, $filename);

        $map = new Map();
        $map->title = $data['title'];
        $map->description = $data['description'] ?? null; // Perbaiki assignment
        $map->file_path = 'map_files/' . $filename;
        $map->layer_type = $data['layer_type'];
        $map->stroke_color = $data['stroke_color'] ?? null;
        $map->fill_color = $data['fill_color'] ?? null;
        $map->opacity = $data['opacity'] ?? null;
        $map->weight = $data['weight'] ?? null;
        $map->radius = $data['radius'] ?? null;
        $map->icon_url = $data['icon_url'] ?? null;
        $map->save();

        return redirect()->route('maps.index')->with('success', 'Peta berhasil disimpan!');
    }

    public function edit(Map $map)
    {
        return view('maps.edit', compact('map'));
    }

    public function update(Request $request, Map $map)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string',
            'file' => 'nullable|mimes:json,csv,zip',
            'layer_type' => 'required|string',
            'stroke_color' => 'nullable|string',
            'fill_color' => 'nullable|string',
            'opacity' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'radius' => 'nullable|numeric',
            'icon_url' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            // Hapus file lama
            $oldPath = public_path($map->file_path);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destination = public_path('map_files');

            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            $file->move($destination, $filename);
            $map->file_path = 'map_files/' . $filename;
        }

        $map->title = $data['title'];
        $map->description = $data['description'] ?? null;
        $map->layer_type = $data['layer_type'];
        $map->stroke_color = $data['stroke_color'] ?? null;
        $map->fill_color = $data['fill_color'] ?? null;
        $map->opacity = $data['opacity'] ?? null;
        $map->weight = $data['weight'] ?? null;
        $map->radius = $data['radius'] ?? null;
        $map->icon_url = $data['icon_url'] ?? null;
        $map->save();

        return redirect()->route('maps.index')->with('success', 'Peta berhasil diperbarui!');
    }

    public function show(Map $map)
    {
        return view('maps.show', compact('map'));
    }

    public function destroy(Map $map)
    {
        $path = public_path($map->file_path);
        if (file_exists($path)) {
            unlink($path);
        }

        $map->delete();
        return redirect()->route('maps.index')->with('success', 'Peta berhasil dihapus!');
    }

    public function geojson(Map $map)
    {
        $path = public_path($map->file_path);
        
        if (!file_exists($path)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        $content = file_get_contents($path);
        
        // Cek apakah file adalah JSON yang valid
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'File bukan JSON yang valid'], 400);
        }

        return response($content, 200)->header('Content-Type', 'application/json');
    }
}