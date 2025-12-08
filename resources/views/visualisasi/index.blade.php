@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="{{ asset('css/map.css') }}">

    @if (request()->boolean('embed'))
        <style>
            header,
            nav,
            footer {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .container {
                padding-top: 0;
            }
        </style>
    @endif
@endsection

@section('content')
    <div class="container">
        @if (!request()->boolean('embed'))
            <div class="text-center mb-8 pt-2">
                <h1 style="font-size: 28px; font-weight: 700; color: #333; margin-bottom: 8px;">
                    Halaman Peta SISIRAJA
                </h1>
                <p style="color: #666; font-size: 16px;">
                    Koleksi peta dan visualisasi data geografis SISIRAJA
                </p>
            </div>
        @endif

        @php
            // LOGIKA FINAL YANG LENGKAP DAN BENAR
            $legendItems = collect();
            
            foreach ($maps as $map) {
                foreach ($map->layers as $layer) {
                    $uniqueKey = ($layer->nama_layer ?? 'Tanpa Nama') . '-' . ($layer->pivot->layer_type ?? 'marker');
            
                    if (!$legendItems->has($uniqueKey)) {
                        $legendItems->put($uniqueKey, [
                            'id' => $layer->id, // Penting untuk JavaScript
                            'name' => $layer->nama_layer ?? 'Layer Tanpa Nama',
                            'type' => $layer->pivot->layer_type ?? 'marker',
                            'stroke_color' => $layer->pivot->stroke_color,
                            'fill_color' => $layer->pivot->fill_color,
                            'weight' => $layer->pivot->weight,
                            'opacity' => $layer->pivot->opacity,
                            'radius' => $layer->pivot->radius, // Penting untuk JavaScript
                            'icon_url' => $layer->pivot->icon_url,
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
                    }@if (!$loop->last),@endif
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
                    }@if (!$loop->last),@endif
                @endforeach
            ],
            "layers": [
                @foreach ($legendItems as $item)
                    {
                        "id": {{ $item['id'] }},
                        "name": {!! json_encode($item['name']) !!},
                        "type": "{{ $item['type'] }}",
                        "stroke_color": "{{ $item['stroke_color'] }}",
                        "fill_color": "{{ $item['fill_color'] }}",
                        "opacity": {{ $item['opacity'] ?? 'null' }},
                        "weight": {{ $item['weight'] ?? 'null' }},
                        "radius": {{ $item['radius'] ?? 'null' }},
                        "icon_url": "{{ $item['icon_url'] }}"
                    }@if (!$loop->last),@endif
                @endforeach
            ]
        }
        </script>

        <div class="layer-controls">
            <h3>Pilih Layer</h3>
            @foreach ($legendItems as $item)
                <div class="layer-item">
                    <label>
                        <input type="checkbox" class="layer-group-toggle" data-layer-name="{{ $item['name'] }}" checked>
                        {{ $item['name'] }}
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
                    @foreach ($legendItems as $item)
                        <div class="legend-item" data-legend-layer="{{ $item['name'] }}">
                            <div class="legend-symbol {{ $item['type'] }}"
                                style="
                                     @if ($item['type'] == 'marker') background-color: {{ $item['fill_color'] ?: '#ff0000' }}; border-color: {{ $item['stroke_color'] ?: '#000000' }};
                                     @elseif ($item['type'] == 'circle') border-color: {{ $item['stroke_color'] ?: '#000000' }}; background-color: {{ $item['fill_color'] ?: '#ff0000' }}; opacity: {{ $item['opacity'] ?? 0.8 }}; border-width: {{ $item['weight'] ?? 2 }}px;
                                     @elseif ($item['type'] == 'polyline') background-color: {{ $item['stroke_color'] ?: '#000000' }}; height: {{ min($item['weight'] ?? 2, 18) }}px; border-width: 0;
                                     @elseif ($item['type'] == 'polygon') background-color: {{ $item['fill_color'] ?: '#ff0000' }}; border-color: {{ $item['stroke_color'] ?: '#000000' }}; opacity: {{ $item['opacity'] ?? 0.8 }}; border-width: {{ $item['weight'] ?? 2 }}px;
                                     @endif
                                 ">
                                @if ($item['type'] == 'marker' && $item['icon_url'])
                                    <img src="{{ $item['icon_url'] }}"
                                        style="width: 16px; height: 16px; border-radius: 50%;" alt="icon">
                                @endif
                            </div>
                            <div class="legend-text">
                                {{ $item['name'] }}
                                <br>
                                <small style="color: #777;">
                                    @if ($item['type'] == 'marker') Penanda Lokasi
                                    @elseif ($item['type'] == 'circle') Lingkaran
                                    @elseif ($item['type'] == 'polyline') Garis/Jalur
                                    @elseif ($item['type'] == 'polygon') Area/Wilayah
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endforeach
                    <div class="legend-item" data-legend-layer="BMKG: 15 Gempa">
                        <div class="legend-symbol marker">
                            <img src="{{ asset('bmkg/earthquake.png') }}" style="width: 16px; height: 16px;"
                                alt="icon">
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

                        const geometry = featureData.geometry;
                        const properties = { ...featureData.properties };

                        if (geometry && geometry.type && geometry.coordinates) {
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
                        if (feature.geometry.type === "Point") {
                            return {}; // Points are handled by pointToLayer
                        }

                        const props = feature.properties || {};
                        const baseStyle = createLayerStyle(layerInfo, mapData);

                        return {
                            color: props.stroke_color || baseStyle.color,
                            fillColor: props.fill_color || baseStyle.fillColor,
                            weight: props.weight || baseStyle.weight,
                            opacity: props.opacity ?? baseStyle.opacity,
                            fillOpacity: props.fill_opacity ?? props.opacity ?? baseStyle.fillOpacity
                        };
                    },

                    pointToLayer: (feature, latlng) => {
                        // 1. Setup Variabel Dasar
                        const props = feature.properties || {};
                        const layerInfo = mapsData.layers.find(l => feature.layer_ids.includes(l.id)) || {};
                        const mapData = mapsData.maps[0];

                        // 2. Parse technical_info (PENTING: Cek ini paling awal)
                        let techInfo = feature.technical_info || {};
                        if (typeof techInfo === 'string') {
                            try {
                                techInfo = JSON.parse(techInfo);
                            } catch (e) {
                                techInfo = {}; 
                            }
                        }

                        // 3. Tentukan Tipe Geometri TERLEBIH DAHULU
                        // Prioritas: technical_info > properties > layer setting > default 'marker'
                        let layerType = (techInfo.geometry_type || props.geometry_type || layerInfo.type || 'marker').toLowerCase();

                        // 4. Siapkan Style
                        const baseStyle = createLayerStyle(layerInfo, mapData);
                        const featureStyle = {
                            color: props.stroke_color || baseStyle.color,
                            fillColor: props.fill_color || baseStyle.fillColor,
                            weight: props.weight || baseStyle.weight,
                            opacity: props.opacity ?? baseStyle.opacity,
                            fillOpacity: props.fill_opacity ?? props.opacity ?? baseStyle.fillOpacity
                        };

                        // 5. EKSEKUSI CIRCLE (Sebelum cek icon)
                        // Jika tipe adalah circle, LANGSUNG return circle, jangan cek icon url
                        if (layerType === 'circle') {
                            const radius = techInfo.radius || props.radius || layerInfo.radius || mapData.default_radius || 300;
                            return L.circle(latlng, { ...featureStyle, radius: parseFloat(radius) });
                        }

                        if (layerType === 'circlemarker') {
                            const radius = techInfo.point_radius || props.point_radius || 6;
                            return L.circleMarker(latlng, { ...featureStyle, radius });
                        }

                        // 6. EKSEKUSI MARKER (Baru cek icon di sini)
                        // Jika bukan circle, baru kita lihat apakah ada icon custom
                        const finalIconUrl = props.icon_url || layerInfo.icon_url || mapData.default_icon_url || '';
                        
                        if (finalIconUrl) {
                            return L.marker(latlng, {
                                icon: L.icon({
                                    iconUrl: finalIconUrl,
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                    popupAnchor: [1, -34]
                                })
                            });
                        }

                        // Fallback ke marker standar Leaflet (biru)
                        return L.marker(latlng);
                    },

                    onEachFeature: function(feature, layer) {
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
            if (allBounds.length > 0) {
                const combinedGroup = L.featureGroup(allBounds);
                const bounds = combinedGroup.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 12 });
                } else {
                    console.warn("Gagal menyesuaikan peta karena batas area tidak valid.");
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
            const geomType = feature.geometry?.type || '';
            const coords = feature.geometry?.coordinates || null;

            const latlng = extractLatLng(geomType, coords);
            const title = props.Name || props.name || props.title || props.nama || 'Informasi';

            const payload = {
                dataSource: 'geojson',
                geometryType: geomType,
                latlng: latlng,
                radius: props.radius || feature.radius || null,
                properties: props,
                technical_info: feature.technical_info,
                image_path: feature.image_path,
                caption: feature.caption
            };

            const encodedData = encodeURIComponent(JSON.stringify(payload));

            return `
                <div class="popup-header">${title}</div>
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

        function extractLatLng(geometry) {
            if (!geometry || !geometry.type || !geometry.coordinates) {
                return null;
            }

            const type = geometry.type;
            const coords = geometry.coordinates;

            // --- POINT ---
            if (type === "Point") {
                return {
                    lat: parseFloat(coords[1]),
                    lng: parseFloat(coords[0])
                };
            }

            // --- LINESTRING: [[lng, lat, z], [lng, lat, z], ...] ---
            if (type === "LineString") {
                if (coords.length > 0) {
                    return {
                        lat: parseFloat(coords[0][1]),
                        lng: parseFloat(coords[0][0])
                    };
                }
            }

            // --- MULTILINESTRING: [[[lng, lat, z], ...], [...]] ---
            if (type === "MultiLineString") {
                if (coords.length > 0 && coords[0].length > 0) {
                    return {
                        lat: parseFloat(coords[0][0][1]),
                        lng: parseFloat(coords[0][0][0])
                    };
                }
            }

            // --- POLYGON: [[[lng, lat, z], ...]] ---
            if (type === "Polygon") {
                if (coords.length > 0 && coords[0].length > 0) {
                    return {
                        lat: parseFloat(coords[0][0][1]),
                        lng: parseFloat(coords[0][0][0])
                    };
                }
            }

            // --- MULTIPOLYGON: [[[[lng, lat, z], ...]]]
            if (type === "MultiPolygon") {
                if (coords.length > 0 && coords[0].length > 0 && coords[0][0].length > 0) {
                    return {
                        lat: parseFloat(coords[0][0][0][1]),
                        lng: parseFloat(coords[0][0][0][0])
                    };
                }
            }

            return null;
        }

        function displayDetailContent(featureData) {
            const detailContent = document.getElementById('detail-content');
            const modalTitleElement = document.querySelector('#detail-modal .modal-title');

            const props = featureData.properties || {};
            const geomType = (featureData.geometryType || "").toLowerCase();

            let content = "";
            let modalTitle = props.Name || props.name || props.title || props.nama || "Detail Informasi";

            // === Latitude & Longitude ===
            if (featureData.latlng) {
                content += `
                    <div class="detail-item">
                        <div class="detail-label">Latitude</div>
                        <div class="detail-value">${featureData.latlng.lat}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Longitude</div>
                        <div class="detail-value">${featureData.latlng.lng}</div>
                    </div>
                `;
            }

            // === Radius khusus Circle ===
            if (geomType === "circle" || geomType === "circlemarker") {
                content += `
                    <div class="detail-item">
                        <div class="detail-label">Radius</div>
                        <div class="detail-value">${featureData.radius || props.radius || '-'}</div>
                    </div>
                `;
            }

            // === Name ===
            if (modalTitle) {
                content += `
                    <div class="detail-item">
                        <div class="detail-label">Name</div>
                        <div class="detail-value">${modalTitle}</div>
                    </div>
                `;
            }

            // === Description ===
            if (props.description || props.Deskripsi) {
                content += `
                    <div class="detail-item">
                        <div class="detail-label">Description</div>
                        <div class="detail-value">${props.description || props.Deskripsi}</div>
                    </div>
                `;
            }

            // === Info Teknis ===
            if (featureData.technical_info) {
                let tech = featureData.technical_info;
                if (typeof tech === "string") {
                    try { tech = JSON.parse(tech); } catch {}
                }

                if (typeof tech === "object" && tech !== null) {
                    let techList = "<ul>";
                    for (const [k, v] of Object.entries(tech)) {
                        if (v) techList += `<li><strong>${k}:</strong> ${v}</li>`;
                    }
                    techList += "</ul>";

                    content += `
                        <div class="detail-item">
                            <div class="detail-label">Info Teknis</div>
                            <div class="detail-value">${techList}</div>
                        </div>
                    `;
                }
            }

            // === Foto ===
            if (featureData.image_path) {
                content += `
                    <div class="detail-item">
                        <div class="detail-label">Foto</div>
                        <div class="detail-value">
                            <img src="${featureData.image_path}" style="width:100%; border-radius:8px;">
                        </div>
                    </div>
                `;
            }

            // === Caption ===
            if (featureData.caption) {
                content += `
                    <div class="detail-item">
                        <div class="detail-label">Caption</div>
                        <div class="detail-value">${featureData.caption}</div>
                    </div>
                `;
            }

            // Render
            modalTitleElement.textContent = modalTitle;
            detailContent.innerHTML = content || "<p>Tidak ada detail.</p>";
        }
    </script>
@endsection