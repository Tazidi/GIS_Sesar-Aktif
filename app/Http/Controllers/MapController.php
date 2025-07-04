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
        return view('maps.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'file' => 'required|mimes:json,csv,zip'
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $destination = public_path('map_files');

        // Pastikan folder public/maps ada
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $file->move($destination, $filename);

        Map::create([
            'title' => $data['title'],
            'description' => $request->description,
            'file_path' => 'map_files/' . $filename
        ]);

        return redirect()->route('maps.index');
    }

    public function edit(Map $map)
    {
        return view('maps.edit', compact('map'));
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
        return back();
    }

    public function geojson(Map $map)
    {
        $path = public_path($map->file_path);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }

        $content = file_get_contents($path);
        return response($content, 200)->header('Content-Type', 'application/json');
    }
}
