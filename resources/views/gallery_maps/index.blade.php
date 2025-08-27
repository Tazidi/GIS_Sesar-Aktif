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
            --text-dark: #111827;
            --text-medium: #374151;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --background-white: #ffffff;
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
        
        .map-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px 0;
            border-top: 1px solid var(--border-color);
        }

        .detail-item { font-size: 13px; }
        .detail-label { font-weight: 600; color: var(--text-medium); margin-bottom: 4px; }
        .detail-value { color: var(--text-dark); }

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
        @forelse ($layers as $layer)
    @php
        $layerType = 'Layer'; 
        $firstMap = $layer->maps->first();
        $lat = $firstMap->pivot->lat ?? null;
        $lng = $firstMap->pivot->lng ?? null;
        $iconUrl = $firstMap->pivot->icon_url ?? null;
    @endphp
    <div class="map-card">
        <div class="map-content">
            {{-- Bagian Peta --}}
            <div>
                {{-- ID peta harus unik untuk setiap layer --}}
                <div id="map-{{ $layer->id }}" class="preview-map"></div>
            </div>
            
            {{-- Bagian Informasi --}}
            <div>
                {{-- Gunakan variabel $layer --}}
                <h2 class="map-title">{{ $layer->nama_layer }}</h2>
                
                <p class="map-description">
                    {{ $layer->deskripsi ?? 'Peta ini menyajikan informasi geografis penting.' }}
                </p>
                
                <div class="map-details">
                    <div class="detail-item">
                        <div class="detail-label">Tipe</div>
                        <div class="detail-value">Kumpulan Peta</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Jumlah Peta</div>
                        {{-- Gunakan properti maps_count yang kita buat di controller --}}
                        <div class="detail-value">{{ $layer->maps_count }} titik</div>
                    </div>
                </div>
                
                {{-- Arahkan ke route yang benar dengan parameter $layer --}}
                <a href="{{ route('gallery.layer.show', $layer->id) }}" class="btn-view">
                    Lihat Detail
                </a>
            </div>
        </div>
    </div>
@empty
    <div class="empty-state">
        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Belum ada layer</h3>
        <p>Saat ini tidak ada data layer yang dapat ditampilkan.</p>
    </div>
@endforelse
    </div>
@endsection

@section('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const layersData = @json($layers);
        
        const initPreviewMap = (mapData) => {
            const mapContainerId = `map-${mapData.id}`;
            const mapContainer = document.getElementById(mapContainerId);
            if (!mapContainer || mapContainer.classList.contains('leaflet-container')) return;
            
            const previewMap = L.map(mapContainerId, {
                zoomControl: false, scrollWheelZoom: false, dragging: false, doubleClickZoom: false,
                touchZoom: false, boxZoom: false, keyboard: false,
            }).setView([-2.54, 118.01], 5);
            
            L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                attribution: '© Google', maxZoom: 20,
            }).addTo(previewMap);
            
            const firstLayer = mapData.layers && mapData.layers.length > 0 ? mapData.layers[0] : {};
            const firstLayerPivot = firstLayer.pivot || {};

            const createStyle = () => ({
                color: firstLayerPivot.stroke_color || mapData.default_stroke_color || '#e74c3c',
                fillColor: firstLayerPivot.fill_color || mapData.default_fill_color || '#e74c3c',
                weight: firstLayerPivot.weight || mapData.default_weight || 3,
                opacity: firstLayerPivot.opacity || mapData.default_opacity || 1.0,
                fillOpacity: (firstLayerPivot.opacity || mapData.default_opacity || 0.3) * 0.8,
            });
            
            const renderFeatures = async () => {
                try {
                    const geojsonUrl = `{{ url('maps') }}/${mapData.id}/geojson`;
                    const response = await fetch(geojsonUrl);
                    if (!response.ok) throw new Error('GeoJSON not found');
                    
                    const geojsonData = await response.json();
                    
                    const geoLayer = L.geoJSON(geojsonData, {
                        style: (feature) => createStyle(),
                        pointToLayer: (feature, latlng) => {
                            const layerType = firstLayerPivot.layer_type || 'marker';
                            const style = createStyle();
                            const iconUrl = firstLayerPivot.icon_url || mapData.default_icon_url;
                            
                            if (layerType === 'circle') {
                                return L.circle(latlng, { ...style, radius: firstLayerPivot.radius || mapData.default_radius || 1000 });
                            }
                            if (layerType === 'marker' && iconUrl) {
                                const icon = L.icon({ iconUrl: iconUrl, iconSize: [24, 24], iconAnchor: [12, 12] });
                                return L.marker(latlng, { icon });
                            }
                            return L.circleMarker(latlng, { ...style, radius: 6 });
                        }
                    }).addTo(previewMap);
                    
                    if (geoLayer.getBounds().isValid()) {
                        previewMap.fitBounds(geoLayer.getBounds(), { padding: [10, 10], maxZoom: 12 });
                    }
                    
                } catch (error) {
                    const lat = parseFloat(firstLayerPivot.lat || mapData.lat);
                    const lng = parseFloat(firstLayerPivot.lng || mapData.lng);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const latlng = L.latLng(lat, lng);
                        const style = createStyle();
                        let fallbackLayer;
                        const layerType = firstLayerPivot.layer_type || 'marker';
                        
                        if (layerType === 'circle') {
                            fallbackLayer = L.circle(latlng, { ...style, radius: firstLayerPivot.radius || mapData.radius || 1000 });
                        } else if (layerType === 'marker' && (firstLayerPivot.icon_url || mapData.icon_url)) {
                            const icon = L.icon({ iconUrl: firstLayerPivot.icon_url || mapData.icon_url, iconSize: [24, 24], iconAnchor: [12, 12] });
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
        
        Object.values(mapsData).forEach(initPreviewMap);
    });
    </script>
@endsection
