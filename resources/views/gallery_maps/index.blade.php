@extends('layouts.app')

@section('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />


    <style>
        /* Gaya untuk peta pratinjau */
        .preview-map {
            height: 200px;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }
        
        /* Gaya untuk thumbnail gambar dan ikon */
        .map-thumbnail {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 0.25rem;
            border: 1px solid #cbd5e1;
        }
        
        /* Menonaktifkan interaksi pada peta pratinjau */
        .preview-map.leaflet-container {
            background-color: #f7fafc;
        }
        
        /* Layout responsive untuk card peta */
        .map-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            margin-bottom: 2rem;
        }
        
        .map-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
        }
        
        .map-content {
            display: flex;
            flex-direction: column;
        }
        
        .map-visual {
            background: #f9fafb;
            padding: 1.5rem;
        }
        
        .map-info {
            padding: 1.5rem;
            flex-grow: 1;
        }
        
        .map-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
        }
        
        .map-description {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .map-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        
        .detail-value {
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }
        
        .asset-preview {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        /* Layout untuk desktop - side by side */
        @media (min-width: 768px) {
            .map-content {
                flex-direction: row;
            }
            
            .map-visual {
                flex: 0 0 400px;
            }
            
            .map-info {
                flex: 1;
            }
            
            .map-details {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .map-details {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
@endsection

@section('content')
    {{-- Header untuk Galeri Peta Interaktif --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2"><center>Galeri Peta</center></h2>
        <p class="text-gray-600"><center>Koleksi peta dan visualisasi data geografis Sesar Jawa Bagian Barat</center></p>
    </div>

    {{-- Container untuk Peta --}}
    <div class="space-y-6">
        @forelse ($maps as $map)
            <div class="map-card">
                <div class="map-content">
                    {{-- Bagian Peta (Kiri pada desktop, Atas pada mobile) --}}
                    <div class="map-visual">
                        <div id="map-{{ $map->id }}" class="preview-map"></div>
                    </div>
                    
                    {{-- Bagian Informasi (Kanan pada desktop, Bawah pada mobile) --}}
                    <div class="map-info">
                        <div class="map-title">
                            <a href="{{ route('gallery.show', $map->id) }}" 
                            class="text-blue-600 hover:underline">
                                {{ $map->name }}
                            </a>
                        </div>
                        
                        <div class="map-description">
                            @if ($map->description)
                                {{ $map->description }}
                            @else
                                Peta {{ ucfirst($map->layer_type) }} yang menampilkan informasi geografis dengan koordinat 
                                {{ $map->lat && $map->lng ? number_format($map->lat, 6) . ', ' . number_format($map->lng, 6) : 'yang telah ditentukan' }}.
                                @if ($map->layer_type == 'circle' && $map->radius)
                                    Area cakupan dengan radius {{ number_format($map->radius) }} meter.
                                @endif
                            @endif
                        </div>
                        
                        <div class="map-details">
                            <div class="detail-item">
                                <span class="detail-label">Jenis Fitur</span>
                                <span class="detail-value">{{ ucfirst($map->layer_type) }}</span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Koordinat</span>
                                <span class="detail-value">
                                    @if ($map->lat && $map->lng)
                                        {{ number_format($map->lat, 6) }}, {{ number_format($map->lng, 6) }}
                                    @else
                                        <span class="text-gray-400 italic">Tidak tersedia</span>
                                    @endif
                                </span>
                            </div>
                            
                            @if ($map->layer_type == 'circle' && $map->radius)
                                <div class="detail-item">
                                    <span class="detail-label">Radius</span>
                                    <span class="detail-value">{{ number_format($map->radius) }} meter</span>
                                </div>
                            @endif
                            
                            <div class="detail-item">
                                <span class="detail-label">Aset Visual</span>
                                <div class="detail-value">
                                    @if ($map->icon_url || $map->image_path)
                                        <div class="asset-preview">
                                            @if ($map->icon_url)
                                                <img src="{{ asset($map->icon_url) }}" 
                                                    alt="Ikon {{ $map->name }}" 
                                                    title="Ikon: {{ basename($map->icon_url) }}" 
                                                    class="map-thumbnail">
                                            @endif
                                            @if ($map->image_path)
                                                <img src="{{ asset($map->image_path) }}" 
                                                    alt="Gambar {{ $map->name }}" 
                                                    title="Gambar: {{ basename($map->image_path) }}" 
                                                    class="map-thumbnail">
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada aset visual</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if ($map->file_path)
                                <div class="detail-item">
                                    <span class="detail-label">Data GeoJSON</span>
                                    <div class="detail-value">
                                        <a href="{{ asset($map->file_path) }}" 
                                        target="_blank" 
                                        class="inline-flex items-center text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            Lihat File Data
                                        </a>
                                    </div>
                                </div>
                            @endif
                            
                            @if ($map->fault_name || $map->magnitude || $map->fault_type)
                                <div class="detail-item">
                                    <span class="detail-label">Info Sesar</span>
                                    <div class="detail-value">
                                        @if ($map->fault_name)
                                            <div class="text-sm"><strong>Nama:</strong> {{ $map->fault_name }}</div>
                                        @endif
                                        @if ($map->magnitude)
                                            <div class="text-sm"><strong>Magnitudo:</strong> {{ $map->magnitude }}</div>
                                        @endif
                                        @if ($map->fault_type)
                                            <div class="text-sm"><strong>Tipe:</strong> 
                                                @switch($map->fault_type)
                                                    @case('R')
                                                        Reverse Fault
                                                        @break
                                                    @case('N')
                                                        Normal Fault
                                                        @break
                                                    @case('SS')
                                                        Strike-Slip Fault
                                                        @break
                                                    @default
                                                        {{ $map->fault_type }}
                                                @endswitch
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-lg shadow">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Belum ada peta tersedia</h3>
                <p class="text-gray-500">Tidak ada data peta interaktif yang dapat ditampilkan untuk kategori Sesar Jawa Bagian Barat.</p>
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
        // Data peta dari controller
        const mapsData = @json($maps);
        
        // Fungsi untuk inisialisasi setiap peta pratinjau
        const initPreviewMap = (mapData) => {
            const mapContainerId = `map-${mapData.id}`;
            const geojsonUrl = `{{ url('maps') }}/${mapData.id}/geojson`;
            
            // Pastikan container ada sebelum inisialisasi
            const mapContainer = document.getElementById(mapContainerId);
            if (!mapContainer) {
                console.warn(`Map container ${mapContainerId} tidak ditemukan`);
                return;
            }
            
            // Inisialisasi peta dengan opsi interaksi dinonaktifkan
            const previewMap = L.map(mapContainerId, {
                zoomControl: false,
                scrollWheelZoom: false,
                dragging: false,
                doubleClickZoom: false,
                touchZoom: false,
                boxZoom: false,
                keyboard: false,
            }).setView([-2.54, 118.01], 5); // Center of Indonesia
            
            // Tambahkan tile layer OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18,
            }).addTo(previewMap);
            
            // Fungsi untuk membuat style layer dari properties
            const createStyle = (props = {}) => ({
                color: props.stroke_color || mapData.stroke_color || '#e74c3c',
                fillColor: props.fill_color || mapData.fill_color || '#e74c3c',
                weight: props.weight || mapData.weight || 3,
                opacity: props.opacity || mapData.opacity || 1.0,
                fillOpacity: (props.fill_opacity || mapData.fill_opacity || 0.3) * 0.8,
            });
            
            // Fungsi untuk menampilkan fitur GeoJSON atau fallback
            const renderFeatures = async () => {
                try {
                    const response = await fetch(geojsonUrl);
                    if (!response.ok) throw new Error('GeoJSON not found');
                    
                    const geojsonData = await response.json();
                    
                    // Handle single feature atau feature collection
                    const geoLayer = L.geoJSON(geojsonData, {
                        style: (feature) => createStyle(feature.properties),
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
                            
                            if (type === 'marker' && iconUrl) {
                                const icon = L.icon({ 
                                    iconUrl: iconUrl, 
                                    iconSize: [32, 32], 
                                    iconAnchor: [16, 16] 
                                });
                                return L.marker(latlng, { icon });
                            }
                            
                            return L.circleMarker(latlng, { ...style, radius: 8 });
                        },
                        onEachFeature: (feature, layer) => {
                            // const title = feature.properties?.name || mapData.name || 'Info';
                            // const description = feature.properties?.description || mapData.description || '';
                            
                            // let popupContent = `<div class="text-center">
                            //     <h4 class="font-bold text-gray-900 mb-1">${title}</h4>`;
                            
                            // if (description) {
                            //     popupContent += `<p class="text-sm text-gray-600">${description}</p>`;
                            // }
                            
                            // popupContent += `</div>`;
                            
                            // layer.bindPopup(popupContent, {
                            //     maxWidth: 200,
                            //     className: 'custom-popup'
                            // });
                        }
                    }).addTo(previewMap);
                    
                    // Fit bounds jika valid
                    if (geoLayer.getBounds().isValid()) {
                        previewMap.fitBounds(geoLayer.getBounds(), { 
                            padding: [20, 20], 
                            maxZoom: 14 
                        });
                    }
                    
                } catch (error) {
                    console.warn(`GeoJSON gagal dimuat untuk peta ${mapData.name}:`, error.message);
                    
                    // Fallback: Gunakan data lat/lng dari database
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
                                iconSize: [32, 32], 
                                iconAnchor: [16, 16] 
                            });
                            fallbackLayer = L.marker(latlng, { icon });
                        } else {
                            fallbackLayer = L.circleMarker(latlng, { ...style, radius: 8 });
                        }

                        fallbackLayer.addTo(previewMap); // Tanpa bindPopup

                        // fallbackLayer.bindPopup(`<div class="text-center">
                        //     <h4 class="font-bold text-gray-900">${mapData.name}</h4>
                        //     ${mapData.description ? `<p class="text-sm text-gray-600 mt-1">${mapData.description}</p>` : ''}
                        // </div>`).addTo(previewMap);
                        
                        previewMap.setView(latlng, 10);
                    } else {
                        // Jika tidak ada koordinat, tampilkan peta Indonesia
                        console.warn(`Tidak ada koordinat valid untuk peta ${mapData.name}`);
                    }
                } finally {
                    // Pastikan peta di-render ulang dengan ukuran yang benar
                    setTimeout(() => {
                        previewMap.invalidateSize();
                    }, 250);
                }
            };
            
            renderFeatures();
        };
        
        // Inisialisasi semua peta
        if (mapsData && mapsData.length > 0) {
            mapsData.forEach(initPreviewMap);
        }
        
        // Handle window resize untuk memastikan peta ter-render dengan benar
        window.addEventListener('resize', function() {
            mapsData.forEach(mapData => {
                const mapContainer = document.getElementById(`map-${mapData.id}`);
                if (mapContainer && mapContainer._leaflet_id) {
                    const map = window[mapContainer._leaflet_id];
                    if (map) {
                        setTimeout(() => map.invalidateSize(), 100);
                    }
                }
            });
        });
    });
    </script>
@endsection