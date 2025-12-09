@extends('layouts.app')

@section('title', 'Edit Layer: ' . $layer->nama_layer)

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <style>
        .feature-card { border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; }
        .feature-card .leaflet-container { border-radius: 0.5rem; border: 1px solid #d1d5db; }
        .leaflet-draw-toolbar { border: 1px solid #9ca3af !important; }
    </style>
@endsection

@section('content')
<div class="container mx-auto py-8 px-4">
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700 border border-red-200" role="alert">
            <span class="font-bold">Terjadi Kesalahan:</span>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('layers.update', $layer) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Bagian untuk Data Layer Utama --}}
        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Edit Detail Layer</h1>
            <p class="text-sm text-gray-500 mt-1 mb-6">Ubah nama, deskripsi, dan pindahkan layer ke peta lain jika diperlukan.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_layer" class="block text-sm font-medium text-gray-700">Nama Layer</label>
                    <input type="text" name="nama_layer" id="nama_layer" value="{{ old('nama_layer', $layer->nama_layer) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi Layer</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('deskripsi', $layer->deskripsi) }}</textarea>
                </div>

                {{-- BARU: Pilihan untuk memindahkan layer ke peta lain --}}
                <div class="md:col-span-2">
                    <label for="map_id" class="block text-sm font-medium text-gray-700">Lokasi Peta</label>
                    <select name="map_id" id="map_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Peta Tujuan --</option>
                        @foreach($maps as $map)
                            <option value="{{ $map->id }}" {{ (old('map_id', $currentMapId) == $map->id) ? 'selected' : '' }}>
                                {{ $map->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('map_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                {{-- AKHIR BAGIAN BARU --}}
            </div>
        </div>

        {{-- Perulangan untuk setiap Fitur/Geometri --}}
        {{-- Peta overview semua geometri --}}
        <div class="bg-white p-4 rounded-xl shadow-lg border mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Peta Lokasi</h2>
            <p class="text-xs text-gray-500 mb-3">
                Setiap perubahan geometri & styling pada fitur di bawah akan langsung tercermin di peta ini.
            </p>
            <div id="overview-map" class="h-80 w-full border border-gray-300 rounded"></div>
        </div>

        {{-- Perulangan untuk setiap Fitur/Geometri --}}
        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Geometri Individual</h2>
        <div class="space-y-8">
            @foreach ($layer->mapFeatures as $feature)
                @php
                    $props = !empty($feature->properties) ? json_decode($feature->properties, true) : [];
                    $tech  = !empty($feature->technical_info) ? json_decode($feature->technical_info, true) : [];
                    $pivot = $feature->pivot;

                    // Deteksi tipe geometri dari geometry + technical_info
                    $geometryData = !empty($feature->geometry) ? json_decode($feature->geometry, true) : null;
                    $rawType = strtolower($geometryData['type'] ?? ($tech['geometry_type'] ?? ''));

                    $geometryType = 'polygon'; // default aman

                    if ($rawType === 'point') {
                        // Kalau di technical_info ada geometry_type, pakai itu (marker / circle),
                        // kalau tidak, anggap marker
                        $geometryType = $tech['geometry_type'] ?? 'marker';
                    } elseif (in_array($rawType, ['linestring', 'multilinestring'])) {
                        $geometryType = 'polyline';
                    } elseif (in_array($rawType, ['polygon', 'multipolygon'])) {
                        $geometryType = 'polygon';
                    } elseif ($rawType === 'circle') {
                        $geometryType = 'circle';
                    } elseif (!empty($tech['geometry_type'])) {
                        // fallback terakhir dari technical_info
                        $geometryType = $tech['geometry_type'];
                    }

                    // Nilai default styling (ambil dari pivot > props > fallback)
                    $selectedIcon = old("features.{$feature->id}.icon_url",
                        $pivot->icon_url ?? ($props['icon_url'] ?? 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png')
                    );

                    $radius = old("features.{$feature->id}.radius",
                        $pivot->radius ?? ($props['radius'] ?? 200)
                    );

                    $strokeColor = old("features.{$feature->id}.stroke_color",
                        $pivot->stroke_color ?? ($props['stroke_color'] ?? '#3388ff')
                    );

                    $fillColor = old("features.{$feature->id}.fill_color",
                        $pivot->fill_color ?? ($props['fill_color'] ?? '#3388ff')
                    );

                    $weight = old("features.{$feature->id}.weight",
                        $pivot->weight ?? ($props['weight'] ?? 3)
                    );

                    $opacity = old("features.{$feature->id}.opacity",
                        $pivot->opacity ?? ($props['opacity'] ?? 0.5)
                    );
                @endphp

                <div class="feature-card bg-white p-6 shadow-md"
                    data-feature-id="{{ $feature->id }}"
                    data-geometry-type="{{ $geometryType }}"
                    data-stroke-color="{{ $strokeColor }}"
                    data-fill-color="{{ $fillColor }}"
                    data-weight="{{ $weight }}"
                    data-opacity="{{ $opacity }}"
                    data-icon-url="{{ $selectedIcon }}"
                    data-radius="{{ $radius }}">
                    <input type="hidden" name="features[{{ $feature->id }}][id]" value="{{ $feature->id }}">
                    <input type="hidden" name="features[{{ $feature->id }}][geometry_type]" value="{{ $geometryType }}">

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Mengedit Fitur: {{ $props['name'] ?? "Fitur #{$feature->id}" }}
                        <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                            Tipe: {{ ucfirst($geometryType) }}
                        </span>
                    </h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Kolom Kiri: Properti & Styling --}}
                        <div class="space-y-6">
                            {{-- Input Nama & Deskripsi Fitur --}}
                            <div>
                                <label for="name-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Nama Fitur</label>
                                <input type="text"
                                    name="features[{{$feature->id}}][name]"
                                    id="name-{{$feature->id}}"
                                    value="{{ old("features.{$feature->id}.name", $props['name'] ?? '') }}"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="desc-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Deskripsi Fitur</label>
                                <textarea name="features[{{$feature->id}}][description]"
                                        id="desc-{{$feature->id}}"
                                        rows="2"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old("features.{$feature->id}.description", $props['description'] ?? '') }}</textarea>
                            </div>

                            {{-- Input Styling (dinamis per geometry_type) --}}
                            <div class="border-t pt-4">
                                <h4 class="text-md font-medium text-gray-800 mb-2">Styling</h4>
                                <p class="text-xs text-gray-500 mb-3">
                                    Pengaturan tampilan berdasarkan jenis geometri:
                                    <span class="font-semibold">{{ ucfirst($geometryType) }}</span>
                                </p>

                                <div class="grid grid-cols-2 gap-4">

                                    {{-- === MARKER: hanya icon_url === --}}
                                    <div class="{{ $geometryType === 'marker' ? '' : 'hidden' }}">
                                        <label for="icon-{{$feature->id}}" class="block text-sm font-medium text-gray-700">
                                            Ikon Marker
                                        </label>
                                        <select name="features[{{$feature->id}}][icon_url]"
                                                id="icon-{{$feature->id}}"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-sm">
                                            <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png"
                                                {{ $selectedIcon == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png' ? 'selected' : '' }}>
                                                Biru (Default)
                                            </option>
                                            <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png"
                                                {{ $selectedIcon == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png' ? 'selected' : '' }}>
                                                Hijau
                                            </option>
                                            <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png"
                                                {{ $selectedIcon == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png' ? 'selected' : '' }}>
                                                Kuning
                                            </option>
                                            <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png"
                                                {{ $selectedIcon == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png' ? 'selected' : '' }}>
                                                Merah
                                            </option>
                                            <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png"
                                                {{ $selectedIcon == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png' ? 'selected' : '' }}>
                                                Abu-abu
                                            </option>
                                        </select>
                                    </div>

                                    {{-- === CIRCLE: radius + style garis/isi === --}}
                                    <div class="{{ $geometryType === 'circle' ? '' : 'hidden' }}">
                                        <label for="radius-{{$feature->id}}" class="block text-sm font-medium text-gray-700">
                                            Radius (meter)
                                        </label>
                                        <input type="number"
                                            name="features[{{$feature->id}}][radius]"
                                            id="radius-{{$feature->id}}"
                                            value="{{ $radius }}"
                                            min="1"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    {{-- === Garis: polygon / polyline / circle === --}}
                                    <div class="{{ in_array($geometryType, ['polygon', 'polyline', 'circle']) ? '' : 'hidden' }}">
                                        <label for="stroke-{{$feature->id}}" class="block text-sm font-medium text-gray-700">
                                            Warna Garis
                                        </label>
                                        <input type="color"
                                            name="features[{{$feature->id}}][stroke_color]"
                                            id="stroke-{{$feature->id}}"
                                            value="{{ $strokeColor }}"
                                            class="mt-1 block w-full h-10 border border-gray-300 rounded">
                                    </div>

                                    {{-- === Fill: hanya polygon / circle === --}}
                                    <div class="{{ in_array($geometryType, ['polygon', 'circle']) ? '' : 'hidden' }}">
                                        <label for="fill-{{$feature->id}}" class="block text-sm font-medium text-gray-700">
                                            Warna Isi
                                        </label>
                                        <input type="color"
                                            name="features[{{$feature->id}}][fill_color]"
                                            id="fill-{{$feature->id}}"
                                            value="{{ $fillColor }}"
                                            class="mt-1 block w-full h-10 border border-gray-300 rounded">
                                    </div>

                                    {{-- === Ketebalan garis: polygon / polyline / circle === --}}
                                    <div class="{{ in_array($geometryType, ['polygon', 'polyline', 'circle']) ? '' : 'hidden' }}">
                                        <label for="weight-{{$feature->id}}" class="block text-sm font-medium text-gray-700">
                                            Tebal Garis
                                        </label>
                                        <input type="number"
                                            name="features[{{$feature->id}}][weight]"
                                            id="weight-{{$feature->id}}"
                                            value="{{ $weight }}"
                                            min="1"
                                            max="10"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    {{-- === Opacity: polygon / polyline / circle === --}}
                                    <div class="{{ in_array($geometryType, ['polygon', 'polyline', 'circle']) ? '' : 'hidden' }}">
                                        <label for="opacity-{{$feature->id}}" class="block text-sm font-medium text-gray-700">
                                            Transparansi
                                        </label>
                                        <input type="number"
                                            name="features[{{$feature->id}}][opacity]"
                                            id="opacity-{{$feature->id}}"
                                            value="{{ $opacity }}"
                                            step="0.1"
                                            min="0"
                                            max="1"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Peta, Gambar & Info Teknis --}}
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Peta Geometri</label>
                                <div id="map-{{ $feature->id }}" class="h-64 w-full border border-gray-300 rounded"></div>
                                <input type="hidden"
                                    name="features[{{$feature->id}}][geometry]"
                                    id="geometry-{{$feature->id}}"
                                    value="{{ old("features.{$feature->id}.geometry", $feature->geometry) }}">
                            </div>
                            
                            <div>
                                <label for="image-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Gambar Fitur</label>
                                <input type="file"
                                    name="features[{{$feature->id}}][image]"
                                    id="image-{{$feature->id}}"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @if ($feature->image_path)
                                    <div class="mt-2">
                                        <img src="{{ asset($feature->image_path) }}"
                                            alt="Gambar fitur"
                                            class="w-24 h-24 object-cover rounded-md border border-gray-300">
                                        <label class="flex items-center mt-1">
                                            <input type="checkbox"
                                                name="features[{{$feature->id}}][remove_image]"
                                                value="1"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-red-600">Hapus gambar</span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-8 flex justify-end space-x-4 bg-white p-4 rounded-xl shadow-lg border">
            <a href="{{ route('layers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition duration-200">Batal</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold transition duration-200">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

{{-- Script section remains unchanged --}}
@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // =========================
    // GLOBAL: PETA OVERVIEW
    // =========================
    let overviewMap = null;
    let overviewLayerGroup = null;
    const overviewLayersByFeatureId = {};

    function createOverviewMap() {
        const container = document.getElementById('overview-map');
        if (!container) return;

        overviewMap = L.map(container).setView([-6.9175, 107.6191], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(overviewMap);

        overviewLayerGroup = new L.FeatureGroup().addTo(overviewMap);
    }

    // Helper: convert string geometry => GeoJSON Feature/FeatureCollection
    function geometryStringToGeoJSONFeature(geometryString) {
        if (!geometryString) return null;
        try {
            const geom = JSON.parse(geometryString);
            if (!geom || !geom.type) return null;

            if (geom.type === 'Feature' || geom.type === 'FeatureCollection') {
                return geom;
            }

            return {
                type: 'Feature',
                geometry: geom,
                properties: {}
            };
        } catch (e) {
            console.error('Gagal parse geometry string:', e);
            return null;
        }
    }

    // Helper: sinkron 1 fitur ke peta overview
    function upsertOverviewFeature(featureId, geojson, options) {
        if (!overviewLayerGroup) return;

        // Hapus layer lama
        if (overviewLayersByFeatureId[featureId]) {
            overviewLayerGroup.removeLayer(overviewLayersByFeatureId[featureId]);
            delete overviewLayersByFeatureId[featureId];
        }

        if (!geojson) return;

        const layer = L.geoJSON(geojson, options || {});
        layer.addTo(overviewLayerGroup);
        overviewLayersByFeatureId[featureId] = layer;
    }

    // =========================
    // HELPER ICON MARKER
    // =========================
    function createMarkerIcon(iconUrl) {
        return L.icon({
            iconUrl: iconUrl,
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            shadowSize: [41, 41],
            shadowAnchor: [12, 41]
        });
    }

    // =========================
    // EDITOR PER FEATURE
    // =========================
    function initializeFeatureEditor(featureCard) {
        const featureId = featureCard.dataset.featureId;

        // style awal dari data-*
        let geometryType  = featureCard.dataset.geometryType || 'polygon';
        let strokeColor   = featureCard.dataset.strokeColor || '#3388ff';
        let fillColor     = featureCard.dataset.fillColor || '#3388ff';
        let weight        = parseFloat(featureCard.dataset.weight)  || 3;
        let opacity       = parseFloat(featureCard.dataset.opacity) || 0.5;
        let iconUrl       = featureCard.dataset.iconUrl || 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png';
        let radiusDefault = parseFloat(featureCard.dataset.radius) || 200;

        const mapContainer  = document.getElementById(`map-${featureId}`);
        const geometryInput = document.getElementById(`geometry-${featureId}`);

        const radiusInput   = document.getElementById(`radius-${featureId}`);
        const strokeInput   = document.getElementById(`stroke-${featureId}`);
        const fillInput     = document.getElementById(`fill-${featureId}`);
        const weightInput   = document.getElementById(`weight-${featureId}`);
        const opacityInput  = document.getElementById(`opacity-${featureId}`);
        const iconSelect    = document.getElementById(`icon-${featureId}`);

        if (!mapContainer || !geometryInput) return;

        const map = L.map(mapContainer).setView([-6.9175, 107.6191], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const drawnItems = new L.FeatureGroup().addTo(map);

        // opsi style untuk GeoJSON (dipakai juga di overview)
        const geoJsonOptions = {
            style: function (feature) {
                const hasFill = (geometryType === 'polygon' || geometryType === 'circle');
                return {
                    color: strokeColor,
                    weight: weight,
                    opacity: opacity,
                    fillColor: hasFill ? fillColor : undefined,
                    fillOpacity: hasFill ? opacity : 0
                };
            },
            pointToLayer: function (feature, latlng) {
                if (geometryType === 'circle') {
                    return L.circle(latlng, {
                        radius: radiusDefault,
                        color: strokeColor,
                        weight: weight,
                        opacity: opacity,
                        fillColor: fillColor,
                        fillOpacity: opacity
                    });
                } else {
                    const icon = createMarkerIcon(iconUrl);
                    return L.marker(latlng, { icon: icon });
                }
            }
        };

        // Render geometri awal (mini map + overview)
        try {
            const initialGeo = geometryStringToGeoJSONFeature(geometryInput.value);
            if (initialGeo) {
                L.geoJSON(initialGeo, geoJsonOptions).addTo(drawnItems);

                if (drawnItems.getLayers().length > 0) {
                    map.fitBounds(drawnItems.getBounds(), { padding: [20, 20] });
                }

                // tampilkan juga di peta overview
                upsertOverviewFeature(featureId, initialGeo, geoJsonOptions);
            }
        } catch (e) {
            console.error(`Gagal memuat GeoJSON untuk fitur #${featureId}:`, e);
        }

        const drawControl = new L.Control.Draw({
            edit: { 
                featureGroup: drawnItems,
                edit: true,
                remove: true
            },
            draw: {
                polygon: true,
                polyline: true,
                rectangle: true,
                circle: true,
                marker: true,
                circlemarker: false
            }
        });
        map.addControl(drawControl);

        // Apply styling ke layer (mini map) sesuai setting terbaru
        function applyStyleToLayer(layer) {
            if (layer instanceof L.Marker) {
                if (geometryType === 'marker' && iconUrl) {
                    layer.setIcon(createMarkerIcon(iconUrl));
                }
            } else if (layer instanceof L.Circle) {
                layer.setStyle({
                    color: strokeColor,
                    weight: weight,
                    opacity: opacity,
                    fillColor: fillColor,
                    fillOpacity: opacity
                });
                if (!isNaN(radiusDefault) && radiusDefault > 0) {
                    layer.setRadius(radiusDefault);
                }
            } else if (layer.setStyle) {
                const hasFill = (geometryType === 'polygon' || geometryType === 'circle');
                layer.setStyle({
                    color: strokeColor,
                    weight: weight,
                    opacity: opacity,
                    fillColor: hasFill ? fillColor : undefined,
                    fillOpacity: hasFill ? opacity : 0
                });
            }
        }

        // Update hidden input + peta overview (dipanggil setelah create/edit/delete)
        function updateGeometryAndRadius() {
            const geoJsonData = drawnItems.toGeoJSON();

            if (geoJsonData.features.length > 0) {
                const geom = geoJsonData.features[0].geometry;
                geometryInput.value = JSON.stringify(geom);

                const featureGeo = {
                    type: 'Feature',
                    geometry: geom,
                    properties: {}
                };
                // update di overview
                upsertOverviewFeature(featureId, featureGeo, geoJsonOptions);
            } else {
                geometryInput.value = '';
                upsertOverviewFeature(featureId, null, geoJsonOptions);
            }

            // Sinkron radius circle bila diedit dari peta
            if (radiusInput) {
                let circleLayer = null;
                drawnItems.eachLayer(function (layer) {
                    if (layer instanceof L.Circle) {
                        circleLayer = layer;
                    }
                });
                if (circleLayer) {
                    radiusInput.value = Math.round(circleLayer.getRadius());
                }
            }
        }

        // Restyle semua layer (mini + overview) ketika styling di form berubah
        function restyleAllLayers() {
            drawnItems.eachLayer(function (layer) {
                applyStyleToLayer(layer);
            });

            const currentGeo = geometryStringToGeoJSONFeature(geometryInput.value);
            if (currentGeo) {
                upsertOverviewFeature(featureId, currentGeo, geoJsonOptions);
            }
        }

        // Event Leaflet Draw: create/edit/delete
        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            const layer = e.layer;
            applyStyleToLayer(layer);
            drawnItems.addLayer(layer);
            updateGeometryAndRadius();
        });

        map.on(L.Draw.Event.EDITED, function (e) {
            e.layers.eachLayer(function (layer) {
                applyStyleToLayer(layer);
            });
            updateGeometryAndRadius();
        });

        map.on(L.Draw.Event.DELETED, function (e) {
            updateGeometryAndRadius();
        });

        // =========================
        // EVENT INPUT FORM (REALTIME STYLING)
        // =========================
        if (strokeInput) {
            strokeInput.addEventListener('input', function () {
                strokeColor = this.value;
                restyleAllLayers();
            });
        }

        if (fillInput) {
            fillInput.addEventListener('input', function () {
                fillColor = this.value;
                restyleAllLayers();
            });
        }

        if (weightInput) {
            weightInput.addEventListener('input', function () {
                const v = parseFloat(this.value);
                if (!isNaN(v) && v > 0) weight = v;
                restyleAllLayers();
            });
        }

        if (opacityInput) {
            opacityInput.addEventListener('input', function () {
                const v = parseFloat(this.value);
                if (!isNaN(v) && v >= 0 && v <= 1) opacity = v;
                restyleAllLayers();
            });
        }

        if (iconSelect) {
            iconSelect.addEventListener('change', function () {
                iconUrl = this.value;
                restyleAllLayers();
            });
        }

        if (radiusInput) {
            radiusInput.addEventListener('input', function () {
                const v = parseFloat(this.value);
                if (!isNaN(v) && v > 0) {
                    radiusDefault = v;
                    drawnItems.eachLayer(function (layer) {
                        if (layer instanceof L.Circle) {
                            layer.setRadius(radiusDefault);
                        }
                    });
                    restyleAllLayers();
                }
            });
        }
    }

    // =========================
    // INISIALISASI SEMUA PETA
    // =========================
    createOverviewMap();

    document.querySelectorAll('.feature-card').forEach(card => {
        initializeFeatureEditor(card);
    });

    // Fit overview ke semua fitur setelah semua selesai di-render
    if (overviewMap && overviewLayerGroup && overviewLayerGroup.getLayers().length > 0) {
        overviewMap.fitBounds(overviewLayerGroup.getBounds(), { padding: [20, 20] });
    }
});
</script>
@endsection
