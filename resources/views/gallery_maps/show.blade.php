@extends('layouts.app')

@section('styles')
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

        .map-title a {
            color: #2563eb;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .map-title a:hover {
            color: #1d4ed8;
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

        /* Description Card */
        .description-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #f3f4f6;
        }

        .description-image {
            margin-bottom: 20px;
            text-align: center;
        }

        .description-image img {
            max-width: 100%;
            max-height: 300px;
            width: auto;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .description-content {
            font-size: 15px;
            line-height: 1.6;
            color: #374151;
        }

        .no-description {
            color: #9ca3af;
            font-style: italic;
            text-align: center;
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
            z-index: 1000;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translate(-50%, -45%);
            }
            to { 
                opacity: 1;
                transform: translate(-50%, -50%);
            }
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
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: #6b7280;
        }

        .modal-body {
            padding: 20px 24px 24px;
            max-height: 60vh;
            overflow-y: auto;
        }

        #detail-content {
            font-size: 14px;
            line-height: 1.5;
        }

        #detail-content > div {
            margin-bottom: 12px;
        }

        #detail-content b {
            color: #374151;
            font-weight: 600;
        }

        #detail-content img {
            margin-top: 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
            margin-top: 8px;
            transition: background-color 0.2s ease;
        }

        .btn-detail:hover {
            background: #1d4ed8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .map-gallery-container {
                padding: 16px;
            }

            .map-title {
                font-size: 24px;
            }

            .map-container {
                height: 400px;
                border-radius: 8px;
            }

            .description-card {
                padding: 20px;
                border-radius: 8px;
            }

            .modal-content {
                width: 95%;
                max-height: 85vh;
                border-radius: 12px;
            }

            .modal-header {
                padding: 16px 20px 12px;
            }

            .modal-body {
                padding: 16px 20px 20px;
            }
        }

        /* Loading state */
        .map-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 600px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #f3f4f6;
        }

        .loading-spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #f3f4f6;
            border-top: 3px solid #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Hide default Leaflet attribution */
        .leaflet-control-attribution {
            font-size: 11px;
            opacity: 0.7;
        }
    </style>
@endsection

@section('content')
<div class="map-gallery-container">
    <!-- Header -->
    <header class="map-header">
        <h1 class="map-title">
            <a href="{{ route('gallery.show', $map->id) }}">
                {{ $map->name }}
            </a>
        </h1>
        <a href="{{ route('gallery_maps.index') }}" class="back-link">
            Kembali ke Galeri Peta
        </a>
    </header>

    <!-- Tambahkan ini di show.blade.php sebelum script -->
    <script id="maps-data" type="application/json">
    {!! $maps->toJson() !!}
    </script>

    <!-- Map -->
    <div class="map-wrapper">
        <div class="map-container">
            <div id="map"></div>
        </div>
    </div>

    <!-- Description Card -->
    <div class="description-card">
        @if ($map->image_path)
            <div class="description-image">
                <img src="{{ asset($map->image_path) }}" 
                     alt="Gambar {{ $map->name }}">
            </div>
        @endif

        @if ($map->description)
            <div class="description-content">
                {!! nl2br(e($map->description)) !!}
            </div>
        @else
            <p class="no-description">Tidak ada deskripsi untuk peta ini.</p>
        @endif
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
const mapsData = JSON.parse(document.getElementById('maps-data').textContent);

