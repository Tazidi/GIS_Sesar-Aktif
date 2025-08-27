<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use App\Models\Project;

class GalleryMapsController extends Controller
{
    
    public function galeriPeta()
{
    $layers = \App\Models\Layer::with('maps')->withCount('maps')->get(); 
    
    $projects = \App\Models\Project::query()
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
    $layer->load('maps.layers');
    
    return view('gallery_maps.show', compact('layer'));
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
