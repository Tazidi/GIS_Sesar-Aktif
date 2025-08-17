@extends('layouts.app')

@section('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Container & Layout - dipakai untuk mode Map */
        .map-gallery-container { max-width: 1200px; margin: 0 auto; padding: 20px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .map-header { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; }
        .map-title { font-size: 28px; font-weight: 600; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2; }
        .back-link { display: inline-flex; align-items: center; color: #6b7280; text-decoration: none; font-size: 14px; transition: color 0.2s ease; }
        .back-link:hover { color: #374151; }
        .back-link::before { content: '←'; margin-right: 8px; }
        .map-wrapper { margin-bottom: 24px; }
        .map-container { position: relative; height: 600px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        #map { height: 100%; width: 100%; }
        /* Modal Styles (mode Map) */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 1050; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 16px; width: 90%; max-width: 500px; max-height: 80vh; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .modal-header { padding: 20px 24px 16px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 20px; font-weight: 600; color: #1f2937; margin: 0; }
        .modal-close { background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer; padding: 4px; border-radius: 6px; transition: all 0.2s ease; }
        .modal-close:hover { background: #f3f4f6; color: #6b7280; }
        .modal-body { padding: 20px 24px 24px; max-height: calc(80vh - 70px); overflow-y: auto; }
        .leaflet-popup-content-wrapper { border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .leaflet-popup-content { margin: 16px; font-size: 14px; line-height: 1.4; }
        .btn-detail { background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; margin-top: 12px; display: block; width: 100%; text-align: center; transition: background-color 0.2s ease; }
        .btn-detail:hover { background: #1d4ed8; }
        .detail-item { margin-bottom: 12px; border-bottom: 1px solid #f3f4f6; padding-bottom: 8px; }
        .detail-label { font-weight: bold; color: #374151; margin-bottom: 4px; }
        .detail-value { color: #6b7280; }

        /* ---- Mode Proyek (adopsi projects.show, view-only) ---- */
        .proj-wrap { max-width: 1120px; margin: 0 auto; padding: 20px; }
        .proj-header { display:flex; justify-content:space-between; align-items:start; margin-bottom: 16px; }
        .proj-title { font-size: 28px; font-weight: 800; color:#1f2937; }
        .proj-desc { color:#6b7280; margin-top:4px; }
        .proj-map-card { background: white; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); border-radius: 12px; padding: 16px; margin-bottom: 24px; }
        #proj-map { height: 450px; border-radius: 8px; }
        .loc-grid { display:grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 16px; }
        @media (min-width:768px){ .loc-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
        @media (min-width:1024px){ .loc-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
        .loc-card { background:#fff; border-radius:12px; overflow:hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); display:flex; flex-direction:column; }
        .loc-img { width:100%; height:190px; object-fit:cover; background:#f3f4f6; display:block; }
        .loc-body { padding:14px; display:flex; flex-direction:column; flex:1; }
        .loc-title { font-weight:700; }
        .loc-text { font-size:14px; color:#6b7280; margin-top:6px; flex:1; }
        .loc-geo { font-size:12px; color:#9ca3af; margin-top:8px; }
    </style>
@endsection

@section('content')

{{-- ===================== MODE: MAP (eksisting) ===================== --}}
@if(isset($map))
    <div class="map-gallery-container">
        <header class="map-header">
            <h1 class="map-title">{{ $map->name }}</h1>
            <a href="{{ route('gallery_maps.index') }}" class="back-link">Kembali ke Galeri Peta</a>
        </header>

        <div class="map-wrapper">
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>

        {{-- Deskripsi Peta --}}
        <div class="max-w-2xl mx-auto bg-white p-6 shadow-lg rounded-lg mt-6">
            <h2 class="text-lg font-bold text-gray-800 mb-2">Deskripsi Peta</h2>
            <p class="text-sm text-gray-600">{{ $map->description ?? 'Belum ada deskripsi yang tersedia untuk peta ini.' }}</p>
        </div>

        {{-- Modal Detail --}}
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
@endif

{{-- ===================== MODE: PROYEK (adopsi projects.show – view-only) ===================== --}}
@if(isset($project))
    <div class="proj-wrap">
        <div class="mb-3">
            <a href="{{ route('gallery_maps.index') }}" class="back-link">Kembali ke Galeri Peta</a>
        </div>

        <div class="proj-header">
            <div>
                <h1 class="proj-title">{{ $project->name }}</h1>
                <p class="proj-desc">{{ $project->description }}</p>
            </div>
        </div>

        <div class="proj-map-card">
            <div id="proj-map"></div>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Lokasi</h2>
        <div class="loc-grid">
            @forelse ($project->surveyLocations as $location)
                <a href="{{ route('gallery_maps.projects.locations.show', [$project, $location->id]) }}" class="loc-card" style="text-decoration: none; color: inherit;">
                    @if($location->primary_image)
                        <img class="loc-img" src="{{ asset('survey/' . $location->primary_image) }}" alt="{{ $location->nama }}">
                    @else
                        <div class="loc-img"></div>
                    @endif
                    <div class="loc-body">
                        <div class="loc-title">{{ $location->nama }}</div>
                        <p class="loc-text">{{ $location->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                        <p class="loc-geo">Lat: {{ $location->geometry['lat'] ?? 'N/A' }}, Lng: {{ $location->geometry['lng'] ?? 'N/A' }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white p-8 rounded-lg shadow-md text-center">
                    <p class="text-gray-500">Belum ada lokasi yang ditambahkan ke proyek ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Data untuk peta proyek (adopsi dari projects.show) --}}
    @php
        $locationsForMap = $project->surveyLocations->map(function($loc) {
            return [
                'lat' => $loc->geometry['lat'] ?? 0,
                'lng' => $loc->geometry['lng'] ?? 0,
                'nama' => $loc->nama,
                'image' => $loc->primary_image ? asset('survey/' . $loc->primary_image) : null
            ];
        });
    @endphp
@endif

@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

@if(isset($map))
    {{-- ========= Script MAP (eksisting, dipertahankan) ========= --}}
    <script type="application/json" id="maps-data">
        [
            @foreach ($maps as $index => $map)
            {
                "id": @json($map->id),
                "name": @json($map->name),
                "description": @json($map->description),
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
                "layer_name": @json($map->layer->nama_layer ?? 'Layer Tanpa Nama'),
                "geometry": {!! $map->geometry ? json_encode(json_decode($map->geometry)) : 'null' !!},
                "features": [
                    @foreach ($map->features as $feature)
                    {
                        "geometry": {!! $feature->geometry ? json_encode($feature->geometry) : 'null' !!},
                        "properties": {!! $feature->properties ? json_encode($feature->properties) : 'null' !!},
                        "image_path": "{{ $feature->feature_image_path ?? ($feature->image_path ? asset($feature->image_path) : '') }}",
                        "caption": @json($feature->caption ?? ''),
                        "technical_info": {!! json_encode($feature->technical_info ?? '') !!}
                    }@if(!$loop->last),@endif
                    @endforeach
                ]
            }@if(!$loop->last),@endif
            @endforeach
        ]
    </script>

    <script>
        // === Seluruh script map show eksisting kamu tetap dipakai di sini ===
        // (Direduksi bagian panjang agar jawaban ringkas—logika sama seperti file aslinya.) :contentReference[oaicite:6]{index=6}

        let mapsData = [];
        try { mapsData = JSON.parse(document.getElementById('maps-data').textContent); } catch(e){ mapsData = []; }

        function openModal(featureData){ displayDetailContent(featureData); document.getElementById('detail-modal').style.display='block'; document.body.style.overflow='hidden'; }
        function closeModal(){ document.getElementById('detail-modal').style.display='none'; document.body.style.overflow='auto'; }
        function formatLabel(key){ if(!key||typeof key!=='string') return ''; return key.replace(/_/g,' ').replace(/\b\w/g,l=>l.toUpperCase()); }
        function displayDetailContent(data) {
            const wrap = document.getElementById('detail-content');
            if (!wrap) return;

            let html = '';

            const skip = new Set(['dataSource','feature_image_path','caption','technical_info']);
            Object.keys(data).forEach(key => {
                if (skip.has(key)) return;
                const val = data[key];
                if (!val || typeof val === 'object') return;

                html += `
                <div class="detail-item">
                    <div class="detail-label">${formatLabel(key)}</div>
                    <div class="detail-value">${val}</div>
                </div>
                `;
            });

            if (data.technical_info) {
                html += `
                <div class="detail-item">
                    <div class="detail-label">Informasi Teknis</div>
                    <div class="detail-value"><pre style="white-space:pre-wrap; margin:0;">${data.technical_info}</pre></div>
                </div>
                `;
            }

            if (data.feature_image_path) {
                html += `
                <div class="detail-item" style="margin-top:16px;">
                    <img src="${data.feature_image_path}" 
                        alt="Gambar" 
                        style="width:100%; max-height:240px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                    ${data.caption ? `<div class="detail-value">${data.caption}</div>` : ''}
                </div>
                `;
            }

            wrap.innerHTML = html || '<div class="detail-value">Tidak ada detail tersedia.</div>';
        }

        function createPopupContent(feature, mapData) {
            const props = feature.properties || {};
            const isGeoJSON = Object.keys(props).length > 0;
            let dataForModal, title;
            if (isGeoJSON) {
                title = props.Name || props.name || props.title || props.nama || 'Informasi';
                dataForModal = { ...props, dataSource:'geojson', feature_image_path: feature.feature_image_path || '', caption: feature.caption || '', technical_info: feature.technical_info || '' };
            } else {
                title = mapData.name || 'Informasi';
                dataForModal = { dataSource:'manual', name: mapData.name, description: mapData.description, image_path: mapData.image_path };
            }
            const encoded = encodeURIComponent(JSON.stringify(dataForModal));
            return `<div style="font-weight: bold; margin-bottom: 8px;">${title}</div><button class="btn-detail" data-feature='${encoded}'>Lihat Detail</button>`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('map', { preferCanvas:true, zoomControl:true }).setView([-2.5, 117], 5);
            const baseLayers = {
                "Google Maps": L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom:20, subdomains:['mt0','mt1','mt2','mt3'], attribution:'© Google' }),
                "Google Satellite": L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { maxZoom:20, subdomains:['mt0','mt1','mt2','mt3'], attribution:'© Google' }),
                "OpenTopoMap": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { maxZoom:17, attribution:'© OpenTopoMap' })
            };
            baseLayers["Google Satellite"].addTo(map);
            L.control.layers(baseLayers).addTo(map);

            const allBounds = [];

            mapsData.forEach(mapData => {
                const style = {
                    color: mapData.stroke_color || "#3388ff",
                    weight: mapData.weight || 3,
                    opacity: mapData.opacity || 0.8,
                    fillColor: mapData.fill_color || "#3388ff",
                    fillOpacity: (mapData.opacity || 0.8) * 0.5,
                };

                if (mapData.features && mapData.features.length > 0) {
                    mapData.features.forEach(feature => {
                        const geometry = typeof feature.geometry === 'string' ? JSON.parse(feature.geometry) : feature.geometry;
                        if (!geometry) return;
                        const geojsonFeature = {
                            "type": "Feature",
                            "geometry": geometry,
                            "properties": { ...(feature.properties || {}), feature_image_path: feature.image_path || feature.feature_image_path || null, caption: feature.caption || null, technical_info: feature.technical_info || null }
                        };
                        const layer = L.geoJSON(geojsonFeature, {
                            style: () => style,
                            onEachFeature: function(feature, layer) {
                                feature.feature_image_path = geojsonFeature.properties.feature_image_path;
                                feature.caption = geojsonFeature.properties.caption;
                                feature.technical_info = geojsonFeature.properties.technical_info;
                                const popupContent = createPopupContent(feature, mapData);
                                layer.bindPopup(popupContent);
                            },
                            pointToLayer: function(feature, latlng) {
                                const layerType = mapData.layer_type || 'marker';
                                if (layerType === 'circle') return L.circle(latlng, { ...style, radius: mapData.radius || 300 });
                                if (layerType === 'marker' && mapData.icon_url) {
                                    const customIcon = L.icon({ iconUrl: mapData.icon_url, iconSize: [32,32], iconAnchor:[16,16], popupAnchor:[0,-16] });
                                    return L.marker(latlng, { icon: customIcon });
                                }
                                return L.circleMarker(latlng, { ...style, radius: 8 });
                            }
                        }).addTo(map);
                        if (layer.getBounds && layer.getBounds().isValid()) { allBounds.push(layer.getBounds()); }
                    });
                } else if (mapData.geometry && typeof mapData.geometry === 'object') {
                    const layer = L.geoJSON(mapData.geometry, {
                        style: () => style,
                        onEachFeature: function(feature, layer) {
                            const popupContent = createPopupContent(feature, mapData);
                            layer.bindPopup(popupContent);
                        },
                        pointToLayer: function(feature, latlng) {
                            const layerType = mapData.layer_type || 'marker';
                            if (layerType === 'circle') return L.circle(latlng, { ...style, radius: mapData.radius || 300 });
                            if (layerType === 'marker' && mapData.icon_url) {
                                const customIcon = L.icon({ iconUrl: mapData.icon_url, iconSize: [32,32], iconAnchor:[16,16], popupAnchor:[0,-16] });
                                return L.marker(latlng, { icon: customIcon });
                            }
                            return L.circleMarker(latlng, { ...style, radius: 8 });
                        }
                    }).addTo(map);
                    if (layer.getBounds && layer.getBounds().isValid()) { allBounds.push(layer.getBounds()); }
                } else if (mapData.lat && mapData.lng) {
                    const latlng = L.latLng(mapData.lat, mapData.lng);
                    const layerType = mapData.layer_type || 'marker';
                    let layer;
                    if (layerType === 'circle') layer = L.circle(latlng, { ...style, radius: mapData.radius || 300 });
                    else if (layerType === 'marker' && mapData.icon_url) {
                        const customIcon = L.icon({ iconUrl: mapData.icon_url, iconSize: [32,32], iconAnchor:[16,16], popupAnchor:[0,-16] });
                        layer = L.marker(latlng, { icon: customIcon });
                    } else layer = L.circleMarker(latlng, { ...style, radius: 8 });
                    const popupContent = createPopupContent({ properties: {} }, mapData);
                    layer.bindPopup(popupContent).addTo(map);
                    allBounds.push(L.latLngBounds([latlng]));
                }
            });

            if (allBounds.length > 0) {
                const group = L.featureGroup();
                allBounds.forEach(bounds => { if (bounds.isValid()) group.addLayer(L.rectangle(bounds, { opacity: 0 })); });
                if (group.getLayers().length > 0) map.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 16 });
            }

            map.on('popupopen', function() {
                const btn = document.querySelector('.btn-detail');
                if (btn) btn.addEventListener('click', function(e) {
                    try { const featureData = JSON.parse(decodeURIComponent(e.target.getAttribute('data-feature'))); openModal(featureData); } catch (err) {}
                });
            });

            document.getElementById('detail-modal')?.addEventListener('click', function(e){ if (e.target === this) closeModal(); });
            document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeModal(); });
        });
    </script>
@endif

@if(isset($project))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const locations = @json($locationsForMap ?? []);

    if (locations.length > 0) {
        const map = L.map('proj-map').setView([locations[0].lat, locations[0].lng], 13);

        // === Basemap ===
        const googleMaps = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            attribution: '© Google Maps',
            maxZoom: 20
        });

        const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: '© Google Satellite',
            maxZoom: 20
        }).addTo(map); // default aktif

        const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data: © OSM contributors, SRTM | Map style: © OpenTopoMap',
            maxZoom: 17
        });

        const baseMaps = {
            "Google Maps": googleMaps,
            "Google Satellite": googleSatellite,
            "OpenTopoMap": topo
        };

        // === Overlay lokasi per item ===
        const overlayMaps = {};
        const markers = [];

        locations.forEach(loc => {
            let popupContent = `<b class="font-semibold">${loc.nama}</b>`;
            if (loc.image) {
                popupContent += `<br><img src="${loc.image}" alt="${loc.nama}" style="width:120px; margin-top:8px; border-radius: 4px;">`;
            }

            const marker = L.marker([loc.lat, loc.lng]).bindPopup(popupContent);
            overlayMaps[loc.nama] = marker;
            marker.addTo(map); // default tampil
            markers.push(marker);
        });

        // Tambahkan control box (basemap + lokasi checkbox)
        L.control.layers(baseMaps, overlayMaps, { position: 'topright', collapsed: true }).addTo(map);

        // Auto zoom ke semua lokasi
        if (markers.length > 1) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    } else {
        const map = L.map('proj-map').setView([-6.9175, 107.6191], 10);

        const googleMaps = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            attribution: '© Google Maps',
            maxZoom: 20
        });

        const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: '© Google Satellite',
            maxZoom: 20
        }).addTo(map);

        const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data: © OSM contributors, SRTM | Map style: © OpenTopoMap',
            maxZoom: 17
        });

        const baseMaps = {
            "Google Maps": googleMaps,
            "Google Satellite": googleSatellite,
            "OpenTopoMap": topo
        };
        L.control.layers(baseMaps, null, { position: 'topright', collapsed: true }).addTo(map);
    }
});
</script>
@endif
@endsection
