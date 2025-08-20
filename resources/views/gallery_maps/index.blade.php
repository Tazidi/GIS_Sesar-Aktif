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

        /* === PETA SECTION === */
        .preview-map {
            height: 200px;
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }

        .map-card {
            background: var(--background-white);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .map-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .map-description {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
            max-height: 3.9em;
            overflow: hidden;
            position: relative;
        }

        .map-description::after {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40%;
            height: 1.5em;
            background: linear-gradient(to right, transparent, var(--background-white));
        }

        .map-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px 0;
            border-top: 1px solid var(--border-color);
        }

        .detail-item {
            font-size: 13px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-medium);
            margin-bottom: 4px;
        }

        .detail-value {
            color: var(--text-dark);
        }

        /* Button untuk map - sesuaikan dengan primary color */
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

        .asset-images {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .asset-images img {
            width: 24px;
            height: 24px;
            object-fit: contain;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        @media (min-width: 768px) {
            .map-content {
                display: grid;
                grid-template-columns: 300px 1fr;
                gap: 20px;
                align-items: start;
            }
        }

        /* === PROYEK SECTION === */
        .project-section { 
            max-width: 1000px; 
            margin: 40px auto 0; 
        }

        .project-card {
            background: var(--background-white);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .project-title { 
            font-weight: 700; 
            color: var(--text-dark);
            font-size: 18px;
            margin-bottom: 6px;
        }

        .project-desc { 
            color: var(--text-light); 
            font-size: 14px; 
            margin-top: 6px;
            line-height: 1.5;
        }

        .badge { 
            display: inline-block; 
            background: var(--success-bg); 
            color: var(--success-text); 
            font-size: 12px; 
            padding: 4px 8px; 
            border-radius: 12px;
            font-weight: 500;
        }

        .project-meta { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 12px; 
            margin-top: 12px; 
            font-size: 13px; 
            color: var(--text-medium);
            align-items: center;
        }

        .proj-link { 
            margin-left: auto; 
        }

        /* Button untuk project - sama dengan map button */
        .proj-view { 
            background: var(--primary-color); 
            color: #fff; 
            padding: 8px 16px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        .proj-view:hover { 
            background: var(--primary-hover);
            color: #fff;
            text-decoration: none;
        }

        /* === SHARED COMPONENTS === */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--background-white);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-light);
        }

        .empty-state h3 {
            font-size: 18px; 
            font-weight: 600; 
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .project-card > div {
                flex-direction: column !important;
                gap: 16px !important;
            }
            
            .proj-link {
                margin-left: 0 !important;
                align-self: flex-start;
            }
            
            .map-card {
                padding: 16px;
            }
            
            .map-details {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .mini-map {
                height: 200px;
                border-radius: 10px;
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

    {{-- Container untuk Peta --}}
    <div class="container" style="max-width: 1000px; margin: 0 auto;">
        @forelse ($maps as $map)
            <div class="map-card">
                <div class="map-content">
                    {{-- Bagian Peta --}}
                    <div>
                        <div id="map-{{ $map->id }}" class="preview-map"></div>
                    </div>
                    
                    {{-- Bagian Informasi --}}
                    <div>
                        <h2 class="map-title">{{ $map->name }}</h2>
                        
                        <p class="map-description">
                            {{ $map->description ?? 'Peta ini menyajikan informasi geografis penting.' }}
                        </p>
                        
                        <div class="map-details">
                            <div class="detail-item">
                                <div class="detail-label">Jenis</div>
                                <div class="detail-value">{{ ucfirst($map->layer_type) }}</div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Koordinat</div>
                                <div class="detail-value">
                                    {{ ($map->lat && $map->lng) ? number_format($map->lat, 4).', '.number_format($map->lng, 4) : 'N/A' }}
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Aset</div>
                                <div class="detail-value">
                                    <div class="asset-images">
                                        @if ($map->icon_url)
                                            <img src="{{ asset($map->icon_url) }}" alt="Ikon">
                                        @endif
                                        @if ($map->image_path)
                                            <img src="{{ asset($map->image_path) }}" alt="Gambar">
                                        @endif
                                        @if (!$map->icon_url && !$map->image_path)
                                            <span style="color: #999; font-size: 12px;">Tidak ada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <a href="{{ route('gallery.show', $map->id) }}" class="btn-view">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Belum ada peta</h3>
                <p>Saat ini tidak ada data peta yang dapat ditampilkan.</p>
            </div>
        @endforelse
    </div>
    {{-- ====== BAGIAN: Proyek Survey ====== --}}
    <div class="project-section">
        <h2 class="text-center" style="font-size:22px; font-weight:800; margin-bottom:16px;">Proyek Survey</h2>

        @forelse($projects as $project)
            <div class="map-card">
                <div class="map-content">
                    {{-- Bagian Peta (kiri) --}}
                    <div>
                        <div id="proj-map-{{ $project->id }}" class="preview-map"></div>
                    </div>

                    {{-- Bagian Informasi (kanan) --}}
                    <div>
                        <h2 class="map-title">{{ $project->name }}</h2>

                        <p class="map-description">
                            {{ $project->description ?? 'Belum ada deskripsi proyek.' }}
                        </p>

                        <div class="map-details">
                            <div class="detail-item">
                                <div class="detail-label">Jumlah Lokasi</div>
                                <div class="detail-value">{{ $project->survey_locations_count }} lokasi</div>
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
                    return [
                        'lat' => $loc->geometry['lat'] ?? 0,
                        'lng' => $loc->geometry['lng'] ?? 0,
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
                    if (!mapContainer) return;

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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapsData = @json($maps);
        
        const initPreviewMap = (mapData) => {
            const mapContainerId = `map-${mapData.id}`;        
            const mapContainer = document.getElementById(mapContainerId);
            if (!mapContainer) return;
            
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
                color: props.stroke_color || mapData.stroke_color || '#e74c3c',
                fillColor: props.fill_color || mapData.fill_color || '#e74c3c',
                weight: props.weight || mapData.weight || 3,
                opacity: props.opacity || mapData.opacity || 1.0,
                fillOpacity: (props.fill_opacity || mapData.fill_opacity || 0.3) * 0.8,
            });
            
            const renderFeatures = async () => {
                try {
                    const geojsonUrl = `{{ url('maps') }}/${mapData.id}/geojson`;
                    const response = await fetch(geojsonUrl);
                    if (!response.ok) throw new Error('GeoJSON not found');
                    
                    const geojsonData = await response.json();
                    
                    const geoLayer = L.geoJSON(geojsonData, {
                        style: (feature) => createStyle(feature.properties),
                        pointToLayer: (feature, latlng) => {
                            const props = feature.properties || {};
                            const type = props.layer_type || mapData.layer_type;
                            const style = createStyle(props);
                            const iconUrl = props.icon_url || mapData.icon_url;
                            
                            const imagePath = props.image_path ? `{{ asset('') }}${props.image_path}` : '';
                            
                            if (type === 'circle') {
                                return L.circle(latlng, { 
                                    ...style, 
                                    radius: props.radius || mapData.radius || 1000 
                                });
                            }
                            
                            if (type === 'marker' && iconUrl && iconUrl.trim() !== '' && !iconUrl.includes('marker-survey.png')) {
                                const icon = L.icon({ 
                                    iconUrl: iconUrl, 
                                    iconSize: [24, 24], 
                                    iconAnchor: [12, 12] 
                                });
                                return L.marker(latlng, { icon });
                            }
                            
                            return L.circleMarker(latlng, { ...style, radius: 6 });
                        }
                    }).addTo(previewMap);
                    
                    if (geoLayer.getBounds().isValid()) {
                        previewMap.fitBounds(geoLayer.getBounds(), { 
                            padding: [10, 10], 
                            maxZoom: 12 
                        });
                    }
                    
                } catch (error) {
                    const lat = parseFloat(mapData.lat);
                    const lng = parseFloat(mapData.lng);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const latlng = L.latLng(lat, lng);
                        const style = createStyle();
                        let fallbackLayer;
                        
                        if (mapData.layer_type === 'circle') {
                            fallbackLayer = L.circle(latlng, { 
                                ...style, 
                                radius: mapData.radius || 1000 
                            });
                        } else if (mapData.layer_type === 'marker' && mapData.icon_url) {
                            const icon = L.icon({ 
                                iconUrl: mapData.icon_url, 
                                iconSize: [24, 24], 
                                iconAnchor: [12, 12] 
                            });
                            fallbackLayer = L.marker(latlng, { icon });
                        } else {
                            fallbackLayer = L.circleMarker(latlng, { ...style, radius: 6 });
                        }

                        fallbackLayer.addTo(previewMap);
                        previewMap.setView(latlng, 10);
                    }
                }
                
                setTimeout(() => previewMap.invalidateSize(), 100);
            };
            
            renderFeatures();
        };
        
        if (mapsData && mapsData.length > 0) {
            mapsData.forEach(initPreviewMap);
        }
    });
    </script>
@endsection