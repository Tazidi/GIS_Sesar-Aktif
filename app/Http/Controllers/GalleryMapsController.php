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
        $maps = \App\Models\Map::with(['layers', 'features.layers'])->get(); // Tambah .layers untuk pivot data

        // Transform seperti di show() untuk inject data lengkap ke features
        $maps->transform(function ($map) {
            $map->features->transform(function ($feature) {
                $feature->feature_image_path = $feature->image_path
                    ? asset($feature->image_path)
                    : null;
                $feature->caption = $feature->caption ?? null;
                $feature->technical_info = $feature->technical_info ?? null;
                // Inject layer_ids dari pivot
                $feature->layer_ids = $feature->layers->pluck('id')->toArray();
                return $feature;
            });
            return $map;
        });

        $projects = \App\Models\Project::with('surveyLocations')
            ->withCount('surveyLocations')
            ->where('show_in_gallery', 1) // Hanya proyek yang ditandai untuk ditampilkan di galeri
            ->get();

        return view('gallery_maps.index', compact('maps', 'projects'));
    }

    public function show($id)
    {
        $map = Map::with([
            'layers',
            'features.layers' // 👈 ambil juga layer dari setiap feature
        ])->findOrFail($id);

        $map->features->transform(function ($feature) {
            $feature->feature_image_path = $feature->image_path
                ? asset($feature->image_path)
                : null;
            $feature->caption = $feature->caption ?? null;
            $feature->technical_info = $feature->technical_info ?? null;
            // inject layer_ids array biar bisa dipakai di Blade/JS
            $feature->layer_ids = $feature->layers->pluck('id')->toArray();
            return $feature;
        });

        $maps = collect([$map]);

        return view('gallery_maps.show', compact('map', 'maps'));
    }

    public function showLayer(\App\Models\Layer $layer)
    {
        // Semua kategori ditampilkan
        $layer->load(['maps' => function($query) {
            $query->with('layers');
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

    public function geojson($id)
    {
        $map = \App\Models\Map::with(['features.layers'])->findOrFail($id);

        $features = $map->features->map(function ($feature) {
            $geometry = $feature->geometry;

            // pastikan geometry jadi array
            if (is_string($geometry)) {
                $geometry = json_decode($geometry, true);
            }

            // konversi kalau cuma lat/lng biasa
            if (isset($geometry['lat']) && isset($geometry['lng'])) {
                $geometry = [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $geometry['lng'],
                        (float) $geometry['lat'],
                    ],
                ];
            }

            return [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'id' => $feature->id,
                    'name' => $feature->name ?? null,
                    'caption' => $feature->caption ?? null,
                    'image' => $feature->image_path ? asset($feature->image_path) : null,
                    'technical_info' => $feature->technical_info,
                    'layer_ids' => $feature->layers->pluck('id')->toArray(),
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

}
