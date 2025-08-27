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

{{-- ===================== MODE: LAYER ===================== --}}
@if(isset($layer))
    <div class="layer-container">
        <header class="layer-header">
            <h1 class="layer-title">Layer: {{ $layer->nama_layer }}</h1>
            <p class="layer-description">{{ $layer->deskripsi ?? 'Tidak ada deskripsi untuk layer ini.' }}</p>
            <a href="{{ route('gallery_maps.index') }}" class="back-link">Kembali ke Galeri</a>
        </header>

        <div class="layer-map-wrapper">
            {{-- ID Peta diubah menjadi 'layer-map' agar tidak konflik --}}
            <div id="layer-map" style="height: 100%; width: 100%;"></div>
        </div>
    </div>
@endif

{{-- ===================== MODE: PROYEK ===================== --}}
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
                        <img class="loc-img" src="{{ asset('storage/survey/' . $location->primary_image) }}" alt="{{ $location->nama }}">
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

    @php
        $locationsForMap = $project->surveyLocations->map(function($loc) {
            return [
                'lat' => $loc->geometry['lat'] ?? 0,
                'lng' => $loc->geometry['lng'] ?? 0,
                'nama' => $loc->nama,
                'image' => $loc->primary_image ? asset('storage/survey/' . $loc->primary_image) : null,
                'user_name' => $loc->user->name ?? 'Tidak diketahui'
            ];
        });
    @endphp
@endif

@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

@if(isset($layer))
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const layerData = @json($layer);
        
        const map = L.map('layer-map').setView([-2.5, 117], 5);
        L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3'],
            attribution: '© Google Satellite'
        }).addTo(map);

        const markers = [];

        layerData.maps.forEach(mapData => {
            const pivotData = mapData.layers.find(l => l.id === layerData.id)?.pivot;

            if (pivotData && pivotData.lat && pivotData.lng) {
                const latlng = [pivotData.lat, pivotData.lng];
                let marker;
                
                if (pivotData.layer_type === 'marker' && pivotData.icon_url) {
                    const icon = L.icon({
                        iconUrl: pivotData.icon_url,
                        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
                    });
                    marker = L.marker(latlng, { icon: icon });
                } else {
                    marker = L.circleMarker(latlng, {
                        radius: 8, fillColor: pivotData.fill_color || '#ff7800',
                        color: pivotData.stroke_color || '#000', weight: 1, opacity: 1, fillOpacity: 0.8
                    });
                }
                marker.bindPopup(`<b>${mapData.name}</b><br>${mapData.description || ''}`);
                marker.addTo(map);
                markers.push(marker);
            }
        });

        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.5));
        }
    });
    </script>
    @endif

@if(isset($project))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const locations = @json($locationsForMap ?? []);

    if (locations.length > 0) {
        const map = L.map('proj-map').setView([locations[0].lat, locations[0].lng], 13);
        const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: '© Google Satellite', maxZoom: 20
        }).addTo(map);

        const baseMaps = {
            "Google Maps": L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { attribution: '© Google Maps', maxZoom: 20 }),
            "Google Satellite": googleSatellite,
            "OpenTopoMap": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { attribution: 'Map data: © OSM contributors, SRTM | Map style: © OpenTopoMap', maxZoom: 17 })
        };

        const overlayMaps = {};
        const markers = [];
        const grouped = {};

        locations.forEach(loc => {
            let popupContent = `<b class="font-semibold">${loc.nama}</b>`;
            if (loc.image) {
                popupContent += `<br><img src="${loc.image}" alt="${loc.nama}" style="width:120px; margin-top:8px; border-radius: 4px;">`;
            }
            const marker = L.marker([loc.lat, loc.lng]).bindPopup(popupContent);
            if (!grouped[loc.user_name]) grouped[loc.user_name] = L.layerGroup();
            grouped[loc.user_name].addLayer(marker);
            markers.push(marker);
        });

        Object.keys(grouped).forEach(user => {
            overlayMaps[user] = grouped[user];
            grouped[user].addTo(map);
        });

        L.control.layers(baseMaps, overlayMaps, { position: 'topright', collapsed: true }).addTo(map);

        if (markers.length > 1) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    } else {
        const map = L.map('proj-map').setView([-6.9175, 107.6191], 10);
        L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: '© Google Satellite', maxZoom: 20
        }).addTo(map);
    }
});
</script>
@endif
@endsection
