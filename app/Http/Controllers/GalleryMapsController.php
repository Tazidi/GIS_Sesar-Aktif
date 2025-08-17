<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use App\Models\Project;

class GalleryMapsController extends Controller
{
    /**
     * Halaman Galeri Peta:
     * - Menampilkan daftar Map (eksisting, tidak diubah)
     * - Menampilkan daftar Proyek (view-only; jumlah lokasi diambil via withCount)
     */
    public function galeriPeta()
    {
        $maps = Map::with(['layer', 'features'])
            ->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
            ->get();

        $projects = Project::query()
            ->withCount('surveyLocations')
            ->with(['user']) // opsional, untuk menampilkan nama surveyor di kartu
            ->latest()
            ->get();

        return view('gallery_maps.index', compact('maps', 'projects'));
    }

    /**
     * Menampilkan detail satu Map (tetap seperti semula).
     */
    public function show($id)
    {
        $map = Map::with(['layer', 'features'])
            ->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
            ->findOrFail($id);

        // Tambahkan URL publik untuk setiap feature (tetap seperti pola sebelumnya)
        $map->features->transform(function ($feature) {
            $feature->feature_image_path = $feature->image_path
                ? asset($feature->image_path)
                : null;
            $feature->caption = $feature->caption ?? null;
            $feature->technical_info = $feature->technical_info ?? null;
            return $feature;
        });

        // Agar script show eksisting tetap bekerja (butuh collection $maps)
        $maps = collect([$map]);

        return view('gallery_maps.show', compact('map', 'maps'));
    }

    /**
     * Menampilkan detail satu Proyek (view-only) di halaman galeri.
     * Mengadopsi tampilan dari projects.show (tanpa tombol CRUD). :contentReference[oaicite:1]{index=1}
     */
    public function showProject(Project $project)
    {
        // Eager-load lokasi untuk peta
        $project->load('surveyLocations');

        // Kita gunakan view yang sama (gallery_maps.show) tetapi dengan variabel $project
        return view('gallery_maps.show', compact('project'));
    }

    public function showProjectLocation(Project $project, $locationId)
    {
        $location = $project->surveyLocations()->findOrFail($locationId);

        return view('gallery_maps.show_location', compact('project', 'location'));
    }

}
