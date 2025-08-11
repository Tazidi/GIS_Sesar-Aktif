@extends('layouts.app')

@section('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Container & Layout */
        .map-gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Header */
        .map-header {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .map-title {
            font-size: 28px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 8px 0;
            line-height: 1.2;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #374151;
        }

        .back-link::before {
            content: '←';
            margin-right: 8px;
        }

        /* Map Container */
        .map-wrapper {
            margin-bottom: 24px;
        }

        .map-container {
            position: relative;
            height: 600px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        #map {
            height: 100%;
            width: 100%;
        }
        
        /* Modal Styles */
        .modal-overlay {
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); 
            backdrop-filter: blur(4px); 
            z-index: 1050;
        }

        .modal-content {
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%);
            background: white; 
            border-radius: 16px; 
            width: 90%; 
            max-width: 500px;
            max-height: 80vh; 
            overflow: hidden; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            padding: 20px 24px 16px; 
            border-bottom: 1px solid #f3f4f6;
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }

        .modal-title { 
            font-size: 20px; 
            font-weight: 600; 
            color: #1f2937; 
            margin: 0; 
        }

        .modal-close {
            background: none; 
            border: none; 
            font-size: 24px; 
            color: #9ca3af; 
            cursor: pointer;
            padding: 4px; 
            border-radius: 6px; 
            transition: all 0.2s ease;
        }

        .modal-close:hover { 
            background: #f3f4f6; 
            color: #6b7280; 
        }

        .modal-body { 
            padding: 20px 24px 24px; 
            max-height: calc(80vh - 70px); 
            overflow-y: auto; 
        }

        /* Popup Styles */
        .leaflet-popup-content-wrapper {
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .leaflet-popup-content { 
            margin: 16px; 
            font-size: 14px; 
            line-height: 1.4; 
        }

        .btn-detail {
            background: #2563eb; 
            color: white; 
            border: none; 
            padding: 8px 16px;
            border-radius: 6px; 
            font-size: 13px; 
            font-weight: 500; 
            cursor: pointer;
            margin-top: 12px; 
            display: block; 
            width: 100%; 
            text-align: center;
            transition: background-color 0.2s ease;
        }

        .btn-detail:hover { 
            background: #1d4ed8; 
        }
        
        /* Detail styles */
        .detail-item { 
            margin-bottom: 12px; 
            border-bottom: 1px solid #f3f4f6; 
            padding-bottom: 8px; 
        }

        .detail-label { 
            font-weight: bold; 
            color: #374151; 
            margin-bottom: 4px; 
        }

        .detail-value { 
            color: #6b7280; 
        }
    </style>
@endsection

@section('content')
    <div class="map-gallery-container">
        <header class="map-header">
            <h1 class="map-title">
                {{ $map->name }}
            </h1>
            <a href="{{ route('gallery_maps.index') }}" class="back-link">
                Kembali ke Galeri Peta
            </a>
        </header>

        <div class="map-wrapper">
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>
        
        {{-- Deskripsi Peta --}}
        <div class="max-w-2xl mx-auto bg-white p-6 shadow-lg rounded-lg mt-6">
            <h2 class="text-lg font-bold text-gray-800 mb-2">Deskripsi Peta</h2>
            <p class="text-sm text-gray-600">
                {{ $map->description ?? 'Belum ada deskripsi yang tersedia untuk peta ini.' }}
            </p>
        </div>

        <div id="detail-modal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Detail Informasi</h2>
                    <button class="modal-close" onclick="closeModal()">×</button>
                </div>
                <div class="modal-body">
                    <div id="detail-content"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Data maps dari controller -->
    <script type="application/json" id="maps-data">
        [
            @foreach ($maps as $index => $map)
            {
                "id": {{ $map->id }},
                "name": "{{ addslashes($map->name) }}",
                "description": "{{ addslashes($map->description) }}",
                "image_path": "{{ $map->image_path ? asset($map->image_path) : '' }}",
                "layer_type": "{{ $map->layer_type ?? 'marker' }}",
                "stroke_color": "{{ $map->stroke_color ?? '#000000' }}",
                "fill_color": "{{ $map->fill_color ?? '#ff0000' }}",
                "opacity": {{ $map->opacity ?? 0.8 }},
                "weight": {{ $map->weight ?? 2 }},
                "radius": {{ $map->radius ?? 300 }},
                "icon_url": "{{ $map->icon_url ?? '' }}",
                "lat": {{ $map->lat ?? 0 }},
                "lng": {{ $map->lng ?? 0 }},
                "layer_name": "{{ addslashes($map->layer->nama_layer ?? 'Layer Tanpa Nama') }}",
                "geometry": {!! $map->geometry ? json_encode(json_decode($map->geometry)) : 'null' !!},
                "features": [
                    @foreach ($map->features as $feature)
                    {
                        "geometry": {!! $feature->geometry ? json_encode($feature->geometry) : 'null' !!},
                        "properties": {!! $feature->properties ? json_encode($feature->properties) : 'null' !!},
                        "image_path": "{{ $feature->image_path ? asset($feature->image_path) : '' }}",
                        "caption": "{{ addslashes($feature->caption ?? '') }}",
                        "technical_info": {!! json_encode($feature->technical_info ?? '') !!}
                    }@if(!$loop->last),@endif
                    @endforeach
                ]
            }@if(!$loop->last),@endif
            @endforeach
        ]
    </script>

    <script>
        // Parse data maps dari JSON
        let mapsData = [];
        try {
            const jsonData = document.getElementById('maps-data').textContent;
            mapsData = JSON.parse(jsonData);
        } catch (error) {
            console.error('Error parsing maps data:', error);
            mapsData = [];
        }

        // ===================================
        // FUNGSI MODAL & TAMPILAN DETAIL
        // ===================================
        function openModal(featureData) {
            displayDetailContent(featureData);
            document.getElementById('detail-modal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('detail-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function formatLabel(key) {
            if (!key || typeof key !== 'string') return '';
            return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function displayDetailContent(featureData) {
            const detailContent = document.getElementById('detail-content');
            let content = '';
            
            // KONDISI #1: Untuk data manual (dari maps table)
            if (featureData.dataSource === 'manual') {
                const name = featureData.name || 'Tidak ada nama';
                const description = featureData.description || 'Tidak ada deskripsi';
                const photo = featureData.image_path || '';

                content += `<div class="detail-item"><div class="detail-label">Nama:</div><div class="detail-value">${name}</div></div>`;
                content += `<div class="detail-item"><div class="detail-label">Deskripsi:</div><div class="detail-value">${description || '<i>Tidak ada deskripsi</i>'}</div></div>`;

                // Tampilkan foto
                if (photo) {
                    content += `<div style="margin-top: 15px;">
                        <div class="detail-label">Foto:</div>
                        <div style="text-align: center; margin-top: 8px;">
                            <img src="${photo}" 
                                alt="Foto ${name}" 
                                style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer;"
                                onclick="window.open('${photo}', '_blank')"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none; padding: 20px; background: #f5f5f5; border-radius: 8px; color: #666; text-align: center;">
                                Foto tidak dapat dimuat
                            </div>
                        </div>
                    </div>`;
                } else {
                    content += `<div style="margin-top: 15px;">
                        <div class="detail-label">Foto:</div>
                        <div style="padding: 20px; background: #f5f5f5; border-radius: 8px; color: #666; text-align: center; margin-top: 8px;">
                            Tidak ada foto
                        </div>
                    </div>`;
                }
            }
            // KONDISI #2: Untuk data GeoJSON (dari map_features table)
            else if (featureData.dataSource === 'geojson') {
                const title = featureData.name || featureData.title || featureData.nama || featureData.Name || 'Detail Fitur';
                content += `<div class="detail-item"><div class="detail-label">Nama:</div><div class="detail-value">${title}</div></div>`;

                // Loop melalui properti lain
                Object.entries(featureData).forEach(([key, value]) => {
                    const excludedKeys = ['name', 'title', 'nama', 'Name', 'dataSource', 'feature_image_path', 'caption', 'technical_info'];
                    if (!excludedKeys.includes(key) && value) {
                        const label = formatLabel(key);
                        content += `<div class="detail-item"><div class="detail-label">${label}:</div><div class="detail-value">${value}</div></div>`;
                    }
                });

                // Tampilkan foto
                if (featureData.feature_image_path) {
                    content += `<div style="margin-top: 15px;">
                        <div class="detail-label">Foto:</div>
                        <div style="text-align: center; margin-top: 8px;">
                            <img src="${featureData.feature_image_path}" 
                                alt="Foto ${title}" 
                                style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer;"
                                onclick="window.open('${featureData.feature_image_path}', '_blank')"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none; padding: 20px; background: #f5f5f5; border-radius: 8px; color: #666; text-align: center;">
                                Foto tidak dapat dimuat
                            </div>
                        </div>
                    </div>`;
                } else {
                    content += `<div style="margin-top: 15px;">
                        <div class="detail-label">Foto:</div>
                        <div style="padding: 20px; background: #f5f5f5; border-radius: 8px; color: #666; text-align: center; margin-top: 8px;">
                            Tidak ada foto
                        </div>
                    </div>`;
                }

                // Caption terpisah
                if (featureData.caption) {
                    content += `<div style="margin-top: 10px;">
                        <p style="font-style: italic; color: #555;">${featureData.caption}</p>
                    </div>`;
                }

                // KODE BARU: Tampilkan Technical Info
                if (featureData.technical_info) {
                    content += `<div style="margin-top: 15px;">
                        <div class="detail-label">Informasi Teknis:</div>
                        <pre style="white-space: pre-wrap; word-wrap: break-word; background-color: #f3f4f6; padding: 10px; border-radius: 6px; font-family: 'Courier New', Courier, monospace; font-size: 13px; color: #374151;">${featureData.technical_info}</pre>
                    </div>`;
                }
            }

            detailContent.innerHTML = content;
        }

        function createPopupContent(feature, mapData) {
            const props = feature.properties || {};
            const isGeoJSON = Object.keys(props).length > 0;

            let dataForModal;
            let title;

            if (isGeoJSON) {
                // Data dari map_features table
                title = props.Name || props.name || props.title || props.nama || 'Informasi';
                dataForModal = {
                    ...props,
                    dataSource: 'geojson',
                    feature_image_path: feature.feature_image_path || '',
                    caption: feature.caption || '',
                    technical_info: feature.technical_info || '',
                };
            } else {
                // Data manual dari maps table
                title = mapData.name || 'Informasi';
                dataForModal = {
                    dataSource: 'manual',
                    name: mapData.name,
                    description: mapData.description,
                    image_path: mapData.image_path,
                };
            }

            const encodedData = encodeURIComponent(JSON.stringify(dataForModal));

            return `
                <div style="font-weight: bold; margin-bottom: 8px;">${title}</div>
                <button class="btn-detail" data-feature='${encodedData}'>Lihat Detail</button>
            `;
        }

        // ===================================
        // INISIALISASI PETA
        // ===================================
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('map', {
                preferCanvas: true,
                zoomControl: true
            }).setView([-2.5, 117], 5);

            // Base Layers
            const baseLayers = {
                "Google Maps": L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20, 
                    subdomains: ['mt0','mt1','mt2','mt3'],
                    attribution: '© Google'
                }),
                "Google Satellite": L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    maxZoom: 20, 
                    subdomains: ['mt0','mt1','mt2','mt3'],
                    attribution: '© Google'
                }),
                "OpenTopoMap": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    maxZoom: 17,
                    attribution: '© OpenTopoMap'
                })
            };
            
            baseLayers["Google Satellite"].addTo(map);
            L.control.layers(baseLayers).addTo(map);

            // ===================================
            // MEMPROSES SEMUA DATA PETA
            // ===================================
            const allBounds = [];

            mapsData.forEach(mapData => {
                // Siapkan style untuk setiap peta
                const style = {
                    color: mapData.stroke_color || "#3388ff",
                    weight: mapData.weight || 3,
                    opacity: mapData.opacity || 0.8,
                    fillColor: mapData.fill_color || "#3388ff",
                    fillOpacity: (mapData.opacity || 0.8) * 0.5,
                };

                // 1. Proses features dari map_features table jika ada
                if (mapData.features && mapData.features.length > 0) {
                    mapData.features.forEach(feature => {
                        const geometry = typeof feature.geometry === 'string' 
                            ? JSON.parse(feature.geometry) 
                            : feature.geometry;
                        
                        if (geometry) {
                            const geojsonFeature = {
                                "type": "Feature",
                                "geometry": geometry,
                                "properties": {
                                    ...(feature.properties || {}),
                                    feature_image_path: feature.image_path || null,
                                    caption: feature.caption || null,
                                    technical_info: feature.technical_info || null,
                                },
                                
                            };

                            const layer = L.geoJSON(geojsonFeature, {
                                style: function(feature) {
                                    return style;
                                },
                                onEachFeature: function(feature, layer) {
                                    // Tambahkan image_path dan caption ke feature
                                    feature.feature_image_path = geojsonFeature.properties.feature_image_path;
                                    feature.caption = geojsonFeature.properties.caption;
                                    feature.technical_info = geojsonFeature.properties.technical_info;

                                    const popupContent = createPopupContent(feature, mapData);
                                    layer.bindPopup(popupContent);
                                },
                                pointToLayer: function(feature, latlng) {
                                    const layerType = mapData.layer_type || 'marker';
                                    
                                    if (layerType === 'circle') {
                                        return L.circle(latlng, { 
                                            ...style, 
                                            radius: mapData.radius || 300 
                                        });
                                    } else if (layerType === 'marker' && mapData.icon_url) {
                                        const customIcon = L.icon({
                                            iconUrl: mapData.icon_url,
                                            iconSize: [32, 32],
                                            iconAnchor: [16, 16],
                                            popupAnchor: [0, -16]
                                        });
                                        return L.marker(latlng, { icon: customIcon });
                                    }
                                    return L.circleMarker(latlng, { ...style, radius: 8 });
                                }
                            }).addTo(map);

                            // Tambahkan bounds
                            if (layer.getBounds && layer.getBounds().isValid()) {
                                allBounds.push(layer.getBounds());
                            }
                        }
                    });
                }
                // 2. Fallback ke geometry dari maps table
                else if (mapData.geometry && typeof mapData.geometry === 'object') {
                    const layer = L.geoJSON(mapData.geometry, {
                        style: function(feature) {
                            return style;
                        },
                        onEachFeature: function(feature, layer) {
                            const popupContent = createPopupContent(feature, mapData);
                            layer.bindPopup(popupContent);
                        },
                        pointToLayer: function(feature, latlng) {
                            const layerType = mapData.layer_type || 'marker';
                            
                            if (layerType === 'circle') {
                                return L.circle(latlng, { 
                                    ...style, 
                                    radius: mapData.radius || 300 
                                });
                            } else if (layerType === 'marker' && mapData.icon_url) {
                                const customIcon = L.icon({
                                    iconUrl: mapData.icon_url,
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 16],
                                    popupAnchor: [0, -16]
                                });
                                return L.marker(latlng, { icon: customIcon });
                            }
                            return L.circleMarker(latlng, { ...style, radius: 8 });
                        }
                    }).addTo(map);

                    if (layer.getBounds && layer.getBounds().isValid()) {
                        allBounds.push(layer.getBounds());
                    }
                }
                // 3. Fallback ke koordinat manual
                else if (mapData.lat && mapData.lng) {
                    const latlng = L.latLng(mapData.lat, mapData.lng);
                    const layerType = mapData.layer_type || 'marker';
                    let layer;

                    if (layerType === 'circle') {
                        layer = L.circle(latlng, { 
                            ...style, 
                            radius: mapData.radius || 300 
                        });
                    } else if (layerType === 'marker' && mapData.icon_url) {
                        const customIcon = L.icon({
                            iconUrl: mapData.icon_url,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16],
                            popupAnchor: [0, -16]
                        });
                        layer = L.marker(latlng, { icon: customIcon });
                    } else {
                        layer = L.circleMarker(latlng, { ...style, radius: 8 });
                    }

                    const popupContent = createPopupContent({ properties: {} }, mapData);
                    layer.bindPopup(popupContent);
                    layer.addTo(map);
                    
                    allBounds.push(L.latLngBounds([latlng]));
                }
            });

            // Zoom ke semua bounds jika ada data
            if (allBounds.length > 0) {
                const group = L.featureGroup();
                allBounds.forEach(bounds => {
                    if (bounds.isValid()) {
                        group.addLayer(L.rectangle(bounds, { opacity: 0 }));
                    }
                });
                
                if (group.getLayers().length > 0) {
                    map.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 16 });
                }
            }

            // Menangani klik pada tombol detail di popup
            map.on('popupopen', function() {
                const detailButton = document.querySelector('.btn-detail');
                if (detailButton) {
                    detailButton.addEventListener('click', function(e) {
                        try {
                            const featureData = JSON.parse(decodeURIComponent(e.target.getAttribute('data-feature')));
                            openModal(featureData);
                        } catch (error) {
                            console.error('Error parsing feature data:', error);
                        }
                    });
                }
            });
            
            // Menangani klik pada overlay modal
            document.getElementById('detail-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Menangani ESC key untuk menutup modal
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
        });
    </script>
@endsection