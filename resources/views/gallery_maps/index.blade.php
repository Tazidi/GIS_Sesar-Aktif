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
        <div class="maps-preview">
            @forelse($maps as $map)
                <div class="map-preview-card">
                <div id="map-{{ $map->id }}" class="preview-map">
                    <div class="map-loading">Memuat peta...</div>
                </div>
                <h3 class="preview-title">{{ $map->name }}</h3>
                <p class="preview-description">{{ Str::limit($map->description ?? '—', 80) }}</p>
                <a href="{{ route('gallery_maps.show', $map->id) }}" class="btn-view">Lihat Peta</a>
                </div>
            @empty
                <div>Tidak ada peta</div>
            @endforelse
            </div>
    </div>

    {{-- ====== BAGIAN: Proyek Survey ====== --}}
    <div class="project-section">
        <h2 class="project-title">Proyek</h2>

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
                                <span>Lokasi proyek</span>
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
        $mapsData = collect($maps)->map(function($map) {
            return [
                'unique_id'   => "map-{$map->id}",
                'id'          => $map->id,
                'name'        => $map->name,
                'description' => $map->description,
                'image_path'  => $map->image_path ? asset($map->image_path) : '',
                'layer_type'  => $map->layer_type ?? 'marker',
                'stroke_color'=> $map->stroke_color ?? '#3388ff',
                'fill_color'  => $map->fill_color ?? '#3388ff',
                'opacity'     => $map->opacity ?? 0.8,
                'weight'      => $map->weight ?? 2,
                'radius'      => $map->radius ?? 300,
                'icon_url'    => $map->icon_url ?? '',
                'lat'         => $map->lat ?? null,
                'lng'         => $map->lng ?? null,
                'geometry'    => $map->geometry ? (is_string($map->geometry) ? json_decode($map->geometry, true) : $map->geometry) : null,
            ];
        })->toArray();
    @endphp

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapsData = @json($mapsData);

        // helper: parse technical_info yang mungkin string JSON atau object
        const parseTechnicalInfo = (val) => {
            if (!val) return {};
            if (typeof val === 'object') return val;
            try {
                return JSON.parse(val);
            } catch (e) {
                return {};
            }
        };

        const safeNumber = (v, fallback) => {
            const n = Number(v);
            return Number.isFinite(n) ? n : fallback;
        };

        const initPreviewMap = (mapData) => {
            const mapContainerId = mapData.unique_id;
            const mapContainer = document.getElementById(mapContainerId);
            if (!mapContainer || mapContainer._leaflet_id) {
                console.log('Map container not found or already initialized:', mapContainerId);
                return;
            }

            const loadingIndicator = mapContainer.querySelector('.map-loading');
            if (loadingIndicator) loadingIndicator.remove();
            if (mapContainer.offsetHeight === 0) mapContainer.style.height = '120px';

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

            const getStyleFrom = (props = {}, tech = {}) => {
                // kemungkinan field warna bisa bernama stroke_color, fill_color, color, atau color_hex
                const stroke_color = tech.stroke_color || tech.color || tech.color_hex || props.stroke_color || props.color || mapData.stroke_color || '#3388ff';
                const fill_color   = tech.fill_color   || tech.color || tech.color_hex || props.fill_color   || props.color || mapData.fill_color   || stroke_color;
                const weight       = safeNumber(tech.weight ?? props.weight ?? mapData.weight, 3);
                const opacity      = (typeof tech.opacity !== 'undefined' ? tech.opacity : (typeof props.opacity !== 'undefined' ? props.opacity : (mapData.opacity ?? 0.8)));
                const fillOpacity  = (typeof tech.fill_opacity !== 'undefined' ? tech.fill_opacity : (typeof props.fill_opacity !== 'undefined' ? props.fill_opacity : (mapData.fill_opacity ?? 0.3)));

                return {
                    color: stroke_color,
                    fillColor: fill_color,
                    weight,
                    opacity,
                    fillOpacity: fillOpacity,
                };
            };

            const renderFeatures = async () => {
                try {
                    const geojsonUrl = `{{ url('maps') }}/${mapData.id}/geojson`;
                    const response = await fetch(geojsonUrl);
                    if (!response.ok) throw new Error('GeoJSON not found');

                    const geojsonData = await response.json();

                    const geoLayer = L.geoJSON(geojsonData, {
                        style: (feature) => {
                            const props = feature.properties || {};
                            const tech = parseTechnicalInfo(props.technical_info);
                            return getStyleFrom(props, tech);
                        },
                        pointToLayer: (feature, latlng) => {
                            const props = feature.properties || {};
                            const tech = parseTechnicalInfo(props.technical_info);

                            // Tentukan jenis geometri yang diharapkan:
                            // prioritas: technical_info.geometry_type > props.geometry_type > props.layer_type > mapData.layer_type
                            const geomTypeRaw = (tech.geometry_type || props.geometry_type || props.layer_type || mapData.layer_type || (feature.geometry && feature.geometry.type === 'Point' ? 'marker' : '')).toString().toLowerCase();

                            // normalisasi beberapa variasi nama
                            const geomType = geomTypeRaw
                                .replace('_', '')
                                .replace('-', '')
                                .trim(); // contoh: "circle_marker" => "circlemarker"

                            const style = getStyleFrom(props, tech);
                            const iconUrl = tech.icon_url || props.icon_url || mapData.icon_url || '';

                            const radius = safeNumber(tech.radius ?? props.radius ?? mapData.radius, 300);
                            const pointRadius = safeNumber(tech.point_radius ?? props.point_radius ?? props.radius ?? mapData.point_radius ?? mapData.radius ?? 5, 5);

                            // Jika explicit 'circle' => gunakan L.circle (dengan radius meter)
                            if (geomType === 'circle') {
                                return L.circle(latlng, {
                                    ...style,
                                    radius: radius
                                });
                            }

                            // Jika explicit 'circlemarker' => gunakan L.circleMarker (radius pixel)
                            if (geomType === 'circlemarker' || geomType === 'circlemarker' || geomType === 'circlemark') {
                                return L.circleMarker(latlng, {
                                    ...style,
                                    radius: pointRadius
                                });
                            }

                            // Jika explicit 'marker' => gunakan marker (prefer custom icon bila tersedia)
                            if (geomType === 'marker' || geomType === 'point') {
                                if (iconUrl && iconUrl.toString().trim() !== '' && !iconUrl.includes('marker-survey.png')) {
                                    const icon = L.icon({
                                        iconUrl: iconUrl,
                                        iconSize: [18, 18],
                                        iconAnchor: [9, 9]
                                    });
                                    return L.marker(latlng, { icon });
                                }
                                // default marker (Leaflet)
                                return L.marker(latlng);
                            }

                            // Jika tidak jelas, fall back:
                            // Jika geometri asli adalah Point -> circleMarker kecil
                            if (feature.geometry && feature.geometry.type && feature.geometry.type.toLowerCase() === 'point') {
                                return L.circleMarker(latlng, {
                                    ...style,
                                    radius: pointRadius
                                });
                            }

                            // fallback safety
                            return L.marker(latlng);
                        }
                    }).addTo(previewMap);

                    // set view: coba fit bounds kalau valid, kalau tidak gunakan satu layer center/latlng
                    try {
                        const bounds = geoLayer.getBounds();
                        if (bounds && bounds.isValid && bounds.isValid()) {
                            previewMap.fitBounds(bounds, { padding: [10, 10], maxZoom: 12 });
                            setTimeout(() => previewMap.invalidateSize(), 120);
                            return;
                        }
                    } catch (e) {
                        // ignore
                    }

                    // jika tidak ada bounds valid (mis. satu titik), ambil layer pertama
                    const layers = geoLayer.getLayers ? geoLayer.getLayers() : [];
                    if (layers && layers.length === 1) {
                        const layer = layers[0];
                        if (layer.getLatLng) {
                            previewMap.setView(layer.getLatLng(), 12);
                        }
                    }

                } catch (error) {
                    console.error('Error loading GeoJSON for map', mapData.id, ':', error);
                    // fallback: gunakan lat/lng dari mapData
                    const lat = parseFloat(mapData.lat);
                    const lng = parseFloat(mapData.lng);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        const latlng = L.latLng(lat, lng);
                        const techFallback = {};
                        const style = getStyleFrom({}, techFallback);
                        let fallbackLayer;

                        if ((mapData.layer_type || '').toString().toLowerCase() === 'circle') {
                            fallbackLayer = L.circle(latlng, { ...style, radius: safeNumber(mapData.radius, 1000) });
                        } else if ((mapData.layer_type || '').toString().toLowerCase() === 'marker') {
                            if (mapData.icon_url) {
                                const icon = L.icon({ iconUrl: mapData.icon_url, iconSize: [18, 18], iconAnchor: [9, 9] });
                                fallbackLayer = L.marker(latlng, { icon });
                            } else {
                                fallbackLayer = L.marker(latlng);
                            }
                        } else {
                            fallbackLayer = L.circleMarker(latlng, { ...style, radius: 5 });
                        }

                        fallbackLayer.addTo(previewMap);
                        previewMap.setView(latlng, 10);
                    } else {
                        previewMap.setView([-2.54, 118.01], 5);
                    }
                }

                // pastikan ukuran ter-update
                setTimeout(() => previewMap.invalidateSize(), 150);
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