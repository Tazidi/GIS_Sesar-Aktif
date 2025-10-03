@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="{{ asset('css/map.css') }}">

    @if(request()->boolean('embed'))
        <style>
            header, nav, footer { display: none !important; }
            body { margin: 0; padding: 0; }
            .container { padding-top: 0; }
        </style>
    @endif
@endsection

@section('content')
    <div class="container">
        @if(!request()->boolean('embed'))
            <div class="text-center mb-8 pt-2">
                <h1 style="font-size: 28px; font-weight: 700; color: #333; margin-bottom: 8px;">
                    Halaman Peta SISIRAJA
                </h1>
                <p style="color: #666; font-size: 16px;">
                    Koleksi peta dan visualisasi data geografis SISIRAJA
                </p>
            </div>
        @endif

        <!-- Data JSON untuk JavaScript -->
        @php
    // Membuat kembali variabel untuk daftar layer unik beserta stylenya
    $allUniqueLayersWithStyle = collect();

    foreach ($maps as $map) {
        foreach ($map->layers as $layer) {
            if (!$allUniqueLayersWithStyle->has($layer->id)) {
                $allUniqueLayersWithStyle->put($layer->id, [
                    'id' => $layer->id,
                    'name' => $layer->nama_layer ?? 'Layer Tanpa Nama',
                    'type' => $layer->pivot->layer_type ?? 'marker',
                    'stroke_color' => $layer->pivot->stroke_color ?? '',
                    'fill_color' => $layer->pivot->fill_color ?? '',
                    'opacity' => $layer->pivot->opacity ?? null,
                    'weight' => $layer->pivot->weight ?? null,
                    'radius' => $layer->pivot->radius ?? null,
                    'icon_url' => $layer->pivot->icon_url ?? ''
                ]);
            }
        }
    }
@endphp

<script type="application/json" id="maps-data">
    {
        "maps": [
            @foreach ($maps as $map)
                {
                    "id": {{ $map->id }},
                    "name": {!! json_encode($map->name) !!},
                    "description": {!! json_encode($map->description) !!},
                    "default_stroke_color": "{{ $map->stroke_color ?? '#000000' }}",
                    "default_fill_color": "{{ $map->fill_color ?? '#ff0000' }}",
                    "default_opacity": {{ $map->opacity ?? 0.8 }},
                    "default_weight": {{ $map->weight ?? 2 }},
                    "default_radius": {{ $map->radius ?? 300 }},
                    "default_icon_url": "{{ $map->icon_url ?? '' }}"
                }@if(!$loop->last),@endif
            @endforeach
        ],
        "features": [
            @foreach ($allFeatures as $feature)
                {
                    "type": "Feature",
                    "geometry": {!! $feature['geometry'] ? json_encode($feature['geometry']) : 'null' !!},
                    "properties": {!! $feature['properties'] ? json_encode($feature['properties']) : '{}' !!},
                    "image_path": "{{ $feature['image_path'] }}",
                    "caption": {!! json_encode($feature['caption'] ?? '') !!},
                    "technical_info": {!! $feature['technical_info'] ? json_encode($feature['technical_info']) : '{}' !!},
                    "layer_ids": {!! json_encode($feature['layer_ids']) !!}
                }@if(!$loop->last),@endif
            @endforeach
        ],
        "layers": [
            @foreach ($allUniqueLayersWithStyle as $layer)
                {
                    "id": {{ $layer['id'] }},
                    "name": {!! json_encode($layer['name']) !!},
                    "type": "{{ $layer['type'] }}",
                    "stroke_color": "{{ $layer['stroke_color'] }}",
                    "fill_color": "{{ $layer['fill_color'] }}",
                    "opacity": {{ $layer['opacity'] ?? 'null' }},
                    "weight": {{ $layer['weight'] ?? 'null' }},
                    "radius": {{ $layer['radius'] ?? 'null' }},
                    "icon_url": "{{ $layer['icon_url'] }}"
                }@if(!$loop->last),@endif
            @endforeach
        ]
    }
