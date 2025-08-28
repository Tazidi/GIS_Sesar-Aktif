@extends('layouts.app')

@section('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        /* === VARIABEL WARNA UTAMA === */
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;  
            --secondary-color: #2563eb;
            --text-dark: #111827;
            --text-medium: #374151;
            --text-light: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --background-white: #ffffff;
            --background-light: #f9fafb;
            --success-bg: #dcfce7;
            --success-text: #166534;
        }

        /* === LAYER SECTION === */
        .layer-card {
            background: var(--background-white);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .layer-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .layer-description {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .layer-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px 0;
            border-top: 1px solid var(--border-color);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
        }

        .meta-count {
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        /* === PETA PREVIEW SECTION === */
        .maps-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .map-preview-card {
            background: var(--background-light);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            transition: all 0.2s ease;
        }

        .map-preview-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .preview-map {
            height: 120px;
            width: 100%;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .preview-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .preview-description {
            font-size: 12px;
            color: var(--text-light);
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        /* Button untuk layer */
        .btn-view {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: background-color 0.2s ease;
        }

        .btn-view:hover {
            background: var(--primary-hover);
            color: white;
            text-decoration: none;
        }

        /* === PROYEK SECTION === */
        .project-section { 
            max-width: 1000px; 
            margin: 40px auto 0; 
        }

        .project-title { 
            font-size:22px; 
            font-weight:800; 
            margin-bottom:16px; 
            text-align: center;
        }

        /* Tambahkan style untuk memastikan map container memiliki tinggi */
        .preview-map {
            height: 120px;
            width: 100%;
            border-radius: 6px;
            margin-bottom: 10px;
            min-height: 120px; /* Pastikan selalu ada tinggi minimum */
        }

        /* Style untuk loading state */
        .map-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: #6b7280;
            font-size: 12px;
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .maps-preview {
                grid-template-columns: 1fr;
            }
            
            .layer-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
@endsection

@section('content')
    {{-- Header Sederhana --}}
    <div class="text-center mb-8 pt-8">
        <h1 style="font-size: 28px; font-weight: 700; color: #333; margin-bottom: 8px;">
            Galeri Peta
        </h1>
        <p style="color: #666; font-size: 16px;">
            Koleksi peta dan visualisasi data geografis Sesar Jawa Bagian Barat
        </p>
    </div>

    {{-- Container untuk Layer --}}
    <div class="container" style="max-width: 1000px; margin: 0 auto;">
        @forelse ($layers as $layer)
            <div class="layer-card">
                <h2 class="layer-title">{{ $layer->nama_layer }}</h2>
                
                <p class="layer-description">
                    {{ $layer->deskripsi ?? 'Layer ini berisi kumpulan peta geografis.' }}
                </p>
                
                <div class="layer-meta">
                    <div class="meta-item">
                        <span class="meta-count">{{ $layer->maps_count }}</span>
                        <span>Peta dalam layer</span>
                    </div>
                </div>
                
                {{-- Preview peta dalam layer --}}
                @if($layer->maps->count() > 0)
                    <div class="maps-preview">
                        @foreach($layer->maps as $map)
                            <div class="map-preview-card">
                                {{-- PERBAIKAN 1: Buat ID unik dengan menggabungkan ID layer dan ID peta --}}
                                <div id="map-{{ $layer->id }}-{{ $map->id }}" class="preview-map">
                                    <div class="map-loading">Memuat peta...</div>
                                </div>
                                <h3 class="preview-title">{{ $map->name }}</h3>
                                <p class="preview-description">
                                    {{ Str::limit($map->description ?? 'Peta ini menyajikan informasi geografis penting.', 80) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <a href="{{ route('gallery_maps.showLayer', $layer) }}" class="btn-view">
                    Lihat Semua Peta dalam Layer
                </a>
            </div>
        @empty
            <div class="empty-state">
                <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Belum ada layer</h3>
                <p>Saat ini tidak ada data layer yang dapat ditampilkan.</p>
            </div>
        @endforelse
    </div>

    {{-- ====== BAGIAN: Proyek Survey ====== --}}
    <div class="project-section">
        <h2 class="project-title">Proyek Survey</h2>

        @forelse($projects as $project)
            <div class="layer-card">
                <div class="map-content">
                    {{-- Bagian Peta (kiri) --}}
                    <div>
                        <div id="proj-map-{{ $project->id }}" class="preview-map">
                            <div class="map-loading">Memuat peta...</div>
                        </div>
                    </div>

                    {{-- Bagian Informasi (kanan) --}}
                    <div>
                        <h2 class="layer-title">{{ $project->name }}</h2>

                        <p class="layer-description">
                            {{ $project->description ?? 'Belum ada deskripsi proyek.' }}
                        </p>

                        <div class="layer-meta">
                            <div class="meta-item">
                                <span class="meta-count">{{ $project->survey_locations_count }}</span>
                                <span>Lokasi survey</span>
                            </div>
                        </div>

                        <a href="{{ route('gallery_maps.projects.show', $project) }}" class="btn-view">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>

            {{-- Data lokasi untuk tiap proyek --}}
            @php
                $locationsForMap = $project->surveyLocations->map(function($loc) {
                    $g = $loc->geometry;
                    // Normalisasi geometry ke array [lat,lng]
                    if (is_string($g)) {
                        $g = json_decode($g, true);
                    } elseif (is_object($g)) {
                        $g = (array)$g;
                    }
                    $lat = $g['lat'] ?? ($g['latitude'] ?? 0);
                    $lng = $g['lng'] ?? ($g['longitude'] ?? 0);
                    return [
                        'lat' => $lat,
                        'lng' => $lng,
                        'nama' => $loc->nama,
                        'image' => $loc->primary_image ? asset('survey/' . $loc->primary_image) : null
                    ];
                });
            @endphp

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const locations = @json($locationsForMap);
                    const mapId = "proj-map-{{ $project->id }}";
                    const mapContainer = document.getElementById(mapId);
                    if (!mapContainer || mapContainer._leaflet_id) return; // Cek jika sudah diinisialisasi

                    // Hapus loading indicator
                    const loadingIndicator = mapContainer.querySelector('.map-loading');
                    if (loadingIndicator) {
                        loadingIndicator.remove();
                    }

                    const map = L.map(mapId, {
                        zoomControl: false,
                        scrollWheelZoom: false,
                        dragging: false,
                    }).setView([-2.5, 117], 5);

                    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                        attribution: '© Google',
                        maxZoom: 20,
                    }).addTo(map);

                    const markers = [];
                    locations.forEach(loc => {
                        if (!loc.lat || !loc.lng) return;
                        const popupContent = `<b>${loc.nama}</b>` + 
                            (loc.image ? `<br><img src="${loc.image}" style="width:100px;margin-top:5px;border-radius:4px;">` : '');
                        const marker = L.marker([loc.lat, loc.lng]).addTo(map);
                        marker.bindPopup(popupContent);
                        markers.push(marker);
                    });

                    if (markers.length > 0) {
                        const group = L.featureGroup(markers);
                        map.fitBounds(group.getBounds().pad(0.2));
                    }

                    setTimeout(() => map.invalidateSize(), 100);
                });
            </script>

        @empty
            <div class="empty-state">Belum ada proyek untuk ditampilkan.</div>
        @endforelse
    </div>
@endsection

@section('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- Siapkan data di sisi PHP --}}
    @php
        $mapsData = $layers->flatMap(function($layer) {
            // PERBAIKAN 2: Gunakan `use ($layer)` untuk membawa variabel layer ke dalam closure map
            return $layer->maps->map(function($map) use ($layer) {
                $pivot = $map->pivot;
                return [
                    // Buat ID unik untuk digunakan di JavaScript
                    'unique_id' => "map-{$layer->id}-{$map->id}",
                    'id' => $map->id,
                    'name' => $map->name,
                    'description' => $map->description,
                    'image_path' => $map->image_path ? asset($map->image_path) : '',
                    'layer_type' => $pivot->layer_type ?? $map->layer_type ?? 'marker',
                    'stroke_color' => $pivot->stroke_color ?? $map->stroke_color ?? '#3388ff',
                    'fill_color' => $pivot->fill_color ?? $map->fill_color ?? '#3388ff',
                    'opacity' => $pivot->opacity ?? $map->opacity ?? 0.8,
                    'weight' => $pivot->weight ?? $map->weight ?? 2,
                    'radius' => $pivot->radius ?? $map->radius ?? 300,
                    'icon_url' => $pivot->icon_url ?? $map->icon_url ?? '',
                    'lat' => $pivot->lat ?? $map->lat ?? 0,
                    'lng' => $pivot->lng ?? $map->lng ?? 0,
                    'geometry' => $map->geometry ? (is_string($map->geometry) ? json_decode($map->geometry, true) : $map->geometry) : null,
                ];
            });
        })->values()->toArray();
    @endphp

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapsData = @json($mapsData);
        
        const initPreviewMap = (mapData) => {
            // PERBAIKAN 3: Gunakan `unique_id` yang baru dibuat untuk menargetkan elemen
            const mapContainerId = mapData.unique_id;        
            const mapContainer = document.getElementById(mapContainerId);
            
            // Tambahkan pengecekan untuk memastikan kontainer ada dan belum diinisialisasi
            if (!mapContainer || mapContainer._leaflet_id) {
                console.log('Map container not found or already initialized:', mapContainerId);
                return;
            }
            
            // Hapus loading indicator
            const loadingIndicator = mapContainer.querySelector('.map-loading');
            if (loadingIndicator) {
                loadingIndicator.remove();
            }
            
            // Pastikan container memiliki tinggi yang sesuai
            if (mapContainer.offsetHeight === 0) {
                mapContainer.style.height = '120px';
            }
            
            const previewMap = L.map(mapContainerId, {
                zoomControl: false,
                scrollWheelZoom: false,
                dragging: false,
                doubleClickZoom: false,
                touchZoom: false,
                boxZoom: false,
                keyboard: false,
            }).setView([-2.54, 118.01], 5);
            
            L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                attribution: '© Google',
                maxZoom: 20,
            }).addTo(previewMap);
            
            const createStyle = (props = {}) => ({
                color: props.stroke_color || mapData.stroke_color || '#3388ff',
                fillColor: props.fill_color || mapData.fill_color || '#3388ff',
                weight: props.weight || mapData.weight || 3,
                opacity: props.opacity || mapData.opacity || 0.8,
                fillOpacity: (props.fill_opacity || mapData.fill_opacity || 0.3) * 0.8,
            });
            
            const renderFeatures = async () => {
                try {
                    // ID peta asli tetap digunakan untuk mengambil data GeoJSON
                    const geojsonUrl = `{{ url('maps') }}/${mapData.id}/geojson`;
                    const response = await fetch(geojsonUrl);
                    if (!response.ok) throw new Error('GeoJSON not found');
                    
                    const geojsonData = await response.json();
                    
                    const geoLayer = L.geoJSON(geojsonData, {
                        style: (feature) => {
                            const props = feature.properties || {};
                            return {
                                color: props.stroke_color || mapData.stroke_color || '#3388ff',
                                fillColor: props.fill_color || mapData.fill_color || '#3388ff',
                                weight: props.weight || mapData.weight || 3,
                                opacity: props.opacity || mapData.opacity || 0.8,
                                fillOpacity: (props.fill_opacity || mapData.fill_opacity || 0.3) * 0.8,
                            };
                        },
                        pointToLayer: (feature, latlng) => {
                            const props = feature.properties || {};
                            const type = props.layer_type || mapData.layer_type;
                            const style = createStyle(props);
                            const iconUrl = props.icon_url || mapData.icon_url;
                            
                            if (type === 'circle') {
                                return L.circle(latlng, { 
                                    ...style, 
                                    radius: props.radius || mapData.radius || 1000 
                                });
                            }
                            
                            if (type === 'marker' && iconUrl && iconUrl.trim() !== '' && !iconUrl.includes('marker-survey.png')) {
                                const icon = L.icon({ 
                                    iconUrl: iconUrl, 
                                    iconSize: [18, 18], 
                                    iconAnchor: [9, 9] 
                                });
                                return L.marker(latlng, { icon });
                            }
                            
                            return L.circleMarker(latlng, { ...style, radius: 5 });
                        }
                    }).addTo(previewMap);
                    
                    if (geoLayer.getBounds().isValid()) {
                        previewMap.fitBounds(geoLayer.getBounds(), { 
                            padding: [10, 10], 
                            maxZoom: 12 
                        });
                    }
                    
                } catch (error) {
                    console.error('Error loading GeoJSON for map', mapData.id, ':', error);
                    const lat = parseFloat(mapData.lat);
                    const lng = parseFloat(mapData.lng);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const latlng = L.latLng(lat, lng);
                        const style = createStyle();
                        let fallbackLayer;
                        
                        if (mapData.layer_type === 'circle') {
                            fallbackLayer = L.circle(latlng, { ...style, radius: mapData.radius || 1000 });
                        } else if (mapData.layer_type === 'marker' && mapData.icon_url) {
                            const icon = L.icon({ iconUrl: mapData.icon_url, iconSize: [18, 18], iconAnchor: [9, 9] });
                            fallbackLayer = L.marker(latlng, { icon });
                        } else {
                            fallbackLayer = L.circleMarker(latlng, { ...style, radius: 5 });
                        }

                        fallbackLayer.addTo(previewMap);
                        previewMap.setView(latlng, 10);
                    } else {
                        previewMap.setView([-2.54, 118.01], 5);
                    }
                }
                
                setTimeout(() => previewMap.invalidateSize(), 100);
            };
            
            renderFeatures();
        };
        
        if (mapsData && mapsData.length > 0) {
            console.log('Initializing', mapsData.length, 'maps');
            mapsData.forEach(initPreviewMap);
        } else {
            console.log('No maps data found');
        }
    });
    </script>
@endsection