<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use App\Models\SurveyLocation;

class GalleryMapsController extends Controller
{
    public function galeriPeta()
    {
        // Ambil maps dari database
        $maps = Map::with(['layer', 'features'])
            ->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
            ->get();
        
        // Tambahkan "Lokasi Survey" sebagai map khusus
        $maps->push((object)[
            'id' => 'lokasi-survey', // ID khusus
            'name' => 'Lokasi Survey',
            'description' => 'Semua titik hasil input dari Survey Locations.',
            'layer_type' => 'marker',
            'lat' => null,
            'lng' => null,
            'icon_url' => null,
            'image_path' => null,
            'kategori' => 'Galeri Peta',
            'stroke_color' => '#3388ff',
            'fill_color' => '#3388ff',
            'opacity' => 0.8,
            'weight' => 2,
            'radius' => 300
        ]);

        return view('gallery_maps.index', compact('maps'));
    }

    /**
     * Menampilkan detail peta tunggal.
     */
    public function show($id)
    {
        if ($id === 'lokasi-survey') {
            // Ambil semua SurveyLocation
            $surveyLocations = SurveyLocation::with('user')->get()->map(function ($loc) {
                return (object)[
                    'id' => $loc->id,
                    'nama' => $loc->nama,
                    'deskripsi' => $loc->deskripsi ?? '',
                    'lat' => str_replace(',', '.', $loc->geometry['lat'] ?? 0),
                    'lng' => str_replace(',', '.', $loc->geometry['lng'] ?? 0),
                    'image' => $loc->image ? asset('survey/' . $loc->image) : null,
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            (float) str_replace(',', '.', $loc->geometry['lng'] ?? 0),
                            (float) str_replace(',', '.', $loc->geometry['lat'] ?? 0),
                        ],
                    ],
                    'properties' => [
                        'name' => $loc->nama,
                        'description' => $loc->deskripsi ?? '',
                    ],
                    'image_path' => $loc->image ? 'survey/' . $loc->image : null,
                    'caption' => '',
                    'technical_info' => '',
                ];
            }); // Titik koma seharusnya ada di sini, untuk mengakhiri pemanggilan metode 'map'.

            // Kemudian, baru mendefinisikan objek $map
            $map = (object)[
                'id' => 'lokasi-survey',
                'name' => 'Lokasi Survey',
                'description' => 'Semua titik hasil input dari Survey Locations.',
                'layer_type' => 'marker',
                'features' => $surveyLocations,
                'image_path' => null,
                'icon_url' => null,
                'stroke_color' => '#3388ff',
                'fill_color' => '#3388ff',
                'opacity' => 0.8,
                'weight' => 2,
                'radius' => 300,
                'geometry' => null,
                'layer' => (object)['nama_layer' => 'Layer Tanpa Nama'],
            ];

            $maps = collect([$map]);
            return view('gallery_maps.show', compact('map', 'maps'));
        }

        // Map biasa dari database
        $map = Map::with(['layer', 'features'])
            ->whereIn('kategori', ['Galeri Peta', 'Peta SISIRAJA & Galeri Peta'])
            ->findOrFail($id);

        // Tambahkan URL publik untuk setiap feature
        $map->features->transform(function ($feature) {
            $feature->feature_image_path = $feature->image_path 
                ? asset($feature->image_path) 
                : null;
            $feature->caption = $feature->caption ?? null;
            $feature->technical_info = $feature->technical_info ?? null;
            return $feature;
        });

        // Bungkus jadi collection agar struktur sama seperti index
        $maps = collect([$map]);

        return view('gallery_maps.show', compact('map', 'maps'));
    }

    public function lokasiSurveyGeojson()
    {
        // Ambil semua titik dari SurveyLocation dan ubah menjadi FeatureCollection
        $features = SurveyLocation::query()
            ->get()
            ->map(function ($loc) {
                // Pastikan format lat/lng benar
                $lat = (float) str_replace(',', '.', data_get($loc->geometry, 'lat', 0));
                $lng = (float) str_replace(',', '.', data_get($loc->geometry, 'lng', 0));

                // Skip jika koordinat tidak valid
                if (!is_finite($lat) || !is_finite($lng)) {
                    return null;
                }

                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lng, $lat], // GeoJSON format: [lng, lat]
                    ],
                    'properties' => [
                        'name' => $loc->nama,
                        'description' => $loc->deskripsi ?? '',
                        'layer_type' => 'marker',
                        'feature_image_path' => $loc->image ? asset('survey/' . $loc->image) : null,
                        'caption' => null,
                        'technical_info' => null,
                    ],
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
    public function semuaMarkerGeojson() {
        $locations = SurveyLocation::all();
        $features = [];

        foreach ($locations as $loc) {
            $lat = (float) str_replace(',', '.', data_get($loc->geometry, 'lat', 0));
            $lng = (float) str_replace(',', '.', data_get($loc->geometry, 'lng', 0));

            // Abaikan jika koordinat tidak valid
            if (!is_finite($lat) || !is_finite($lng) || ($lat == 0 && $lng == 0)) {
                continue;
            }

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$lng, $lat],
                ],
                'properties' => [
                    'name' => $loc->nama,
                    'description' => $loc->deskripsi ?? '',
                    'layer_type' => 'marker',
                    'icon_url' => asset('images/marker-survey.png'), // Asumsi ada ikon default
                ],
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

}
