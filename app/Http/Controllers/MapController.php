<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use Illuminate\Support\Facades\Storage;


class MapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
        $path = $request->file('file')->store('maps', 'public');

        Map::create([
            'title' => $data['title'],
            'description' => $request->description,
            'file_path' => $path
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
        Storage::disk('public')->delete($map->file_path);
        $map->delete();
        return back();
    }

    public function geojson(Map $map)
    {
        $path = storage_path('app/public/' . $map->file_path);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }

        $content = file_get_contents($path);
        return response($content, 200)->header('Content-Type', 'application/json');
    }
}
