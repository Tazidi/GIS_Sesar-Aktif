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
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 sm:p-12 mb-12 text-center">
    {{-- Ikon Globe --}}
    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
        <svg class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A11.953 11.953 0 0112 16.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12a8.959 8.959 0 01-2.284-5.253" />
        </svg>
    </div>
    <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
        Galeri <span class="text-blue-600">Peta</span>
    </h1>
    <p class="mt-4 max-w-2xl mx-auto text-lg text-gray-600">
        Koleksi peta dan visualisasi data geografis Sesar Jawa Bagian Barat.
    </p>
</div>

    {{-- Container untuk Peta --}}
    {{-- Container untuk Peta --}}
<div class="space-y-12 -mt-16"> 
    @forelse ($maps as $map)
        {{-- Kartu Peta --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            <div class="flex flex-col @if($loop->odd) md:flex-row @else md:flex-row-reverse @endif">
                
                {{-- Bagian Peta --}}
                <div class="md:w-2/5 bg-gray-50 p-4 sm:p-6 flex items-center justify-center">
                    <div id="map-{{ $map->id }}" class="w-full h-64 md:h-full rounded-lg border border-gray-200"></div>
                </div>
                
                {{-- Bagian Informasi dengan Tata Letak Baru --}}
                <div class="md:w-3/5 p-6 sm:p-8 flex flex-col">
                    {{-- Wrapper untuk konten atas (judul, deskripsi, detail) --}}
                    <div class="flex-grow">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">
                            <a href="{{ route('gallery.show', $map->id) }}" class="hover:text-blue-600 hover:underline">
                                {{ $map->name }}
                            </a>
                        </h2>
                        
                        <p class="text-gray-600 text-sm leading-relaxed mb-6">
                            {{ $map->description ?? 'Peta ini menyajikan informasi geografis penting.' }}
                        </p>
                        
                        {{-- Detail Tambahan --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4 pt-4 border-t border-gray-100">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Fitur</h4>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ ucfirst($map->layer_type) }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Koordinat</h4>
                                <p class="text-sm font-medium text-gray-900 mt-1">
                                    {{ ($map->lat && $map->lng) ? number_format($map->lat, 5).', '.number_format($map->lng, 5) : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Aset Visual</h4>
                                <div class="flex items-center space-x-2 mt-1">
                                    @if ($map->icon_url || $map->image_path)
                                        @if ($map->icon_url) <img src="{{ asset($map->icon_url) }}" alt="Ikon" class="h-6 w-6 object-contain"> @endif
                                        @if ($map->image_path) <img src="{{ asset($map->image_path) }}" alt="Gambar" class="h-6 w-6 object-cover rounded"> @endif
                                    @else
                                        <span class="italic text-gray-400 text-sm">Tidak ada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi di Bagian Bawah --}}
                    <div class="mt-8 pt-4 border-t border-gray-100">
                         <a href="{{ route('gallery.show', $map->id) }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-lg hover:bg-blue-700 transition-colors">
                            Lihat Detail Peta
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-16 bg-white rounded-lg shadow-md">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"></path></svg>
            <h3 class="text-xl font-medium text-gray-900">Belum ada peta tersedia</h3>
            <p class="text-gray-500 mt-2">Saat ini tidak ada data peta interaktif yang dapat ditampilkan.</p>
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