// Modal functions
function openModal(featureData) {
    displayDetailContent(featureData);
    document.getElementById('detail-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    document.getElementById('detail-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
}
function displayDetailContent(featureData) {
    const detailContent = document.getElementById('detail-content');
    let content = '';

    // ======== DATA MANUAL ========
    if (featureData.dataSource === 'manual') {
        const name = featureData.name || 'Tidak ada nama';
        const description = featureData.description || '<i>Tidak ada deskripsi</i>';
        const photo = featureData.photo || '';

        content += `<div><b>Nama:</b> ${name}</div>`;
        content += `<div><b>Deskripsi:</b> ${description}</div>`;

        if (photo) {
            const photoUrl = photo.includes('http')
                ? photo
                : `/map_images/${photo.replace(/^.*[\\\/]/, '')}`;
            content += `<div style="margin-top:10px">
                <img src="${photoUrl}" style="max-width:100%;border-radius:8px;"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none;padding:20px;background:#f5f5f5;border-radius:8px;color:#666;text-align:center;">
                    Foto tidak dapat dimuat
                </div>
            </div>`;
        }
    }

    // ======== DATA GEOJSON ========
    else if (featureData.dataSource === 'geojson') {
        const title = featureData.name || 'Detail Fitur';
        content += `<div><b>Nama:</b> ${title}</div>`;

        // Tampilkan semua properti selain yang dikecualikan
        Object.entries(featureData).forEach(([key, value]) => {
            if (!['name','description','dataSource','feature_image_path','caption'].includes(key) && value) {
                content += `<div><b>${key}:</b> ${value}</div>`;
            }
        });

        if (featureData.feature_image_path) {
            content += `<div style="margin-top:10px">
                <img src="${featureData.feature_image_path}" style="max-width:100%;border-radius:8px;"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none;padding:20px;background:#f5f5f5;border-radius:8px;color:#666;text-align:center;">
                    Foto tidak dapat dimuat
                </div>
            </div>`;
        } else {
            content += `<div style="margin-top:10px;padding:20px;background:#f5f5f5;border-radius:8px;color:#666;text-align:center;">
                Tidak ada foto
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
        dataForModal = {...props, dataSource: 'geojson', feature_image_path: feature.feature_image_path || '', caption: feature.caption || ''};
    } else {
        title = mapData.name || 'Informasi';
        dataForModal = {dataSource: 'manual', name: mapData.name, description: mapData.description, photo: mapData.image_path, caption: mapData.caption || ''};
    }

    let quickInfoHTML = '';
    if (isGeoJSON) {
        const keys = Object.keys(props).filter(k => props[k] && k !== 'geometry');
        keys.slice(0, 3).forEach(key => {
            quickInfoHTML += `<div><b>${formatLabel(key)}:</b> ${props[key]}</div>`;
        });
    } else if (dataForModal.description) {
        quickInfoHTML = `<div>${dataForModal.description.substring(0,70)}...</div>`;
    }

    const encodedData = encodeURIComponent(JSON.stringify(dataForModal));
    return `<div><b>${title}</b></div>
            <div>${quickInfoHTML}</div>
            <button class="btn-detail open-detail-btn" data-feature='${encodedData}'>Selengkapnya</button>`;
}

// Modal click
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
        var map = L.map('map', {preferCanvas: true, zoomControl: true}).setView([-2.5, 117], 5);

        var baseLayers = {
            "Google Maps": L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3']
            }),
            "Google Satellite": L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3']
            }),
            "OpenTopoMap": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                maxZoom: 17
            })
        };
        baseLayers["Google Satellite"].addTo(map);

        const layerGroups = {};
        const allBounds = [];

        function createLayerStyle(mapData) {
            return {
                color: mapData.stroke_color || '#000',
                fillColor: mapData.fill_color || '#f00',
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

            if (!layerGroups[layerName]) layerGroups[layerName] = L.layerGroup();

            if (mapData.features && mapData.features.length > 0) {
                mapData.features.forEach(featureData => {
                    let geoData = featureData.geometry;
                    if (geoData.type !== 'FeatureCollection') {
                        geoData = {type: 'FeatureCollection', features: [{type: 'Feature', geometry: geoData, properties: featureData.properties || {}}]};
                    }
                    const layer = L.geoJSON(geoData, {
                        style: () => style,
                        onEachFeature: (feature, layer) => {
                            feature.feature_image_path = featureData.image_path;
                            feature.caption = featureData.caption;
                            layer.bindPopup(createPopupContent(feature, mapData), {maxWidth: 300});
                        },
                        pointToLayer: (feature, latlng) => {
                            if (layerType === 'circle') return L.circle(latlng, {radius, ...style});
                            else if (layerType === 'marker' && iconUrl) return L.marker(latlng, {icon: L.icon({iconUrl, iconSize: [32,32], iconAnchor: [16,16]})});
                            else return L.circleMarker(latlng, {radius: 8, ...style});
                        }
                    });
                    layerGroups[layerName].addLayer(layer);
                    if (layer.getBounds && layer.getBounds().isValid()) allBounds.push(layer.getBounds());
                });
            }
        }

        const filteredMaps = mapsData.filter(m => m.id === {{ $map->id }});
        filteredMaps.forEach(processMapData);

        Object.values(layerGroups).forEach(g => g.addTo(map));

        if (allBounds.length) {
            const group = L.featureGroup();
            allBounds.forEach(b => group.addLayer(L.rectangle(b, {opacity: 0})));
            map.fitBounds(group.getBounds(), {padding: [30,30], maxZoom: 12});
        }
    }, 300);
});
</script>
@endsection