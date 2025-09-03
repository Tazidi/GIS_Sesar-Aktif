@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="{{ asset('css/map.css') }}">

    @if(request()->boolean('embed'))
        <style>
            /* Sembunyikan header/nav/footer dari layout saat di-embed */
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
        <script type="application/json" id="maps-data">
            [
                @foreach ($maps as $map)
                {
                    "id": {{ $map->id }},
                    "name": {!! json_encode($map->name) !!},
                    "description": {!! json_encode($map->description) !!},
                    "image_path": "{{ $map->image_path ? asset('storage/' . $map->image_path) : '' }}",
                    "geometry": {!! $map->geometry ? json_encode(json_decode($map->geometry)) : 'null' !!},
                    "default_stroke_color": "{{ $map->stroke_color ?? '#000000' }}",
                    "default_fill_color": "{{ $map->fill_color ?? '#ff0000' }}",
                    "default_opacity": {{ $map->opacity ?? 0.8 }},
                    "default_weight": {{ $map->weight ?? 2 }},
                    "default_radius": {{ $map->radius ?? 300 }},
                    "default_icon_url": "{{ $map->icon_url ?? '' }}",
                    "features": [
                        @foreach ($map->features as $feature)
                        {
                            "geometry": {!! $feature->geometry ? json_encode($feature->geometry) : 'null' !!},
                            "properties": {!! $feature->properties ? json_encode($feature->properties) : 'null' !!},
                            "image_path": "{{ $feature->image_path ? asset('map_features/' . $feature->image_path) : '' }}",
                            "caption": {!! json_encode($feature->caption ?? '') !!},
                            "technical_info": {!! json_encode($feature->technical_info ?? '') !!}
                        }@if(!$loop->last),@endif
                        @endforeach
                    ],
                    "layers": [
                        @foreach ($map->layers as $layer)
                        {
                            "id": {{ $layer->id }},
                            "name": {!! json_encode($layer->nama_layer ?? 'Layer Tanpa Nama') !!},
                            "type": "{{ $layer->pivot->layer_type ?? 'marker' }}",
                            "stroke_color": "{{ $layer->pivot->stroke_color ?? '' }}",
                            "fill_color": "{{ $layer->pivot->fill_color ?? '' }}",
                            "opacity": {{ $layer->pivot->opacity ?? 'null' }},
                            "weight": {{ $layer->pivot->weight ?? 'null' }},
                            "radius": {{ $layer->pivot->radius ?? 'null' }},
                            "icon_url": "{{ $layer->pivot->icon_url ?? '' }}",
                            "lat": {{ $layer->pivot->lat ?? $map->lat ?? 0 }},
                            "lng": {{ $layer->pivot->lng ?? $map->lng ?? 0 }}
                        }@if(!$loop->last),@endif
                        @endforeach
                    ]
                }@if(!$loop->last),@endif
                @endforeach
            ]
        </script>

        @php
            $uniqueLayers = [];
            $uniqueLegendItems = [];
            foreach ($maps as $map) {
                foreach ($map->layers as $layer) {
                    $layerName = $layer->nama_layer;
                    if (!isset($uniqueLayers[$layerName])) {
                        $uniqueLayers[$layerName] = $layer;
                        $uniqueLegendItems[$layerName] = [
                            'layer_type' => $layer->pivot->layer_type ?? 'marker',
                            'fill_color' => $layer->pivot->fill_color ?: $map->default_fill_color ?? '#ff0000',
                            'stroke_color' => $layer->pivot->stroke_color ?: $map->default_stroke_color ?? '#000000',
                            'opacity' => $layer->pivot->opacity ?? $map->default_opacity ?? 0.8,
                            'weight' => $layer->pivot->weight ?? $map->default_weight ?? 2,
                            'icon_url' => $layer->pivot->icon_url ?: $map->default_icon_url ?? '',
                        ];
                    }
                }
            }
        @endphp

        <div class="layer-controls">
            <h3>Pilih Layer</h3>
            @foreach ($uniqueLayers as $layerName => $layer)
                <div class="layer-item">
                    <label>
                        <input type="checkbox" class="layer-group-toggle" data-layer-name="{{ $layerName }}" checked>
                        {{ $layerName }}
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
                                @elseif ($item['layer_type'] == 'polyline') background-color: {{ $item['stroke_color'] }}; height: {{ $item['weight'] }}px;
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const BMKG_ICON_URL = "{{ asset('bmkg/earthquake.png') }}";
        const mapsData = JSON.parse(document.getElementById('maps-data').textContent);
        
        let map;
        let bmkgLayerGroup;
        const layerGroups = {};

        function updateLegend() {
            document.querySelectorAll('.layer-group-toggle').forEach(checkbox => {
                const layerName = checkbox.getAttribute('data-layer-name');
                const legendItem = document.querySelector(`.legend-item[data-legend-layer="${layerName}"]`);
                if (legendItem) {
                    legendItem.classList.toggle('inactive', !checkbox.checked);
                }
            });
        }

        function openModal(featureData) {
            displayDetailContent(featureData);
            document.getElementById('detail-modal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('detail-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // UPDATED: displayDetailContent to fix duplication and missing photo
        function displayDetailContent(featureData) {
            const detailContent = document.getElementById('detail-content');
            const modalTitleElement = document.querySelector('#detail-modal .modal-title');
            let content = '';
            let modalTitle = 'Detail Informasi';

            if (featureData.dataSource === 'manual') {
                modalTitle = featureData.name || 'Tidak ada nama';
                content += `<div class="detail-item"><div class="detail-label">Deskripsi</div><div class="detail-value">${featureData.description || '<i>Tidak ada</i>'}</div></div>`;
                if (featureData.photo) {
                    content += `<div class="detail-item"><div class="detail-label">Foto</div><div class="detail-value">
                        <img src="${featureData.photo}" alt="Foto" style="max-width: 100%; border-radius: 8px; cursor: pointer;"
                             onclick="window.open('${featureData.photo}', '_blank')"
                             onerror="this.parentElement.innerHTML = '<i>Gagal memuat</i>';">
                    </div></div>`;
                } else {
                    content += `<div class="detail-item"><div class="detail-label">Foto</div><div class="detail-value"><i>Tidak ada foto</i></div></div>`;
                }
            } else if (featureData.dataSource === 'geojson') {
                modalTitle = featureData.Name || featureData.name || featureData.title || featureData.nama || 'Detail Fitur';
                
                const properties = { ...featureData };
                // Remove non-display properties
                delete properties.dataSource;
                delete properties.feature_image_path;
                delete properties.caption;
                delete properties.technical_info;

                // Display properties, excluding the main title properties which are already in the header
                Object.entries(properties).forEach(([key, value]) => {
                    if (value && !['Name', 'name', 'title', 'nama'].includes(key)) {
                       content += `<div class="detail-item"><div class="detail-label">${formatLabel(key)}</div><div class="detail-value">${value}</div></div>`;
                    }
                });

                // Technical Info (parse JSON jika ada)
                if (featureData.technical_info && featureData.technical_info.trim() !== '' && featureData.technical_info !== '{}') {
                    try {
                        const info = JSON.parse(featureData.technical_info);
                        if (info && typeof info === 'object') {
                            // filter hanya value yang tidak null/kosong
                            const validEntries = Object.entries(info).filter(([k, v]) => v !== null && v !== '' && v !== 'null' && v !== undefined);
                            if (validEntries.length > 0) {
                                let list = '<ul>';
                                validEntries.forEach(([k,v]) => {
                                    list += `<li><strong>${formatLabel(k)}:</strong> ${v}</li>`;
                                });
                                list += '</ul>';
                                content += `<div class="detail-item"><div class="detail-label">Info Teknis</div><div class="detail-value">${list}</div></div>`;
                            }
                        }
                    } catch(e) {
                        if (featureData.technical_info !== 'null') {
                            content += `<div class="detail-item"><div class="detail-label">Info Teknis</div><div class="detail-value">${featureData.technical_info}</div></div>`;
                        }
                    }
                }

                // Add photo section with fallback
                if (featureData.feature_image_path) {
                    content += `<div class="detail-item"><div class="detail-label">Foto</div><div class="detail-value">
                        <img src="${featureData.feature_image_path}" alt="Foto" style="max-width: 100%; border-radius: 8px; cursor: pointer;"
                             onclick="window.open('${featureData.feature_image_path}', '_blank')"
                             onerror="this.parentElement.innerHTML = '<i>Gagal memuat</i>';">
                    </div></div>`;
                } else {
                    content += `<div class="detail-item"><div class="detail-label">Foto</div><div class="detail-value"><i>Tidak ada foto</i></div></div>`;
                }

                // Caption
                if (featureData.caption) {
                    content += `<div class="detail-item"><div class="detail-label">Caption</div><div class="detail-value">${featureData.caption}</div></div>`;
                }
            }

            if (modalTitleElement) {
                modalTitleElement.textContent = modalTitle;
            }
            detailContent.innerHTML = content || '<p>Tidak ada detail untuk ditampilkan.</p>';
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
                dataForModal = { ...props, dataSource: 'geojson', feature_image_path: feature.image_path || '', caption: feature.caption || '', technical_info: feature.technical_info || '' };
            } else {
                title = mapData.name || 'Informasi';
                dataForModal = { dataSource: 'manual', name: mapData.name, description: mapData.description, photo: mapData.image_path, caption: mapData.caption || '' };
            }

            let quickInfoHTML = '';
            if (isGeoJSON) {
                const keys = Object.keys(props).filter(key => key !== 'geometry' && key !== 'timestamp' && props[key] && props[key].toString().trim() !== '');
                const topThree = keys.slice(0, 3);
                topThree.forEach(key => {
                    quickInfoHTML += `<div class="popup-info-item">${formatLabel(key)}: ${props[key]}</div>`;
                });
            } else if (dataForModal.description) {
                quickInfoHTML = `<div class="popup-info-item">${dataForModal.description.substring(0, 70)}...</div>`;
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

        document.getElementById('detail-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('open-detail-btn')) {
                const featureData = JSON.parse(decodeURIComponent(e.target.getAttribute('data-feature')));
                openModal(featureData);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                map = L.map('map', { preferCanvas: true, zoomControl: true }).setView([-2.5, 117], 5);

                var baseLayers = {
                    "Google Maps": L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'], attribution: '&copy; Google Maps' }),
                    "Google Satellite": L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'], attribution: '&copy; Google Satellite' }),
                    "OpenTopoMap": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { attribution: 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap (CC-BY-SA)', maxZoom: 17 })
                };
                baseLayers["Google Satellite"].addTo(map);

                const overlayLayers = {};
                const layerControl = L.control.layers(baseLayers, overlayLayers, { collapsed: true, position: 'topright' }).addTo(map);

                setTimeout(() => map.invalidateSize(), 500);
                window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 100));

                const allBounds = [];

                const gempaIcon = L.icon({
                    iconUrl: BMKG_ICON_URL,
                    iconSize: [40, 40],
                    iconAnchor: [20, 40],
                    popupAnchor: [0, -38]
                });
                bmkgLayerGroup = L.layerGroup();
                fetch('https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.json')
                    .then(res => res.json())
                    .then(data => {
                        const gempaList = data?.Infogempa?.gempa?.slice(0, 15) || [];
                        gempaList.forEach(gempa => {
                            const [lat, lng] = gempa.Coordinates.split(',').map(s => parseFloat(s.trim()));
                            if (!isNaN(lat) && !isNaN(lng)) {
                                L.marker([lat, lng], { icon: gempaIcon })
                                .bindPopup(`
                                    <div class="popup-header">Gempa Bumi</div>
                                    <div class="popup-info">
                                        <div class="popup-info-item"><b>Tanggal:</b> ${gempa.Tanggal}</div>
                                        <div class="popup-info-item"><b>Jam:</b> ${gempa.Jam}</div>
                                        <div class="popup-info-item"><b>Magnitude:</b> ${gempa.Magnitude}</div>
                                        <div class="popup-info-item"><b>Kedalaman:</b> ${gempa.Kedalaman}</div>
                                        <div class="popup-info-item"><b>Wilayah:</b> ${gempa.Wilayah}</div>
                                    </div>
                                `, { className: 'custom-popup' })
                                .addTo(bmkgLayerGroup);
                            }
                        });
                        overlayLayers["BMKG: 15 Gempa"] = bmkgLayerGroup;
                        bmkgLayerGroup.addTo(map);
                        layerControl.addOverlay(bmkgLayerGroup, "BMKG: 15 Gempa");
                        updateLegend();
                    })
                    .catch(err => console.error("Gagal mengambil data BMKG:", err));

                function fitAllBounds() {
                    const group = L.featureGroup(allBounds);
                    if (group.getLayers().length > 0) {
                        map.fitBounds(group.getBounds(), { padding: [30, 30], maxZoom: 12 });
                    } else {
                        map.setView([-2.5, 117], 5);
                    }
                }

                function createLayerStyle(layerInfo, mapData) {
                    const opacity = parseFloat(layerInfo.opacity) || parseFloat(mapData.default_opacity) || 0.8;
                    return {
                        color: layerInfo.stroke_color || mapData.default_stroke_color || '#000000',
                        fillColor: layerInfo.fill_color || mapData.default_fill_color || '#ff0000',
                        weight: parseInt(layerInfo.weight) || parseInt(mapData.default_weight) || 2,
                        opacity: opacity,
                        fillOpacity: opacity * 0.7
                    };
                }

                function processMapData(mapData) {
                    mapData.layers.forEach(layerInfo => {
                        const style = createLayerStyle(layerInfo, mapData);
                        const layerType = layerInfo.type || 'marker';
                        const radius = parseFloat(layerInfo.radius) || parseFloat(mapData.default_radius) || 300;
                        const iconUrl = layerInfo.icon_url || mapData.default_icon_url || '';
                        const lat = parseFloat(layerInfo.lat);
                        const lng = parseFloat(layerInfo.lng);
                        const layerName = layerInfo.name;

                        if (!layerGroups[layerName]) {
                            layerGroups[layerName] = L.layerGroup();
                        }

                        let hasData = false;

                        if (mapData.features && mapData.features.length > 0) {
                            hasData = true;
                            mapData.features.forEach(featureData => {
                                try {
                                    const geoJsonFeature = { type: 'Feature', geometry: featureData.geometry, properties: featureData.properties, image_path: featureData.image_path, caption: featureData.caption, technical_info: featureData.technical_info || ''};
                                    const layer = L.geoJSON(geoJsonFeature, {
                                        style: () => style,
                                        onEachFeature: (feature, layer) => {
                                            layer.bindPopup(createPopupContent(feature, mapData), { className: 'custom-popup', minWidth: 250 });
                                        },
                                        pointToLayer: (feature, latlng) => {
                                            if (layerType === 'circle') return L.circle(latlng, { radius, ...style });
                                            if (layerType === 'marker' && iconUrl) return L.marker(latlng, { icon: L.icon({ iconUrl, iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16] }) });
                                            return L.circleMarker(latlng, { radius: 8, ...style });
                                        }
                                    });
                                    layerGroups[layerName].addLayer(layer);
                                    if (layer.getBounds && layer.getBounds().isValid()) allBounds.push(layer);
                                } catch (error) { console.error('Error processing feature:', error, featureData); }
                            });
                        } else if (mapData.geometry) {
                             hasData = true;
                             try {
                                const geoJsonFeature = { type: 'Feature', geometry: mapData.geometry, properties: {} };
                                const layer = L.geoJSON(geoJsonFeature, {
                                    style: () => style,
                                    onEachFeature: (feature, layer) => {
                                        layer.bindPopup(createPopupContent(feature, mapData), { className: 'custom-popup', minWidth: 250 });
                                    },
                                    pointToLayer: (feature, latlng) => {
                                        if (layerType === 'circle') return L.circle(latlng, { radius, ...style });
                                        if (layerType === 'marker' && iconUrl) return L.marker(latlng, { icon: L.icon({ iconUrl, iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16] }) });
                                        return L.circleMarker(latlng, { radius: 8, ...style });
                                    }
                                });
                                layerGroups[layerName].addLayer(layer);
                                if (layer.getBounds && layer.getBounds().isValid()) allBounds.push(layer);
                             } catch (error) { console.error('Error processing map geometry:', error, mapData); }
                        }

                        if (!hasData && !isNaN(lat) && !isNaN(lng)) {
                            const latlng = L.latLng(lat, lng);
                            let manualLayer;
                            if (layerType === 'circle') manualLayer = L.circle(latlng, { radius, ...style });
                            else if (layerType === 'marker' && iconUrl) manualLayer = L.marker(latlng, { icon: L.icon({ iconUrl, iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16] }) });
                            else manualLayer = L.circleMarker(latlng, { radius: 8, ...style });
                            manualLayer.bindPopup(createPopupContent({ properties: {} }, mapData), { className: 'custom-popup', minWidth: 250 });
                            layerGroups[layerName].addLayer(manualLayer);
                            allBounds.push(manualLayer);
                        }
                    });
                }

                mapsData.forEach(processMapData);

                Object.keys(layerGroups).forEach(layerName => {
                    overlayLayers[layerName] = layerGroups[layerName];
                    layerGroups[layerName].addTo(map);
                    layerControl.addOverlay(layerGroups[layerName], layerName);
                });

                updateLegend();
                fitAllBounds();

                document.querySelectorAll('.layer-group-toggle').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const layerName = this.getAttribute('data-layer-name');
                        const targetGroup = layerName === 'BMKG: 15 Gempa' ? bmkgLayerGroup : layerGroups[layerName];
                        
                        if (targetGroup) {
                            if (this.checked) {
                                map.addLayer(targetGroup);
                            } else {
                                map.removeLayer(targetGroup);
                            }
                        }
                        updateLegend();
                    });
                });

                map.on('overlayadd overlayremove', function(e) {
                    const checkbox = document.querySelector(`.layer-group-toggle[data-layer-name="${e.name}"]`);
                    if (checkbox) {
                        checkbox.checked = map.hasLayer(e.layer);
                    }
                    updateLegend();
                });

            }, 300);
        });
    </script>
@endsection
