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

    public function destroy(Map $map)
    {
        Storage::disk('public')->delete($map->file_path);
        $map->delete();
        return back();
    }
}
