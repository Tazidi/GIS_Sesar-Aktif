@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .maps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .map-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
        }

        .map-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .preview-map {
            position: relative;
            height: 200px;
            border-bottom: 1px solid #e5e7eb;
        }

        .leaflet-container {
            border-radius: 12px 12px 0 0;
        }

        .map-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            color: #6b7280;
            font-size: 14px;
            animation: pulse 1.5s infinite;
            z-index: 10;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .map-info {
            padding: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .map-name {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }

        .map-description {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 16px;
        }

        .btn-view {
            display: inline-block;
            text-align: center;
            padding: 8px 12px;
            background: #2563eb;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .btn-view:hover {
            background: #1d4ed8;
            text-decoration: none;
        }
    </style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8" style="max-width: 1200px;">
    <div class="layer-header mb-6">
        <a href="{{ route('gallery_maps.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Galeri</a>
        <h1 class="text-2xl font-bold mt-2">{{ $layer->nama_layer }}</h1>
        <p class="text-gray-600">{{ $layer->deskripsi }}</p>
        <div class="mt-2 text-sm text-gray-500">
            {{ $layer->maps->count() }} peta dalam layer ini
        </div>
    </div>

    <div class="maps-grid">
        @forelse($layer->maps as $map)
            <div class="map-card">
                <div id="map-{{ $map->id }}" class="preview-map">
                    <div class="map-loading">Memuat peta...</div>
                </div>
                <div class="map-info">
                    <div>
                        <h3 class="map-name">{{ $map->name }}</h3>
                        <p class="map-description">
                            {{ Str::limit($map->description ?? 'Tidak ada deskripsi', 100) }}
                        </p>
                    </div>
                    <a href="{{ route('gallery_maps.show', $map->id) }}" class="btn-view">
                        Lihat Detail Peta
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
                <p class="text-gray-500">Tidak ada peta dalam layer ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

@php
// Siapkan data maps di sisi PHP terlebih dahulu
$mapsData = $layer->maps->map(function($map) {
    $pivot = $map->pivot;
    return [
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
})->toArray();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapsData = @json($mapsData);

    const initPreviewMap = (mapData) => {
        const mapContainerId = `map-${mapData.id}`;
        const mapContainer = document.getElementById(mapContainerId);
        if (!mapContainer) return;
        
        // Hapus loading indicator
        const loadingIndicator = mapContainer.querySelector('.map-loading');
        if (loadingIndicator) {
            loadingIndicator.remove();
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
        
        // Render features
        const renderFeatures = async () => {
            try {
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
                        fallbackLayer = L.circle(latlng, { 
                            ...style, 
                            radius: mapData.radius || 1000 
                        });
                    } else if (mapData.layer_type === 'marker' && mapData.icon_url) {
                        const icon = L.icon({ 
                            iconUrl: mapData.icon_url, 
                            iconSize: [18, 18], 
                            iconAnchor: [9, 9] 
                        });
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
        mapsData.forEach(initPreviewMap);
    }
});
</script>
@endsection