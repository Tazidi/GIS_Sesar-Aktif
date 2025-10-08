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
        // 1. Eager load relasi yang benar: Map -> layers -> mapFeatures
        $maps = \App\Models\Map::with('layers.mapFeatures')->get();

        // Project query sudah benar
        $projects = \App\Models\Project::with('surveyLocations')
            ->withCount('surveyLocations')
            ->where('show_in_gallery', 1)
            ->get();

        // Tidak perlu transform di sini karena view gallery_maps.index
        // hanya menampilkan preview dan tidak memerlukan detail feature.
        // Jika Anda memerlukannya, gunakan pola flatMap seperti di method geojson().

        return view('gallery_maps.index', compact('maps', 'projects'));
    }

    public function show($id)
    {
        // 1. Eager load relasi yang benar
        $map = Map::with('layers.mapFeatures')->findOrFail($id);

        // 2. Kumpulkan semua features dari semua layers menjadi satu collection
        $allFeatures = $map->layers->flatMap(function ($layer) {
            return $layer->mapFeatures;
        });

        // 3. Lakukan transform pada collection features yang sudah digabung
        $allFeatures->transform(function ($feature) {
            $feature->feature_image_path = $feature->image_path
                ? asset($feature->image_path)
                : null;
            $feature->caption = $feature->caption ?? null;
            $feature->technical_info = $feature->technical_info ?? null;
            // Feature sekarang hanya milik 1 layer, jadi kita pakai layer_id
            $feature->layer_ids = [$feature->layer_id];
            return $feature;
        });

        // 4. Tambahkan collection features yang sudah di-transform ke objek map
        //    agar bisa diakses di view dengan mudah (misal: $map->all_features)
        $map->all_features = $allFeatures;

        return view('gallery_maps.show', compact('map'));
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
        // 1. Load relasi yang benar
        $map = Map::with('layers.mapFeatures')->findOrFail($id);

        // 2. Gabungkan (flatMap) semua fitur dari semua layer terkait
        $allFeatures = $map->layers->flatMap(function ($layer) {
            return $layer->mapFeatures;
        });

        // 3. Proses collection gabungan tersebut
        $features = $allFeatures->map(function ($feature) {
            $geometry = is_string($feature->geometry) ? json_decode($feature->geometry, true) : $feature->geometry;
            
            // Pastikan format properties konsisten
            $properties = $feature->properties ?? [];
            if (is_string($properties)) $properties = json_decode($properties, true) ?: [];

            $properties['id'] = $feature->id;
            $properties['name'] = $feature->name ?? null;
            $properties['caption'] = $feature->caption ?? null;
            $properties['image'] = $feature->image_path ? asset($feature->image_path) : null;
            $properties['technical_info'] = $feature->technical_info;
            // Kirim ID layer asal dari feature ini
            $properties['layer_id'] = $feature->layer_id; 

            return [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => $properties,
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

}
