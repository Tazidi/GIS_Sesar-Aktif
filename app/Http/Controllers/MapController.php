<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use App\Models\Layer;

class MapController extends Controller
{
    public function index()
    {
        $maps = Map::with('layer')->get();
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
            'layer_type' => 'required|string|max:50',
            'stroke_color' => 'nullable|string|max:10',
            'fill_color' => 'nullable|string|max:10',
            'opacity' => 'nullable|numeric|between:0,1',
            'weight' => 'nullable|integer|min:0',
            'radius' => 'nullable|numeric|min:0',
            'file' => 'nullable|file|mimetypes:application/json,text/plain,text/json,text/geojson,text/csv,application/octet-stream|max:4096',
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

        Map::create($data);

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
            'layer_type' => 'required|string|max:50',
            'stroke_color' => 'nullable|string|max:10',
            'fill_color' => 'nullable|string|max:10',
            'opacity' => 'nullable|numeric|between:0,1',
            'weight' => 'nullable|integer|min:0',
            'radius' => 'nullable|numeric|min:0',
            'file' => 'nullable|file|mimes:json,csv,zip,geojson|max:4096',
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
        $path = public_path($map->file_path);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        $content = file_get_contents($path);

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'File bukan JSON yang valid'], 400);
        }

        return response($content, 200)->header('Content-Type', 'application/json');
    }
}