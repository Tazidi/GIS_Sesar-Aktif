@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="{{ asset('css/map.css') }}">
@endsection

@section('content')
    <div class="container">
        <h1>Halaman Visualisasi Peta</h1>
        <a href="{{ route('home') }}">← Kembali ke Beranda</a>

        <!-- Data JSON untuk JavaScript -->
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
                    "geometry": {!! $map->geometry ? json_encode(json_decode($map->geometry)) : 'null' !!}
                }@if(!$loop->last),@endif
                @endforeach
            ]
        </script>

        <div class="layer-controls">
            <h3>Pilih Layer</h3>
            @php
                $layerGroups = [];
                foreach ($maps as $map) {
                    $layerName = $map->layer->nama_layer ?? 'Layer Tanpa Nama';
                    if (!isset($layerGroups[$layerName])) {
                        $layerGroups[$layerName] = [];
                    }
                    $layerGroups[$layerName][] = $map;
                }
            @endphp

            @foreach ($layerGroups as $layerName => $layerMaps)
                <div class="layer-item">
                    <label>
                        <input type="checkbox" class="layer-group-toggle" data-layer-name="{{ $layerName }}" checked>
                        {{ $layerName }}
                    </label>
                </div>
            @endforeach
        </div>

        <div class="map-container">
            <div id="map"></div>

            <!-- Legend Box -->
            <div class="legend-box">
                <div class="legend-title">Keterangan Peta</div>
                <div id="legend-content">
                    @foreach ($layerGroups as $layerName => $layerMaps)
                        @php $firstMap = $layerMaps[0]; @endphp
                        <div class="legend-item" data-legend-layer="{{ $layerName }}">
                            <div class="legend-symbol {{ $firstMap->layer_type ?? 'marker' }}"
                                style="
                                    @if (($firstMap->layer_type ?? 'marker') == 'marker') background-color: {{ $firstMap->fill_color ?? '#ff0000' }};
                                        border-color: {{ $firstMap->stroke_color ?? '#000000' }};
                                    @elseif(($firstMap->layer_type ?? 'marker') == 'circle')
                                        border-color: {{ $firstMap->stroke_color ?? '#000000' }};
                                        background-color: {{ $firstMap->fill_color ?? '#ff0000' }};
                                        opacity: {{ $firstMap->opacity ?? 0.8 }};
                                        border-width: {{ $firstMap->weight ?? 2 }}px;
                                    @elseif(($firstMap->layer_type ?? 'marker') == 'polyline')
                                        background-color: {{ $firstMap->stroke_color ?? '#000000' }};
                                        height: {{ $firstMap->weight ?? 2 }}px;
                                    @elseif(($firstMap->layer_type ?? 'marker') == 'polygon')
                                        background-color: {{ $firstMap->fill_color ?? '#ff0000' }};
                                        border-color: {{ $firstMap->stroke_color ?? '#000000' }};
                                        opacity: {{ $firstMap->opacity ?? 0.8 }};
                                        border-width: {{ $firstMap->weight ?? 2 }}px; @endif
                                ">
                                @if (($firstMap->layer_type ?? 'marker') == 'marker' && $firstMap->icon_url)
                                    <img src="{{ $firstMap->icon_url }}"
                                        style="width: 16px; height: 16px; border-radius: 50%;" alt="icon">
                                @endif
                            </div>
                            <div class="legend-text">
                                {{ $layerName }}
                                <br>
                                <small style="color: #777;">
                                    @if (($firstMap->layer_type ?? 'marker') == 'marker')
                                        Penanda Lokasi
                                    @elseif(($firstMap->layer_type ?? 'marker') == 'circle')
                                        Lingkaran ({{ $firstMap->radius ?? 300 }}m)
                                    @elseif(($firstMap->layer_type ?? 'marker') == 'polyline')
                                        Garis/Jalur
                                    @elseif(($firstMap->layer_type ?? 'marker') == 'polygon')
                                        Area/Wilayah
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Modal Detail -->
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
    <script>
        // Ambil data dari script JSON
        const mapsData = JSON.parse(document.getElementById('maps-data').textContent);

        function updateLegend() {
            const legendContent = document.getElementById('legend-content');
            let legendHTML = '';

            const layerToggles = document.querySelectorAll('.layer-group-toggle');

            layerToggles.forEach(toggle => {
                const layerName = toggle.getAttribute('data-layer-name');
                const isVisible = toggle.checked;

                // Ambil sample data untuk style dari layer pertama dengan nama yang sama
                const sampleMap = mapsData.find(map => map.layer_name === layerName);

                if (sampleMap) {
                    const layerType = sampleMap.layer_type || 'marker';
                    const strokeColor = sampleMap.stroke_color || '#000000';
                    const fillColor = sampleMap.fill_color || '#ff0000';
                    const opacity = parseFloat(sampleMap.opacity) || 0.8;
                    const weight = parseInt(sampleMap.weight) || 2;

                    let description = '';
                    switch (layerType) {
                        case 'marker':
                            description = 'Penanda Lokasi';
                            break;
                        case 'circle':
                            description = `Lingkaran (${sampleMap.radius || 300}m)`;
                            break;
                        case 'polyline':
                            description = 'Garis/Jalur';
                            break;
                        case 'polygon':
                            description = 'Area/Wilayah';
                            break;
                        default:
                            description = 'Data geografis';
                    }

                    legendHTML += `
                        <div class="legend-item ${!isVisible ? 'inactive' : ''}">
                            <div class="legend-symbol ${layerType}" style="
                                ${layerType === 'marker' ? `background-color: ${fillColor}; border: 1px solid ${strokeColor};` : ''}
                                ${layerType === 'circle' ? `background-color: ${fillColor}; border-color: ${strokeColor}; opacity: ${opacity};` : ''}
                                ${layerType === 'polyline' ? `background-color: ${strokeColor}; height: ${weight}px;` : ''}
                                ${layerType === 'polygon' ? `background-color: ${fillColor}; border-color: ${strokeColor}; opacity: ${opacity};` : ''}
                            "></div>
                            <div class="legend-text">
                                ${layerName} ${!isVisible ? '<span style="color:#999;">(nonaktif)</span>' : ''}
                                <br>
                                <small style="color: #777;">${description}</small>
                            </div>
                        </div>
                    `;
                }
            });

            legendContent.innerHTML = legendHTML;
        }

        // Modal functions
        function openModal(featureData) {
            displayDetailContent(featureData);
            document.getElementById('detail-modal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('detail-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
            currentFeatureData = null;
        }

        function displayDetailContent(featureData) {
            const detailContent = document.getElementById('detail-content');
            let content = '';

            // KONDISI #1: Untuk data manual
            if (featureData.dataSource === 'manual') {
                const name = featureData.name || 'Tidak ada nama';
                const description = featureData.description || 'Tidak ada deskripsi';
                const photo = featureData.photo || '';

                content +=
                    `<div class="detail-item"><div class="detail-label">Nama:</div><div class="detail-value">${name}</div></div>`;
                content +=
                    `<div class="detail-item"><div class="detail-label">Deskripsi:</div><div class="detail-value">${description || '<i>Tidak ada deskripsi</i>'}</div></div>`;

                // Tampilkan foto dari public/map_images
                if (photo) {
                    // Jika photo sudah berupa URL lengkap, gunakan langsung
                    // Jika tidak, gabungkan dengan path map_images
                    const photoUrl = photo.includes('http') ? photo : `/map_images/${photo.replace(/^.*[\\\/]/, '')}`;

                    content += `<div style="margin-top: 15px;">
                        <div style="font-weight: bold; margin-bottom: 8px; color: #333;">Foto:</div>
                        <div style="text-align: center;">
                            <img src="${photoUrl}" 
                                alt="Foto ${name}" 
                                style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer;"
                                onclick="window.open('${photoUrl}', '_blank')"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none; padding: 20px; background: #f5f5f5; border-radius: 8px; color: #666; text-align: center;">
                                Foto tidak dapat dimuat
                            </div>
                        </div>
                    </div>`;
                } else {
                    content += `<div style="margin-top: 15px;">
                        <div style="font-weight: bold; margin-bottom: 8px; color: #333;">Foto:</div>
                        <div style="padding: 20px; background: #f5f5f5; border-radius: 8px; color: #666; text-align: center;">
                            Tidak ada foto
                        </div>
                    </div>`;
                }

                // KONDISI #2: Untuk data GeoJSON
            } else if (featureData.dataSource === 'geojson') {
                const title = featureData.name || featureData.title || featureData.nama || 'Detail Fitur';
                content +=
                    `<div class="detail-item"><div class="detail-label">Nama:</div><div class="detail-value">${title}</div></div>`;

                if (featureData.description) {
                    content +=
                        `<div class="detail-item"><div class="detail-label">Deskripsi:</div><div class="detail-value">${featureData.description}</div></div>`;
                }

                // Loop melalui properti lain
                Object.entries(featureData).forEach(([key, value]) => {
                    const label = formatLabel(key);
                    if (label && key !== 'name' && key !== 'title' && key !== 'nama' && key !== 'description' &&
                        !key.toLowerCase().includes('photo') && !key.toLowerCase().includes('foto') &&
                        !key.toLowerCase().includes('image') && !key.toLowerCase().includes('gambar') &&
                        key !== 'layer_type' && key !== 'dataSource' && value) {
                        content +=
                            `<div class="detail-item"><div class="detail-label">${label}:</div><div class="detail-value">${value}</div></div>`;
                    }
                });

                // Cari field foto dengan berbagai kemungkinan nama
                const photoUrl = featureData.photo || featureData.foto || featureData.image || featureData.gambar;
                if (photoUrl) {
                    // Jika photoUrl sudah berupa URL lengkap, gunakan langsung
                    // Jika tidak, gabungkan dengan path map_images
                    const fullPhotoUrl = photoUrl.includes('http') ? photoUrl :
                        `/map_images/${photoUrl.replace(/^.*[\\\/]/, '')}`;

                    content += `<div style="margin-top: 15px;">
                        <div style="font-weight: bold; margin-bottom: 8px; color: #333;">Foto:</div>
                        <div style="text-align: center;">
                            <img src="${fullPhotoUrl}" 
                                alt="Foto ${title}" 
                                style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer;"
                                onclick="window.open('${fullPhotoUrl}', '_blank')"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none; padding: 20px; background: #f5f5f5; border-radius: 8px; color: #666; text-align: center;">
                                Foto tidak dapat dimuat
                            </div>
                        </div>
                    </div>`;
                }
            }

            detailContent.innerHTML = content;
        }

        function formatLabel(key) {
            return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function createPopupContent(feature, mapData) {
            const props = feature.properties || {};
            const isGeoJSON = Object.keys(props).length > 0;

            let dataForModal;
            let title;

            if (isGeoJSON) {
                title = props.Name || props.name || props.title || props.nama || 'Informasi';
                dataForModal = {
                    ...props,
                    dataSource: 'geojson'
                };
            } else {
                title = mapData.name || 'Informasi';
                dataForModal = {
                    dataSource: 'manual',
                    name: mapData.name,
                    description: mapData.description,
                    photo: mapData.image_path
                };
            }

            let quickInfoHTML = '';

            if (isGeoJSON) {
                const keys = Object.keys(props).filter(key => key !== 'geometry' && props[key]);
                const topThree = keys.slice(0, 3);

                topThree.forEach(key => {
                    const label = formatLabel(key);
                    quickInfoHTML += `
                        <div class="popup-info-item">
                            <span class="popup-info-label" style="font-weight: bold;">${label}:</span>
                            <span class="popup-info-value">${props[key]}</span>
                        </div>`;
                });
            } else if (dataForModal.description) {
                quickInfoHTML = `
                    <div class="popup-info-item">
                        <span class="popup-info-value">${dataForModal.description.substring(0, 70)}...</span>
                    </div>`;
            }

            const encodedData = encodeURIComponent(JSON.stringify(dataForModal));

            return `
                <div class="popup-title">${title}</div>
                <div class="popup-info">${quickInfoHTML}</div>
                <button class="btn-detail open-detail-btn" data-feature='${encodedData}'>
                    Selengkapnya
                </button>
            `;
        }

        // Close modal when clicking outside
        document.getElementById('detail-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Event listener untuk tombol detail
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('open-detail-btn')) {
                const featureData = JSON.parse(decodeURIComponent(e.target.getAttribute('data-feature')));
                openModal(featureData);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                // Inisialisasi peta
                var map = L.map('map', {
                    preferCanvas: true,
                    zoomControl: true
                }).setView([-2.5, 117], 5);

                // Base maps
                var baseLayers = {
                    "OSM Standard": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 18
                    }),
                    "OSM HOT": L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors, Tiles style by Humanitarian OSM Team',
                        maxZoom: 18
                    }),
                    "OpenTopoMap": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                        attribution: 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap (CC-BY-SA)',
                        maxZoom: 17
                    })
                };

                baseLayers["OSM Standard"].addTo(map);

                setTimeout(() => map.invalidateSize(), 500);
                window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 100));

                // Objek untuk menyimpan layer berdasarkan nama layer
                const layerGroups = {};
                const overlayLayers = {};
                const allBounds = [];
                let loadedCount = 0;
                const totalMaps = mapsData.length;

                // Reset View Control
                L.Control.ResetView = L.Control.extend({
                    onAdd: function(map) {
                        var btn = L.DomUtil.create('button',
                            'leaflet-bar leaflet-control leaflet-control-custom');
                        btn.innerHTML = '🔄 Reset View';
                        btn.style.backgroundColor = 'white';
                        btn.style.padding = '5px 10px';
                        btn.style.cursor = 'pointer';
                        btn.style.border = 'none';
                        btn.style.borderRadius = '4px';
                        btn.onclick = () => fitAllBounds();
                        return btn;
                    }
                });

                L.control.resetView = opts => new L.Control.ResetView(opts);
                L.control.resetView({
                    position: 'topright'
                }).addTo(map);

                function fitAllBounds() {
                    const group = L.featureGroup();
                    allBounds.forEach(bounds => {
                        const center = bounds.getCenter();
                        const isValidIndo = center.lat >= -11 && center.lat <= 6 && center.lng >=
                            95 && center.lng <= 141;

                        if (isValidIndo) {
                            group.addLayer(L.rectangle(bounds, {
                                opacity: 0
                            }));
                        }
                    });

                    if (group.getLayers().length > 0) {
                        map.fitBounds(group.getBounds(), {
                            padding: [30, 30],
                            maxZoom: 12
                        });
                    } else {
                        // fallback ke Indonesia
                        map.setView([-2.5, 117], 5);
                    }
                }

                function createLayerStyle(mapData) {
                    return {
                        color: mapData.stroke_color || '#000000',
                        fillColor: mapData.fill_color || '#ff0000',
                        weight: parseInt(mapData.weight) || 2,
                        opacity: parseFloat(mapData.opacity) || 0.8,
                        fillOpacity: (parseFloat(mapData.opacity) || 0.8) * 0.7
                    };
                }

                function processMapData(mapData) {
                    const style = createLayerStyle(mapData);
                    const layerType = mapData.layer_type || 'marker';
                    const radius = parseFloat(mapData.radius) || 300;
                    const iconUrl = mapData.icon_url || '';
                    const lat = parseFloat(mapData.lat);
                    const lng = parseFloat(mapData.lng);
                    const layerName = mapData.layer_name || `Layer ${mapData.id}`;

                    // Inisialisasi layer group jika belum ada
                    if (!layerGroups[layerName]) {
                        layerGroups[layerName] = L.layerGroup();
                    }

                    // Cek apakah ada data geometry (GeoJSON) dari database
                    if (mapData.geometry && typeof mapData.geometry === 'object') {
                        try {
                            // Jika geometry berupa FeatureCollection
                            let geoData = mapData.geometry;

                            // Jika bukan FeatureCollection, buat sebagai Feature tunggal
                            if (geoData.type !== 'FeatureCollection') {
                                if (geoData.type === 'Feature') {
                                    geoData = {
                                        type: 'FeatureCollection',
                                        features: [geoData]
                                    };
                                } else {
                                    // Jika hanya geometry saja
                                    geoData = {
                                        type: 'FeatureCollection',
                                        features: [{
                                            type: 'Feature',
                                            geometry: geoData,
                                            properties: {}
                                        }]
                                    };
                                }
                            }

                            const layer = L.geoJSON(geoData, {
                                style: function(feature) {
                                    return style;
                                },
                                onEachFeature: function(feature, layer) {
                                    const popupContent = createPopupContent(feature, mapData);
                                    layer.bindPopup(popupContent, {
                                        maxWidth: 300,
                                        className: 'custom-popup'
                                    });
                                },
                                pointToLayer: function(feature, latlng) {
                                    if (layerType === 'circle') {
                                        return L.circle(latlng, {
                                            radius,
                                            ...style
                                        });
                                    } else if (layerType === 'marker' && iconUrl) {
                                        const customIcon = L.icon({
                                            iconUrl,
                                            iconSize: [32, 32],
                                            iconAnchor: [16, 16],
                                            popupAnchor: [0, -16]
                                        });
                                        return L.marker(latlng, {
                                            icon: customIcon
                                        });
                                    } else {
                                        return L.circleMarker(latlng, {
                                            radius: 8,
                                            ...style
                                        });
                                    }
                                }
                            });

                            // Tambahkan ke layer group
                            layerGroups[layerName].addLayer(layer);

                            // Tambahkan bounds
                            if (layer.getBounds && layer.getBounds().isValid && layer.getBounds()
                                .isValid()) {
                                allBounds.push(layer.getBounds());
                            }
                        } catch (error) {
                            console.error('Error processing geometry data for map ID:', mapData.id, error);
                            // Fallback ke manual layer jika ada error
                            createManualLayer(mapData, layerName, lat, lng, style, layerType, radius,
                                iconUrl);
                        }
                    } else {
                        // Fallback ke manual layer jika tidak ada geometry
                        createManualLayer(mapData, layerName, lat, lng, style, layerType, radius, iconUrl);
                    }

                    loadedCount++;
                    if (loadedCount === totalMaps) {
                        // Tambahkan layer groups ke overlay layers
                        Object.keys(layerGroups).forEach(layerName => {
                            overlayLayers[layerName] = layerGroups[layerName];
                            layerGroups[layerName].addTo(map);
                        });

                        setTimeout(() => {
                            const layerControl = L.control.layers(baseLayers, overlayLayers, {
                                collapsed: false
                            }).addTo(map);
                            setTimeout(() => fitAllBounds(), 300);
                        }, 100);
                    }
                }

                function createManualLayer(mapData, layerName, lat, lng, style, layerType, radius,
                    iconUrl) {
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const latlng = L.latLng(lat, lng);
                        let layer;

                        if (layerType === 'circle') {
                            layer = L.circle(latlng, {
                                radius,
                                ...style
                            });
                        } else if (layerType === 'marker' && iconUrl) {
                            const customIcon = L.icon({
                                iconUrl,
                                iconSize: [32, 32],
                                iconAnchor: [16, 16],
                                popupAnchor: [0, -16]
                            });
                            layer = L.marker(latlng, {
                                icon: customIcon
                            });
                        } else {
                            layer = L.circleMarker(latlng, {
                                radius: 8,
                                ...style
                            });
                        }

                        const popupContent = createPopupContent({
                            properties: {}
                        }, mapData);
                        layer.bindPopup(popupContent, {
                            maxWidth: 300,
                            className: 'custom-popup'
                        });

                        // Inisialisasi layer group jika belum ada
                        if (!layerGroups[layerName]) {
                            layerGroups[layerName] = L.layerGroup();
                        }

                        layerGroups[layerName].addLayer(layer);
                        allBounds.push(L.latLngBounds([latlng]));
                    }
                }

                // Process semua peta
                mapsData.forEach(mapData => {
                    processMapData(mapData);
                });

                // Event listener untuk toggle layer group di sidebar
                document.querySelectorAll('.layer-group-toggle').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const layerName = this.getAttribute('data-layer-name');
                        if (this.checked) {
                            if (layerGroups[layerName]) {
                                layerGroups[layerName].addTo(map);
                            }
                        } else {
                            if (layerGroups[layerName]) {
                                map.removeLayer(layerGroups[layerName]);
                            }
                        }
                        updateLegend();
                    });
                });

                // Sinkronisasi antara layer control dan checkbox sidebar
                map.on('overlayadd', function(e) {
                    const layerName = e.name;
                    const checkbox = document.querySelector(`[data-layer-name="${layerName}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                    updateLegend();
                });

                map.on('overlayremove', function(e) {
                    const layerName = e.name;
                    const checkbox = document.querySelector(`[data-layer-name="${layerName}"]`);
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                    updateLegend();
                });

            }, 300);
        });
    </script>
@endsection