</script>

        @php
            $uniqueLayers = collect();
            foreach ($maps as $map) {
                foreach ($map->layers as $layer) {
                    $uniqueLayers->push($layer);
                }
            }
            $uniqueLayers = $uniqueLayers->unique('nama_layer');

            $uniqueLegendItems = [];
            foreach($uniqueLayers as $layer) {
                $layerName = $layer->nama_layer;
                $pivot = $layer->pivot;
                $uniqueLegendItems[$layerName] = [
                    'layer_type' => $pivot->layer_type ?? 'marker',
                    'fill_color' => $pivot->fill_color ?? '#ff0000',
                    'stroke_color' => $pivot->stroke_color ?? '#000000',
                    'opacity' => $pivot->opacity ?? 0.8,
                    'weight' => $pivot->weight ?? 2,
                    'icon_url' => $pivot->icon_url ?? '',
                ];
            }
        @endphp

        <div class="layer-controls">
            <h3>Pilih Layer</h3>
            @foreach ($uniqueLayers as $layer)
                <div class="layer-item">
                    <label>
                        <input type="checkbox" class="layer-group-toggle" data-layer-name="{{ $layer->nama_layer }}" checked>
                        {{ $layer->nama_layer }}
                    </label>
                </div>
            @endforeach
            <div class="layer-item">
                <label>
                    <input type="checkbox" class="layer-group-toggle" data-layer-name="BMKG: 15 Gempa" checked>
                    BMKG: 15 Gempa
                </label>
            </div>
        </div>

        <div class="map-container">
            <div id="map"></div>

            <div class="legend-box">
                <div class="legend-title">Keterangan Peta</div>
                <div id="legend-content">
                    @foreach ($uniqueLegendItems as $layerName => $item)
                        <div class="legend-item" data-legend-layer="{{ $layerName }}">
                            <div class="legend-symbol {{ $item['layer_type'] }}"
                                style="
                                    @if ($item['layer_type'] == 'marker') background-color: {{ $item['fill_color'] }}; border-color: {{ $item['stroke_color'] }};
                                    @elseif ($item['layer_type'] == 'circle') border-color: {{ $item['stroke_color'] }}; background-color: {{ $item['fill_color'] }}; opacity: {{ $item['opacity'] }}; border-width: {{ $item['weight'] }}px;
                                    @elseif ($item['layer_type'] == 'polyline') background-color: {{ $item['stroke_color'] }}; height: {{ min($item['weight'], 18) }}px; border-width: 0;
                                    @elseif ($item['layer_type'] == 'polygon') background-color: {{ $item['fill_color'] }}; border-color: {{ $item['stroke_color'] }}; opacity: {{ $item['opacity'] }}; border-width: {{ $item['weight'] }}px;
                                    @endif
                                ">
                                @if ($item['layer_type'] == 'marker' && $item['icon_url'])
                                    <img src="{{ $item['icon_url'] }}" style="width: 16px; height: 16px; border-radius: 50%;" alt="icon">
                                @endif
                            </div>
                            <div class="legend-text">
                                {{ $layerName }}
                                <br>
                                <small style="color: #777;">
                                    @if ($item['layer_type'] == 'marker') Penanda Lokasi
                                    @elseif ($item['layer_type'] == 'circle') Lingkaran
                                    @elseif ($item['layer_type'] == 'polyline') Garis/Jalur
                                    @elseif ($item['layer_type'] == 'polygon') Area/Wilayah
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endforeach
                    <div class="legend-item" data-legend-layer="BMKG: 15 Gempa">
                        <div class="legend-symbol marker">
                            <img src="{{ asset('bmkg/earthquake.png') }}" style="width: 16px; height: 16px;" alt="icon">
                        </div>
                        <div class="legend-text">
                            BMKG: 15 Gempa
                            <br>
                            <small style="color: #777;">Info Gempa Terkini</small>
                        </div>
                    </div>
                </div>
            </div>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const BMKG_ICON_URL = "{{ asset('bmkg/earthquake.png') }}";
        const mapsData = JSON.parse(document.getElementById('maps-data').textContent);
        
        
        let map;
        const layerGroups = {}; 
        const allBounds = [];

        document.addEventListener('DOMContentLoaded', function() {
            map = L.map('map', { 
                preferCanvas: true, 
                zoomControl: true 
            }).setView([-2.5, 117], 5);

            const baseLayers = {
                "Google Maps": L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'], attribution: '&copy; Google' }),
                "Google Satellite": L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'], attribution: '&copy; Google' }),
                "OpenTopoMap": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { maxZoom: 17, attribution: '© OpenTopoMap' })
            };
            baseLayers["Google Satellite"].addTo(map);

            const layerControl = L.control.layers(baseLayers, {}, { 
                collapsed: true, 
                position: 'topright' 
            }).addTo(map);

            const featuresByLayerName = {};
            
            {{-- KODE PENGGANTI --}}
            mapsData.features.forEach(featureData => {
                (featureData.layer_ids || []).forEach(layerId => {
                    const layerInfo = mapsData.layers.find(l => l.id === layerId);
                    if (layerInfo) {
                        const layerName = layerInfo.name;
                        if (!featuresByLayerName[layerName]) {
                            featuresByLayerName[layerName] = {
                                features: [],
                                layerInfo: layerInfo,
                                mapData: mapsData.maps[0] 
                            };
                        }

                        // Gunakan geometry utama dari featureData.geometry langsung
                        const geometry = featureData.geometry;
                        const properties = { ...featureData.properties }; // Salin properti agar bisa diubah

                        if (geometry && geometry.type && geometry.coordinates) {
                            // Pastikan koordinat jadi angka (Leaflet butuh float, bukan string)
                            const cleanGeometry = JSON.parse(JSON.stringify(geometry, (key, value) => {
                                if (Array.isArray(value)) {
                                    return value.map(v => (typeof v === "string" ? parseFloat(v) : v));
                                }
                                return value;
                            }));

                            featuresByLayerName[layerName].features.push({
                                type: 'Feature',
                                geometry: cleanGeometry,
                                properties: properties,
                                image_path: featureData.image_path,
                                caption: featureData.caption,
                                technical_info: featureData.technical_info,
                                layer_ids: featureData.layer_ids
                            });
                        }
                    }
                });
            });
            
            mapsData.layers.forEach(layerInfo => {
                const layerName = layerInfo.name;
                const groupData = featuresByLayerName[layerName];

                
                const features = groupData ? groupData.features : [];
                const mapData = groupData ? groupData.mapData : mapsData.maps[0];

                const geoJsonLayer = L.geoJSON(features, {
                    style: function(feature) {
                        let individualStyle = {};
                        try {
                            const techInfo = typeof feature.technical_info === "string" 
                                ? JSON.parse(feature.technical_info || "{}") 
                                : (feature.technical_info || {});
                            individualStyle = {
                                color: techInfo.stroke_color || layerInfo.stroke_color || mapData.default_stroke_color,
                                fillColor: techInfo.fill_color || layerInfo.fill_color || mapData.default_fill_color,
                                weight: techInfo.weight || layerInfo.weight || mapData.default_weight,
                                opacity: techInfo.opacity ?? layerInfo.opacity ?? mapData.default_opacity,
                                fillOpacity: techInfo.fill_opacity ?? (techInfo.opacity || mapData.default_opacity) * 0.7
                            };
                        } catch (e) { }
                        return { ...createLayerStyle(layerInfo, mapData), ...individualStyle };
                    },
                pointToLayer: (feature, latlng) => {
                    let techInfo = {};
                    try {
                        techInfo = typeof feature.technical_info === "string" 
                            ? JSON.parse(feature.technical_info || "{}") 
                            : (feature.technical_info || {});
                    } catch (e) { }

                    let layerType = (techInfo.geometry_type || feature.properties?.geometry_type || layerInfo.type || 'marker').toLowerCase();

                    if (layerType === 'marker' && (techInfo.radius || layerInfo.radius)) {
                        layerType = 'circle';
                    }

                    const baseStyle = createLayerStyle(layerInfo, mapData);
                    const featureStyle = {
                        color: techInfo.stroke_color || baseStyle.color,
                        fillColor: techInfo.fill_color || baseStyle.fillColor,
                        weight: techInfo.weight || baseStyle.weight,
                        opacity: techInfo.opacity ?? baseStyle.opacity,
                        fillOpacity: techInfo.fill_opacity ?? ((techInfo.opacity ?? baseStyle.opacity) * 0.7)
                    };

                    if (layerType === 'circle') {
                        const radius = techInfo.radius || layerInfo.radius || mapData.default_radius || 300;
                        return L.circle(latlng, { ...featureStyle, radius });
                    }

                    if (layerType === 'circlemarker') {
                        const radius = techInfo.point_radius || 6;
                        return L.circleMarker(latlng, { ...featureStyle, radius });
                    }

                    const finalIconUrl = techInfo.icon_url || layerInfo.icon_url || mapData.default_icon_url || '';
                    if (layerType === 'marker' && finalIconUrl) {
                        return L.marker(latlng, {
                            icon: L.icon({
                                iconUrl: finalIconUrl,
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34]
                            })
                        });
                    }

                    return L.marker(latlng);
                },

                onEachFeature: function(feature, layer) {
                        // Memasang popup ke setiap fitur (garis, lingkaran, poligon, dll)
                        layer.bindPopup(createPopupContent(feature, mapData));
                    }
                });

                layerGroups[layerName] = geoJsonLayer;
                if (features.length > 0) {
                    allBounds.push(geoJsonLayer);
                }
            });

            layerGroups['BMKG: 15 Gempa'] = L.layerGroup();
            
            fetchBMKGData().then(() => {
                initLayers(layerControl);
                fitAllBounds();
                setupEventListeners();
            });
        });

        function initLayers(layerControl) {
            Object.entries(layerGroups).forEach(([layerName, group]) => {
                group.addTo(map);
                layerControl.addOverlay(group, layerName);
            });
            updateUI();
        }

        function fetchBMKGData() {
            return fetch('https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.json')
                .then(res => res.json())
                .then(data => {
                    const gempaList = data?.Infogempa?.gempa?.slice(0, 15) || [];
                    const gempaIcon = L.icon({ iconUrl: BMKG_ICON_URL, iconSize: [40, 40], iconAnchor: [20, 40], popupAnchor: [0, -38] });
                    gempaList.forEach(gempa => {
                        const [lat, lng] = gempa.Coordinates.split(',').map(s => parseFloat(s.trim()));
                        if (!isNaN(lat) && !isNaN(lng)) {
                            L.marker([lat, lng], { icon: gempaIcon }).bindPopup(`<b>Gempa:</b> ${gempa.Wilayah}`).addTo(layerGroups['BMKG: 15 Gempa']);
                        }
                    });
                })
                .catch(err => console.error("Gagal mengambil data BMKG:", err));
        }

        function updateUI() {
            document.querySelectorAll('.legend-item').forEach(legend => {
                const layerName = legend.getAttribute('data-legend-layer');
                if (layerGroups[layerName]) {
                    const isVisible = map.hasLayer(layerGroups[layerName]);
                    legend.classList.toggle('inactive', !isVisible);
                }
            });
            document.querySelectorAll('.layer-group-toggle').forEach(checkbox => {
                 const layerName = checkbox.getAttribute('data-layer-name');
                if (layerGroups[layerName]) {
                    checkbox.checked = map.hasLayer(layerGroups[layerName]);
                }
            });
        }
        
        function setupEventListeners() {
            map.on('overlayadd overlayremove', updateUI);
            
            document.querySelectorAll('.layer-group-toggle').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const layerName = this.getAttribute('data-layer-name');
                    const targetGroup = layerGroups[layerName];
                    if (targetGroup) {
                        this.checked ? map.addLayer(targetGroup) : map.removeLayer(targetGroup);
                    }
                });
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('open-detail-btn')) {
                    const featureData = JSON.parse(decodeURIComponent(e.target.getAttribute('data-feature')));
                    openModal(featureData);
                }
            });
        }
        
        function fitAllBounds() {
            // Pastikan ada layer yang akan diukur
            if (allBounds.length > 0) {
                // Buat grup dari semua layer yang memiliki data
                const combinedGroup = L.featureGroup(allBounds);
                
                // Ambil batas (bounds) dari grup tersebut
                const bounds = combinedGroup.getBounds();

                // **Pemeriksaan Kunci:** Hanya panggil fitBounds jika bounds-nya valid
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 12 });
                } else {
                    // Beri peringatan di console jika bounds tidak valid, ini membantu debugging
                    console.warn("Gagal menyesuaikan peta karena batas area tidak valid. Ini bisa terjadi jika layer tidak memiliki geometri yang valid.");
                }
            }
        }
        
        function createLayerStyle(layerInfo, mapData) {
             const opacity = parseFloat(layerInfo.opacity);
             return {
                 color: layerInfo.stroke_color || mapData.default_stroke_color || '#000000',
                 fillColor: layerInfo.fill_color || mapData.default_fill_color || '#ff0000',
                 weight: parseInt(layerInfo.weight) || mapData.default_weight || 2,
                 opacity: isNaN(opacity) ? (mapData.default_opacity || 0.8) : opacity,
                 fillOpacity: (isNaN(opacity) ? (mapData.default_opacity || 0.8) : opacity) * 0.7
             };
        }

        function createPopupContent(feature, mapData) {
            const props = feature.properties || {};
            const isGeoJSON = Object.keys(props).length > 0;
            let dataForModal;
            let title;
            let quickInfoHTML = '';

            if (isGeoJSON) {
                title = props.Name || props.name || props.title || props.nama || 'Informasi';
                dataForModal = { ...props, dataSource: 'geojson', ...feature };
                
                const keys = Object.keys(props).filter(key => key !== 'geometry' && key !== 'timestamp' && props[key] && props[key].toString().trim() !== '');
                const topThree = keys.slice(0, 3);
                topThree.forEach(key => {
                    quickInfoHTML += `<div class="popup-info-item"><b>${formatLabel(key)}:</b> ${props[key]}</div>`;
                });

            } else {
                title = mapData.name || 'Informasi';
                dataForModal = { dataSource: 'manual', name: mapData.name, description: mapData.description, photo: mapData.image_path, caption: mapData.caption || '' };
                if (dataForModal.description) {
                    quickInfoHTML = `<div class="popup-info-item">${dataForModal.description.substring(0, 70)}...</div>`;
                }
            }

            const encodedData = encodeURIComponent(JSON.stringify(dataForModal));
            return `
                <div class="popup-header">${title}</div>
                <div class="popup-info">${quickInfoHTML || ''}</div>
                <button class="btn-detail open-detail-btn" data-feature='${encodedData}'>
                    Selengkapnya
                </button>
            `;
        }
        
        function openModal(featureData) {
            displayDetailContent(featureData);
            document.getElementById('detail-modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('detail-modal').style.display = 'none';
        }

        function formatLabel(key) {
            return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function displayDetailContent(featureData) {
            const detailContent = document.getElementById('detail-content');
            const modalTitleElement = document.querySelector('#detail-modal .modal-title');
            let content = '';
            let modalTitle = 'Detail Informasi';

            if (featureData.dataSource === 'manual') {
                modalTitle = featureData.name || 'Tidak ada nama';
                content += `<div class="detail-item"><div class="detail-label">Deskripsi</div><div class="detail-value">${featureData.description || '<i>Tidak ada</i>'}</div></div>`;
                if (featureData.photo) {
                    content += `<div class="detail-item"><div class="detail-label">Foto</div><div class="detail-value"><img src="${featureData.photo}" alt="Foto" style="max-width: 100%; border-radius: 8px;"></div></div>`;
                }
            } else if (featureData.dataSource === 'geojson') {
                modalTitle = featureData.properties.Name || featureData.properties.name || featureData.properties.title || featureData.properties.nama || 'Detail Fitur';
                
                const properties = { ...featureData.properties };
                
                Object.entries(properties).forEach(([key, value]) => {
                    if (value) {
                        content += `<div class="detail-item"><div class="detail-label">${formatLabel(key)}</div><div class="detail-value">${value}</div></div>`;
                    }
                });

                // **PERBAIKAN: Handle technical_info sebagai object atau string**
                let techInfo = featureData.technical_info;
                let isValidTechInfo = false;

                if (techInfo) {
                    if (typeof techInfo === 'string') {
                        // Jika string, check trim dan parse
                        if (techInfo.trim() !== '' && techInfo !== '{}' && techInfo !== 'null' && techInfo !== 'undefined') {
                            isValidTechInfo = true;
                        }
                    } else if (typeof techInfo === 'object' && techInfo !== null) {
                        // Jika object, check apakah non-empty (punya entries valid)
                        const entries = Object.entries(techInfo).filter(([k, v]) => v !== null && v !== '' && v !== 'null' && v !== undefined && k.toLowerCase() !== 'icon_url');
                        if (entries.length > 0) {
                            isValidTechInfo = true;
                        }
                    }
                }

                if (isValidTechInfo) {
                    try {
                        // Parse jika masih string; jika sudah object, gunakan langsung
                        if (typeof techInfo === 'string') {
                            techInfo = JSON.parse(techInfo);
                        }
                        
                        if (techInfo && typeof techInfo === 'object') {
                            const validEntries = Object.entries(techInfo).filter(([k, v]) => v !== null && v !== '' && v !== 'null' && v !== undefined && k.toLowerCase() !== 'icon_url');
                            if (validEntries.length > 0) {
                                let list = '<ul>';
                                validEntries.forEach(([k, v]) => {
                                    list += `<li><strong>${formatLabel(k)}:</strong> ${v}</li>`;
                                });
                                list += '</ul>';
                                content += `<div class="detail-item"><div class="detail-label">Info Teknis</div><div class="detail-value">${list}</div></div>`;
                            }
                        }
                    } catch (e) {
                        // Fallback: Tampilkan sebagai string jika parse gagal
                        console.warn('Gagal parse technical_info:', e, techInfo);
                        if (typeof featureData.technical_info === 'string' && featureData.technical_info !== 'null') {
                            content += `<div class="detail-item"><div class="detail-label">Info Teknis</div><div class="detail-value">${featureData.technical_info}</div></div>`;
                        }
                    }
                }

                const image_path = featureData.image_path || featureData.feature_image_path;
                if (image_path) {
                    content += `<div class="detail-item"><div class="detail-label">Foto</div><div class="detail-value"><img src="${image_path}" style="width:100%; border-radius:8px;"></div></div>`;
                } else {
                    content += `<div class="detail-item"><div class="detail-label">Foto</div><div class="detail-value"><i>Tidak ada foto</i></div></div>`;
                }

                if (featureData.caption) {
                    content += `<div class="detail-item"><div class="detail-label">Caption</div><div class="detail-value">${featureData.caption}</div></div>`;
                }
            }
            
            if (modalTitleElement) modalTitleElement.textContent = modalTitle;
            detailContent.innerHTML = content || '<p>Tidak ada detail untuk ditampilkan.</p>';
        }
    </script>
@endsection

