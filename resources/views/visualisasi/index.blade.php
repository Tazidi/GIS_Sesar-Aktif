@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        #map {
            height: 70vh !important;
            min-height: 400px !important;
            width: 100% !important;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .map-container {
            width: 100%;
            margin: 20px 0;
            padding: 0;
            height: auto;
        }

        .leaflet-container {
            width: 100% !important;
            height: 100% !important;
        }

        .layer-controls {
            display: none !important;
        }

        .layer-item {
            margin: 8px 0;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .layer-item:hover {
            background-color: #f5f5f5;
        }

        .layer-toggle {
            margin-right: 8px;
        }

        /* Legend Box Styles */
        .legend-box {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            min-width: 200px;
            max-width: 300px;
            border: 1px solid #ddd;
        }

        .legend-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin: 6px 0;
            font-size: 12px;
            color: #555;
        }

        .legend-symbol {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            border-radius: 3px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .legend-symbol.marker {
            border-radius: 50%;
        }

        .legend-symbol.circle {
            border-radius: 50%;
            border: 2px solid;
            background: none;
        }

        .legend-symbol.polyline {
            height: 3px;
            border-radius: 2px;
            border: none;
        }

        .legend-symbol.polygon {
            border-radius: 2px;
        }

        .legend-text {
            flex: 1;
            line-height: 1.3;
        }

        .legend-item.inactive {
            opacity: 0.4;
        }

        .legend-item.inactive .legend-text {
            text-decoration: line-through;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .modal-close:hover {
            background-color: #f0f0f0;
        }

        .modal-body {
            padding: 20px;
        }

        .detail-item {
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #333;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }

        .photo-container {
            text-align: center;
            margin: 20px 0;
        }

        .photo-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .no-photo {
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            color: #666;
            font-style: italic;
        }

        /* Popup Styles */
        .leaflet-popup-content {
            margin: 12px 16px;
            line-height: 1.4;
        }

        .popup-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .popup-info {
            margin-bottom: 12px;
        }

        .popup-info-item {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .popup-info-label {
            font-weight: 600;
            color: #555;
        }

        .popup-info-value {
            color: #333;
        }

        .btn-detail {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-detail:hover {
            background: linear-gradient(135deg, #0056b3, #003d82);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            #map {
                height: 60vh !important;
                min-height: 300px !important;
            }

            .modal-content {
                width: 95%;
                max-height: 90vh;
            }

            .modal-header,
            .modal-body {
                padding: 15px;
            }
        }

        body,
        html {
            overflow-x: hidden;
        }

        .container {
            overflow-x: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <h1>Halaman Visualisasi Peta</h1>
        <a href="{{ route('home') }}">← Kembali ke Beranda</a>
        <div class="layer-controls">
            <h3>Pilih Layer</h3>
            @foreach ($maps as $map)
                <div class="layer-item">
                    <label>
                        <input type="checkbox" class="layer-toggle" data-id="{{ $map->id }}"
                            data-name="{{ $map->name }}" data-description="{{ $map->description }}"
                            data-photo="{{ $map->image_path ? asset($map->image_path) : '' }}"
                            data-layer-type="{{ $map->layer_type ?? 'marker' }}"
                            data-stroke-color="{{ $map->stroke_color ?? '#000000' }}"
                            data-fill-color="{{ $map->fill_color ?? '#ff0000' }}" data-opacity="{{ $map->opacity ?? 0.8 }}"
                            data-weight="{{ $map->weight ?? 2 }}" data-radius="{{ $map->radius ?? 300 }}"
                            data-icon-url="{{ $map->icon_url ?? '' }}" data-lat="{{ $map->lat }}"
                            data-lng="{{ $map->lng }}"
                            data-layer="{{ $map->layer->nama_layer ?? 'Layer Tanpa Nama' }}" checked>
                        {{ $map->layer->nama_layer ?? 'Layer Tanpa Nama' }}
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
                    @foreach ($maps as $map)
                        <div class="legend-item" data-legend-id="{{ $map->id }}">
                            <div class="legend-symbol {{ $map->layer_type ?? 'marker' }}"
                                style="
                                    @if (($map->layer_type ?? 'marker') == 'marker') background-color: {{ $map->fill_color ?? '#ff0000' }};
                                        border-color: {{ $map->stroke_color ?? '#000000' }};
                                    @elseif(($map->layer_type ?? 'marker') == 'circle')
                                        border-color: {{ $map->stroke_color ?? '#000000' }};
                                        background-color: {{ $map->fill_color ?? '#ff0000' }};
                                        opacity: {{ $map->opacity ?? 0.8 }};
                                        border-width: {{ $map->weight ?? 2 }}px;
                                    @elseif(($map->layer_type ?? 'marker') == 'polyline')
                                        background-color: {{ $map->stroke_color ?? '#000000' }};
                                        height: {{ $map->weight ?? 2 }}px;
                                    @elseif(($map->layer_type ?? 'marker') == 'polygon')
                                        background-color: {{ $map->fill_color ?? '#ff0000' }};
                                        border-color: {{ $map->stroke_color ?? '#000000' }};
                                        opacity: {{ $map->opacity ?? 0.8 }};
                                        border-width: {{ $map->weight ?? 2 }}px; @endif
                                ">
                                @if (($map->layer_type ?? 'marker') == 'marker' && $map->icon_url)
                                    <img src="{{ $map->icon_url }}" style="width: 16px; height: 16px; border-radius: 50%;"
                                        alt="icon">
                                @endif
                            </div>
                            <div class="legend-text">
                                {{ $map->layer->nama_layer ?? 'Layer Tanpa Nama' }}
                                <br>
                                <small style="color: #777;">
                                    @if (($map->layer_type ?? 'marker') == 'marker')
                                        Penanda Lokasi
                                    @elseif(($map->layer_type ?? 'marker') == 'circle')
                                        Lingkaran ({{ $map->radius ?? 300 }}m)
                                    @elseif(($map->layer_type ?? 'marker') == 'polyline')
                                        Garis/Jalur
                                    @elseif(($map->layer_type ?? 'marker') == 'polygon')
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
        function updateLegend() {
            const legendContent = document.getElementById('legend-content');
            let legendHTML = '';

            const layerToggles = document.querySelectorAll('.layer-toggle');

            layerToggles.forEach(toggle => {
                const mapId = toggle.getAttribute('data-id');
                const layerType = toggle.getAttribute('data-layer-type') || 'marker';
                const strokeColor = toggle.getAttribute('data-stroke-color') || '#000000';
                const fillColor = toggle.getAttribute('data-fill-color') || '#ff0000';
                const opacity = parseFloat(toggle.getAttribute('data-opacity')) || 0.8;
                const weight = parseInt(toggle.getAttribute('data-weight')) || 2;
                const isVisible = toggle.checked;
                const layerTitle = toggle.parentElement.textContent.trim();

                let description = '';
                switch (layerType) {
                    case 'marker':
                        description = 'Titik lokasi';
                        break;
                    case 'circle':
                        description = 'Area lingkaran';
                        break;
                    case 'polyline':
                        description = 'Garis/jalur';
                        break;
                    case 'polygon':
                        description = 'Area wilayah';
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
                            ${layerTitle} ${!isVisible ? '<span style="color:#999;">(nonaktif)</span>' : ''}
                            <br>
                            <small style="color: #777;">${description}</small>
                        </div>
                    </div>
                `;
            });

            legendContent.innerHTML = legendHTML;
        }

        // Global variables
        let currentFeatureData = null;

        // Modal functions
        function openModal(featureData) {
            currentFeatureData = featureData;
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
            let props = featureData.properties || featureData;

            let content = '';

            if (Object.keys(props).length === 0) {
                // Input Manual → ambil dari data-* atribut
                const nama = currentFeatureData.getAttribute('data-name') || 'Tidak ada nama';
                const deskripsi = currentFeatureData.getAttribute('data-description') || 'Tidak ada deskripsi';
                const foto = currentFeatureData.getAttribute('data-photo') || '';

                // Tampilkan Nama
                content += `<div class="detail-item">
                    <div class="detail-label">Nama:</div>
                    <div class="detail-value">${nama}</div>
                </div>`;

                // Tampilkan Deskripsi
                content += `<div class="detail-item">
                    <div class="detail-label">Deskripsi:</div>
                    <div class="detail-value">${deskripsi}</div>
                </div>`;

                // Tampilkan Foto
                if (foto) {
                    content += `<div class="photo-container">
                        <div class="detail-label">Foto:</div>
                        <img src="${foto}" alt="Foto" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="no-photo" style="display: none;">Foto tidak dapat dimuat</div>
                    </div>`;
                } else {
                    content += `<div class="photo-container">
                        <div class="detail-label">Foto:</div>
                        <div class="no-photo">Tidak ada foto</div>
                    </div>`;
                }

            } else {
                // GeoJSON → tampilkan semua key-value
                // Nama
                if (props.name || props.title || props.nama) {
                    content += `<div class="detail-item">
                        <div class="detail-label">Nama:</div>
                        <div class="detail-value">${props.name || props.title || props.nama}</div>
                    </div>`;
                }

                // Key-value lainnya
                Object.entries(props).forEach(([key, value]) => {
                    if (key !== 'name' && key !== 'title' && key !== 'nama' && key !== 'photo' && key !== 'foto' &&
                        key !== 'image' && key !== 'gambar') {
                        content += `<div class="detail-item">
                            <div class="detail-label">${formatLabel(key)}:</div>
                            <div class="detail-value">${value || 'Tidak ada data'}</div>
                        </div>`;
                    }
                });

                // Foto
                const photoUrl = props.photo || props.foto || props.image || props.gambar;
                if (photoUrl) {
                    content += `<div class="photo-container">
                        <div class="detail-label">Foto:</div>
                        <img src="${photoUrl}" alt="Foto" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="no-photo" style="display: none;">Foto tidak dapat dimuat</div>
                    </div>`;
                } else {
                    content += `<div class="photo-container">
                        <div class="detail-label">Foto:</div>
                        <div class="no-photo">Tidak ada foto</div>
                    </div>`;
                }
            }

            detailContent.innerHTML = content;
        }

        function formatLabel(key) {
            // Format label untuk tampilan yang lebih baik
            return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function createPopupContent(feature, fallbackData) {
            const props = feature.properties || {};
            const name = props.name || fallbackData.dataset.name || 'Informasi';
            const title = props.name || props.title || props.nama || fallbackData.dataset.name || 'Informasi';

            const quickInfo = [];

            if (Object.keys(props).length > 0) {
                // GeoJSON → Tampilkan max 3 properti
                Object.entries(props).slice(0, 3).forEach(([key, value]) => {
                    if (key !== 'name' && key !== 'title' && key !== 'nama' && value) {
                        quickInfo.push(`<div class="popup-info-item">
                            <span class="popup-info-label">${formatLabel(key)}:</span>
                            <span class="popup-info-value">${value}</span>
                        </div>`);
                    }
                });
            }

            const encodedData = Object.keys(props).length === 0 ?
                encodeURIComponent(JSON.stringify({
                    nama: fallbackData.dataset.name || 'Tidak ada nama',
                    deskripsi: fallbackData.dataset.description || 'Tidak ada deskripsi',
                    foto: fallbackData.dataset.photo || ''
                })) :
                encodeURIComponent(JSON.stringify(props));

            return `
                <div class="popup-title">${title}</div>
                <div class="popup-info">
                    ${quickInfo.join('')}
                </div>
                <button class="btn-detail open-detail-btn" data-feature="${encodedData}">
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

        // Close modal with Escape key
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
                }).setView([-7.5, 107.5], 7);

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

                // Pasang default base layer
                baseLayers["OSM Standard"].addTo(map);


                // Fix ukuran peta
                setTimeout(() => map.invalidateSize(), 500);
                window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 100));

                // Objek untuk menyimpan layer
                const mapLayers = {};
                const allBounds = [];
                let loadedCount = 0;
                const totalMaps = {{ count($maps) }};

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

                // Fungsi untuk fit semua bounds
                function fitAllBounds() {
                    if (allBounds.length > 0) {
                        const group = L.featureGroup();
                        allBounds.forEach(bounds => {
                            if (bounds.isValid()) {
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
                        }
                    } else {
                        map.setView([-7.5, 107.5], 7);
                    }
                }

                // Fungsi membuat style berdasarkan layer type
                function createLayerStyle(layerData) {
                    const layerType = layerData.getAttribute('data-layer-type') || 'marker';
                    const strokeColor = layerData.getAttribute('data-stroke-color') || '#000000';
                    const fillColor = layerData.getAttribute('data-fill-color') || '#ff0000';
                    const opacity = parseFloat(layerData.getAttribute('data-opacity')) || 0.8;
                    const weight = parseInt(layerData.getAttribute('data-weight')) || 2;

                    return {
                        color: strokeColor,
                        fillColor: fillColor,
                        weight: weight,
                        opacity: opacity,
                        fillOpacity: opacity * 0.7
                    };
                }

                // Fungsi untuk load GeoJSON
                function loadGeoJSON(mapId, layerData) {
                    const url = `/maps/${mapId}/geojson`;
                    const style = createLayerStyle(layerData);
                    const layerType = layerData.getAttribute('data-layer-type') || 'marker';
                    const radius = parseFloat(layerData.getAttribute('data-radius')) || 300;
                    const iconUrl = layerData.getAttribute('data-icon-url') || '';
                    const lat = parseFloat(layerData.getAttribute('data-lat'));
                    const lng = parseFloat(layerData.getAttribute('data-lng'));
                    const title = layerData.getAttribute('data-title') || 'Informasi';

                    fetch(url)
                        .then(response => {
                            if (!response.ok) throw new Error('No GeoJSON found');
                            return response.json();
                        })
                        .then(data => {
                            const layer = L.geoJSON(data, {
                                style: function(feature) {
                                    return style;
                                },
                                onEachFeature: function(feature, layer) {
                                    const popupContent = createPopupContent(feature,
                                        layerData);
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

                            mapLayers[mapId] = layer;
                            layer.addTo(map);
                            if (layer.getBounds && layer.getBounds().isValid()) {
                                allBounds.push(layer.getBounds());
                            }
                        })
                        .catch(() => {
                            // Fallback ke lat/lng kalo GeoJSON tidak ada
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
                                }, layerData);
                                layer.bindPopup(popupContent, {
                                    maxWidth: 300,
                                    className: 'custom-popup'
                                });

                                mapLayers[mapId] = L.layerGroup([layer]);
                                mapLayers[mapId].addTo(map);
                                allBounds.push(L.latLngBounds([latlng]));
                            }
                        })
                        .finally(() => {
                            loadedCount++;
                            if (loadedCount === totalMaps) {
                                setTimeout(() => fitAllBounds(), 500);
                            }
                        });
                }

                setTimeout(() => {
                    const overlayLayers = {};

                    // Ambil semua layer yang dimuat
                    Object.keys(mapLayers).forEach(id => {
                        const checkbox = document.querySelector(
                            `.layer-toggle[data-id="${id}"]`);
                        const label = checkbox?.dataset.layer || checkbox?.parentElement
                            ?.textContent.trim() || `Layer ${id}`;
                        overlayLayers[label] = mapLayers[id];
                    });

                    // Tambahkan ke control box Leaflet
                    L.control.layers(baseLayers, overlayLayers, {
                        collapsed: false
                    }).addTo(map);
                }, 1000);

                // Load semua peta
                @foreach ($maps as $map)
                    (function() {
                        const layerToggle = document.querySelector(`[data-id="{{ $map->id }}"]`);
                        if (layerToggle) {
                            loadGeoJSON({{ $map->id }}, layerToggle);
                        }
                    })();
                @endforeach

                // Event listener untuk toggle layer
                document.querySelectorAll('.layer-toggle').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const mapId = this.getAttribute('data-id');
                        if (this.checked) {
                            if (mapLayers[mapId]) {
                                mapLayers[mapId].addTo(map);
                            } else {
                                loadGeoJSON(mapId, this);
                            }
                        } else {
                            if (mapLayers[mapId]) {
                                map.removeLayer(mapLayers[mapId]);
                            }
                        }
                        updateLegend();
                    });
                });

            }, 300);
        });
    </script>
@endsection
