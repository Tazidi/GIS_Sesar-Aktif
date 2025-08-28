<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use App\Models\Project;
use App\Models\Layer;

class GalleryMapsController extends Controller
{
    
    public function galeriPeta()
    {
        // Ambil semua layer dengan peta sesuai kategori
        $layers = Layer::withCount(['maps' => function($query) {
                $query->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta']);
            }])
            ->with(['maps' => function($query) {
                $query->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
                    ->with('layers'); // load style dari relasi
            }])
            ->whereHas('maps', function($query) {
                $query->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta']);
            })
            ->get();

        $projects = Project::with('surveyLocations')
            ->withCount('surveyLocations')
            ->get();

        return view('gallery_maps.index', compact('layers', 'projects'));
    }

    public function show($id)
    {
        $map = Map::with(['layers', 'features'])
            ->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
            ->findOrFail($id);

        $map->features->transform(function ($feature) {
            $feature->feature_image_path = $feature->image_path
                ? asset($feature->image_path)
                : null;
            $feature->caption = $feature->caption ?? null;
            $feature->technical_info = $feature->technical_info ?? null;
            return $feature;
        });

        $maps = collect([$map]);

        return view('gallery_maps.show', compact('map', 'maps'));
    }

    public function showLayer(\App\Models\Layer $layer)
    {
        // Filter maps berdasarkan kategori
        $layer->load(['maps' => function($query) {
            $query->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
                ->with('layers');
        }]);
        
        return view('gallery_maps.show_layer', compact('layer'));
    }

    public function showProject(Project $project)
    {
        $project->load('surveyLocations.user'); 
        return view('gallery_maps.show', compact('project'));
    }

    public function showProjectLocation(Project $project, $locationId)
    {
        $location = $project->surveyLocations()->findOrFail($locationId);

        return view('gallery_maps.show_location', compact('project', 'location'));
    }

}
