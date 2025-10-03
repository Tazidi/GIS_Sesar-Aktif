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
@extends('layouts.app')

@section('title', 'Edit Geometri - ' . $map->name)

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map-notification {
            transition: opacity 0.5s, transform 0.5s;
            transform: translateY(-20px);
            opacity: 0;
            display: none;
        }
        #map-notification.show {
            transform: translateY(0);
            opacity: 1;
            display: block;
        }
        #drawing-toolbar .draw-tool-btn.active {
            background-color: #bfdbfe; /* blue-200 */
            border: 1px solid #3b82f6; /* blue-500 */
        }
    </style>
@endsection

@section('content')
@php
    $props = is_array($geometry->properties) ? $geometry->properties : (json_decode($geometry->properties ?? '[]', true) ?: []);
    $tech  = is_array($geometry->technical_info) ? $geometry->technical_info : (json_decode($geometry->technical_info ?? '[]', true) ?: []);

    // {{-- PERBAIKAN: Logika penentuan tipe geometri diperbaiki untuk memprioritaskan data yang tersimpan --}}
    $geomType = strtolower(
        old('geometry_type', $props['geometry_type'] ?? $tech['geometry_type'] ?? ($geometry->geometry['type'] ?? 'marker'))
    );
    
    // Ambil semua layer yang terkait dengan geometry
    $geometryLayers = $geometry->layers;
    
    // Gunakan nilai dari layer pertama jika ada, atau nilai default
    $firstLayer = $geometryLayers->first();
    $strokeColor = old('stroke_color', $firstLayer->pivot->stroke_color ?? '#3388ff');
    $fillColor   = old('fill_color', $firstLayer->pivot->fill_color ?? '#3388ff');
    $weight      = old('weight', $firstLayer->pivot->weight ?? 3);
    $opacity     = old('opacity', $firstLayer->pivot->opacity ?? 0.5);
    $radius      = old('radius', $firstLayer->pivot->radius ?? 300);
    $iconUrl     = old('icon_url', $firstLayer->pivot->icon_url ?? 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png');
@endphp
<div class="container mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Edit Geometri</h1>

    <form action="{{ route('maps.geometries.update', [$map, $geometry]) }}" method="POST" enctype="multipart/form-data" id="geometry-form">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                
                {{-- Left Column: Form Fields --}}
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Geometri</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('name', $props['name'] ?? $tech['name'] ?? $geometry->caption ?? '') }}" placeholder="Contoh: Titik Survey A" required>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Deskripsi singkat tentang geometri ini">{{ old('description', $props['description'] ?? $tech['description'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih Layer</label>
                        <div class="mt-2 space-y-2">
                            @forelse ($layers as $layer)
                                <label class="flex items-center">
                                    <input type="checkbox" name="layer_ids[]" value="{{ $layer->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        {{ in_array($layer->id, old('layer_ids', $geometry->layers->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ $layer->nama_layer }}
                                        @if($layer->deskripsi) - <span class="text-gray-500 italic">{{ $layer->deskripsi }}</span> @endif
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">Tidak ada layer yang tersedia. Silakan buat layer terlebih dahulu.</p>
                            @endforelse
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Boleh pilih lebih dari satu layer.</p>
                    </div>

                    <div class="border-t pt-4 space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 -mb-2">Opsi Styling</h3>
                        <p class="text-xs text-gray-500">Styling ini akan diterapkan pada semua layer yang dipilih.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Field Warna Garis --}}
                            <div id="stroke-color-field">
                                <label for="stroke_color" class="block text-sm font-medium text-gray-700">Warna Garis</label>
                                <input type="color" name="stroke_color" id="stroke_color" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm" value="{{ $strokeColor }}">
                            </div>

                            {{-- Field Warna Isi --}}
                            <div id="fill-color-field">
                                <label for="fill_color" class="block text-sm font-medium text-gray-700">Warna Isi</label>
                                <input type="color" name="fill_color" id="fill_color" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm" value="{{ $fillColor }}">
                            </div>

                            {{-- Field Ketebalan Garis --}}
                            <div id="weight-field">
                                <label for="weight" class="block text-sm font-medium text-gray-700">Ketebalan Garis (1-10)</label>
                                <input type="number" name="weight" id="weight" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ $weight }}" min="1" max="10">
                            </div>

                            {{-- Field Transparansi --}}
                            <div id="opacity-field">
                                <label for="opacity" class="block text-sm font-medium text-gray-700">Transparansi (0-1)</label>
                                <input type="number" name="opacity" id="opacity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ $opacity }}" min="0" max="1" step="0.1">
                            </div>

                            {{-- Field Radius --}}
                            <div id="radius-field">
                                <label for="radius" class="block text-sm font-medium text-gray-700">Radius (meter)</label>
                                <input type="number" name="radius" id="radius" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ $radius }}" min="1">
                            </div>

                            {{-- Field Ikon --}}
                            <div id="icon-field">
                                <label for="icon_url" class="block text-sm font-medium text-gray-700">Ikon Marker</label>
                                <select name="icon_url" id="icon_url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png" {{ $iconUrl == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png' ? 'selected' : '' }}>Biru (Default)</option>
                                    <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png" {{ $iconUrl == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png' ? 'selected' : '' }}>Hijau</option>
                                    <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png" {{ $iconUrl == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png' ? 'selected' : '' }}>Kuning</option>
                                    <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png" {{ $iconUrl == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png' ? 'selected' : '' }}>Merah</option>
                                    <option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png" {{ $iconUrl == 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png' ? 'selected' : '' }}>Abu-abu</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="feature_image" class="block text-sm font-medium text-gray-700">Gambar Fitur (opsional)</label>
                        <input type="file" name="feature_image" id="feature_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @if($geometry->image_path)
                            <div class="mt-2">
                                <img src="{{ asset($geometry->image_path) }}" alt="Gambar Fitur Saat Ini" class="w-32 h-32 object-cover rounded">
                                <label class="flex items-center mt-1">
                                    <input type="checkbox" name="remove_feature_image" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span class="ml-2 text-sm text-red-700">Hapus gambar ini</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label for="caption" class="block text-sm font-medium text-gray-700">Caption Gambar</label>
                        <input type="text" name="caption" id="caption" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('caption', $geometry->caption) }}" placeholder="Caption untuk gambar fitur" maxlength="255">
                    </div>
                </div>

                {{-- Right Column: Map and Other Controls --}}
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Peta Interaktif</label>
                        <div id="drawing-toolbar" class="mb-2 p-1 bg-gray-100 border border-gray-200 rounded-md inline-flex items-center space-x-1">
                            <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="marker" title="Marker">📍</button>
                            <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="polyline" title="Polyline">〰️</button>
                            <button type="button" class="draw-tool-btn px-3 py-2 rounded hover:bg-gray-200 text-sm" data-type="polygon" title="Polygon">⬠</button>
                            <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="circle" title="Circle">⭕</button>
                        </div>
                        <div id="map" style="height: 400px; border: 1px solid #ccc; border-radius: 8px;"></div>
                        <div id="map-notification" class="mt-2 p-3 bg-blue-100 border border-blue-400 text-blue-700 rounded"></div>
                    </div>

                    <div>
                        <label for="geojson_file" class="block text-sm font-medium text-gray-700">ATAU Upload GeoJSON untuk Mengganti</label>
                        <input type="file" name="geojson_file" id="geojson_file" accept=".geojson,.json" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div id="manual-coords-wrapper" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">ATAU Input Koordinat Manual</label>
                        <div class="grid grid-cols-2 gap-4 p-3 border border-dashed rounded-md">
                            <div>
                                <label for="manual_lat" class="block text-xs font-medium text-gray-600">Latitude</label>
                                <input type="number" id="manual_lat" step="any" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" value="{{ isset($geometry->geometry['coordinates']) && $geometry->geometry['type'] === 'Point' ? number_format($geometry->geometry['coordinates'][1], 6, '.', '') : '' }}">
                            </div>
                            <div>
                                <label for="manual_lng" class="block text-xs font-medium text-gray-600">Longitude</label>
                                <input type="number" id="manual_lng" step="any" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" value="{{ isset($geometry->geometry['coordinates']) && $geometry->geometry['type'] === 'Point' ? number_format($geometry->geometry['coordinates'][0], 6, '.', '') : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Properties (JSON)</h3>
                        <div id="properties-editor" class="space-y-2"></div>
                        <input type="hidden" name="properties" id="properties-json">
                        <button type="button" id="add-prop-btn" class="mt-2 text-sm text-blue-600 hover:underline">+ Tambah field</button>
                        <p class="mt-1 text-xs text-gray-500">Data tambahan dalam format key-value.</p>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Info Teknis</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="panjang_sesar" class="block text-sm font-medium text-gray-700">Panjang Sesar</label>
                                <input type="text" name="panjang_sesar" id="panjang_sesar" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('panjang_sesar', $tech['panjang_sesar'] ?? '') }}">
                            </div>
                            <div>
                                <label for="lebar_sesar" class="block text-sm font-medium text-gray-700">Lebar Sesar</label>
                                <input type="text" name="lebar_sesar" id="lebar_sesar" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('lebar_sesar', $tech['lebar_sesar'] ?? '') }}">
                            </div>
                            <div>
                                <label for="tipe" class="block text-sm font-medium text-gray-700">Tipe</label>
                                <input type="text" name="tipe" id="tipe" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('tipe', $tech['tipe'] ?? '') }}">
                            </div>
                            <div>
                                <label for="mmax" class="block text-sm font-medium text-gray-700">MMAX</label>
                                <input type="text" name="mmax" id="mmax" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('mmax', $tech['mmax'] ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden inputs to store final data --}}
            <input type="hidden" name="geometry" id="geometry-input" required>
            <input type="hidden" name="geometry_type" id="geometry_type_input" value="{{ $geomType }}" required>

            {{-- Submit Button --}}
            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('maps.geometries.index', $map) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Batal</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Update Geometri</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map').setView([-6.9175, 107.6191], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // {{-- PERBAIKAN: Global `drawnLayer` untuk akses dari script lain --}}
    window.drawnLayer = null; 
    let currentTool = null;
    let polygonPoints = [];

    const geometryInput = document.getElementById('geometry-input');
    const geometryTypeInput = document.getElementById('geometry_type_input');
    const toolbarButtons = document.querySelectorAll('.draw-tool-btn');
    const geojsonFileInput = document.getElementById('geojson_file');
    const manualCoordsWrapper = document.getElementById('manual-coords-wrapper');
    const manualLatInput = document.getElementById('manual_lat');
    const manualLngInput = document.getElementById('manual_lng');

    const dynamicFields = {
        icon: document.getElementById('icon-field'),
        radius: document.getElementById('radius-field'),
        strokeColor: document.getElementById('stroke-color-field'),
        fillColor: document.getElementById('fill-color-field'),
        weight: document.getElementById('weight-field'),
        opacity: document.getElementById('opacity-field'),
    };

    function showNotification(message, duration = 4000) {
        const notification = document.getElementById('map-notification');
        notification.innerHTML = message;
        notification.classList.add('show');
        setTimeout(() => notification.classList.remove('show'), duration);
    }

    function getStyleOptions() {
        return {
            color: document.getElementById('stroke_color').value,
            fillColor: document.getElementById('fill_color').value,
            fillOpacity: parseFloat(document.getElementById('opacity').value),
            weight: parseInt(document.getElementById('weight').value),
            radius: parseInt(document.getElementById('radius').value) || 300
        };
    }

    function clearDrawing() {
        if (window.drawnLayer) {
            map.removeLayer(window.drawnLayer);
            window.drawnLayer = null;
        }
        polygonPoints = [];
        geometryInput.value = '';
    }

    function toggleDynamicFields(tool) {
        for (const key in dynamicFields) {
            if (dynamicFields[key]) dynamicFields[key].style.display = 'none';
        }
        manualCoordsWrapper.classList.add('hidden');

        if (!tool) return;

        const fieldsToShow = {
            marker: ['icon'],
            circle: ['radius', 'strokeColor', 'fillColor', 'weight', 'opacity'],
            polygon: ['strokeColor', 'fillColor', 'weight', 'opacity'],
            polyline: ['strokeColor', 'weight', 'opacity']
        };

        if (fieldsToShow[tool]) {
            fieldsToShow[tool].forEach(key => {
                if (dynamicFields[key]) dynamicFields[key].style.display = 'block';
            });
        }
        
        if (['marker', 'circle'].includes(tool)) {
            manualCoordsWrapper.classList.remove('hidden');
        }
    }

    function setActiveTool(tool) {
        currentTool = tool;
        geometryTypeInput.value = tool;
        toolbarButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === tool);
        });
        toggleDynamicFields(tool);
    }

    function updateGeometryInput(layer) {
    // Jika tidak ada layer/gambar, kosongkan input dan hentikan fungsi.
    // Ini akan menyebabkan validasi 'required' gagal, yang mana sudah benar.
    if (!layer) {
        geometryInput.value = '';
        return;
    }

    let geojsonFeature;

    // L.geoJSON membuat sebuah "grup", jadi kita perlu mengekstrak fitur di dalamnya
    if (typeof layer.toGeoJSON === 'function') {
        const geojson = layer.toGeoJSON();
        // Jika hasilnya adalah "amplop" (FeatureCollection), ambil fitur pertama di dalamnya
        if (geojson.type === 'FeatureCollection' && geojson.features.length > 0) {
            geojsonFeature = geojson.features[0];
        } else {
            geojsonFeature = geojson;
        }
    } else {
        // Ini untuk layer sederhana seperti L.marker atau L.circle
        geojsonFeature = layer.toGeoJSON();
    }

    // Jika setelah semua proses kita tidak menemukan data geometri, jangan lakukan apa-apa
    if (!geojsonFeature || !geojsonFeature.geometry) {
        geometryInput.value = '';
        return;
    }
    
    // Ambil hanya bagian 'geometry' (tipe dan koordinat)
    let geometry = geojsonFeature.geometry;
    
    // Logika khusus untuk Circle: pastikan radiusnya ikut tersimpan
    const getCircleLayer = (l) => {
        if (l instanceof L.Circle) return l;
        if (l.eachLayer) {
            const layers = l.getLayers();
            if (layers.length > 0 && layers[0] instanceof L.Circle) return layers[0];
        }
        return null;
    };

    const circleLayer = getCircleLayer(layer);
    if (circleLayer) {
        if (!geometry.properties) {
            geometry.properties = {};
        }
        geometry.properties.radius = circleLayer.getRadius();
    }
    
    // Ubah objek geometri menjadi string JSON dan masukkan ke input form
    geometryInput.value = JSON.stringify(geometry);
}

    // Initialize from existing geometry
    function initializeMap() {
        try {
            const existingGeoJSON = @json($geometry->geometry ?? null);
            let initialToolType = geometryTypeInput.value || 'marker';

            if (!existingGeoJSON) {
                setActiveTool(initialToolType);
                showNotification('Tidak ada geometri. Silakan gambar di peta atau upload file.');
                return;
            }

            const style = getStyleOptions();

            // {{-- PERBAIKAN: Logika inisialisasi disederhanakan dan diperbaiki --}}
            window.drawnLayer = L.geoJSON(existingGeoJSON, {
                style: style,
                pointToLayer: function (feature, latlng) {
                    if (initialToolType === 'circle') {
                        const radius = feature.properties?.radius || style.radius;
                        return L.circle(latlng, { ...style, radius: radius });
                    }
                    // Default to marker
                    const iconUrl = document.getElementById('icon_url').value;
                    const icon = L.icon({ iconUrl: iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                    return L.marker(latlng, { icon: icon });
                }
            }).addTo(map);

            if (window.drawnLayer.getBounds().isValid()) {
                map.fitBounds(window.drawnLayer.getBounds());
            }

            updateGeometryInput(window.drawnLayer);
            setActiveTool(initialToolType);
            showNotification('Geometri berhasil dimuat. Silakan edit atau gambar ulang.');
            
        } catch (e) {
            console.error("Initialization error:", e);
            setActiveTool('marker');
            showNotification('Gagal memuat geometri. Silakan gambar ulang.', 5000);
        }
    }

    toolbarButtons.forEach(button => {
        button.addEventListener('click', function() {
            const toolType = this.dataset.type;
            clearDrawing();
            setActiveTool(toolType);
            showNotification(`Mode gambar <strong>${toolType}</strong> aktif. Klik di peta untuk memulai.`);
        });
    });

    map.on('click', function(e) {
        if (!currentTool) {
            showNotification('Pilih alat gambar dari toolbar terlebih dahulu!');
            return;
        }

        const style = getStyleOptions();
        const latlng = e.latlng;

        if (currentTool === 'marker' || currentTool === 'circle') {
            clearDrawing();
            if (currentTool === 'marker') {
                const iconUrl = document.getElementById('icon_url').value;
                const icon = L.icon({ iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                window.drawnLayer = L.marker(latlng, { icon: icon }).addTo(map);
            } else { // Circle
                window.drawnLayer = L.circle(latlng, style).addTo(map);
            }
            manualLatInput.value = latlng.lat.toFixed(6);
            manualLngInput.value = latlng.lng.toFixed(6);
        } else if (currentTool === 'polygon' || currentTool === 'polyline') {
            polygonPoints.push(latlng);
            if (window.drawnLayer) {
                map.removeLayer(window.drawnLayer);
            }
            window.drawnLayer = (currentTool === 'polygon') 
                ? L.polygon(polygonPoints, style).addTo(map) 
                : L.polyline(polygonPoints, style).addTo(map);
            showNotification('Titik ditambahkan. Klik lagi untuk melanjutkan, atau pilih alat lain untuk menyelesaikan.');
        }
        
        updateGeometryInput(window.drawnLayer);
    });

    geojsonFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const geojson = JSON.parse(e.target.result);
                clearDrawing();
                const style = getStyleOptions();
                
                // {{-- PERBAIKAN: Logika upload GeoJSON disesuaikan agar lebih fleksibel --}}
                let featureGeometry = geojson;
                if (geojson.type === 'FeatureCollection') {
                    featureGeometry = geojson.features[0];
                }
                
                let detectedType = (featureGeometry.geometry?.type || featureGeometry.type).toLowerCase();
                if (detectedType.includes('point')) {
                    detectedType = 'marker'; // Default point to marker
                } else if (detectedType.includes('string')) {
                    detectedType = 'polyline';
                }
                setActiveTool(detectedType);

                window.drawnLayer = L.geoJSON(featureGeometry, { style }).addTo(map);
                map.fitBounds(window.drawnLayer.getBounds());
                updateGeometryInput(window.drawnLayer);
                showNotification('File GeoJSON berhasil dimuat!');

            } catch (error) {
                showNotification('Error: File GeoJSON tidak valid!', 5000);
                console.error('GeoJSON Parse Error:', error);
            }
        };
        reader.readAsText(file);
        geojsonFileInput.value = ''; // Reset file input
    });

    function handleManualCoordInput() {
        const lat = parseFloat(manualLatInput.value);
        const lng = parseFloat(manualLngInput.value);

        if (isNaN(lat) || isNaN(lng) || !currentTool || !['marker', 'circle'].includes(currentTool)) return;
        
        clearDrawing();
        const latlng = L.latLng(lat, lng);
        const style = getStyleOptions();

        if (currentTool === 'marker') {
            const iconUrl = document.getElementById('icon_url').value;
            const icon = L.icon({ iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
            window.drawnLayer = L.marker(latlng, { icon: icon }).addTo(map);
        } else { // Circle
            window.drawnLayer = L.circle(latlng, style).addTo(map);
        }
        
        updateGeometryInput(window.drawnLayer);
        map.setView(latlng, 15);
        showNotification('Geometri diperbarui dari input manual.');
    }

    manualLatInput.addEventListener('input', handleManualCoordInput);
    manualLngInput.addEventListener('input', handleManualCoordInput);

    // Jalankan inisialisasi peta
    initializeMap();
});
</script>
<script>
// Script untuk editor properties JSON
document.addEventListener('DOMContentLoaded', function(){
    const container = document.getElementById('properties-editor');
    const hiddenInput = document.getElementById('properties-json');
    const addBtn = document.getElementById('add-prop-btn');
    const form = document.getElementById('geometry-form');

    let props;
    try {
        props = JSON.parse('{!! old('properties', json_encode($props ?? [])) !!}');
    } catch (e) {
        props = {};
    }

    const reservedKeys = ['name', 'description', 'geometry_type', 'layer_id', 'radius'];

    function render() {
        container.innerHTML = '';
        const propEntries = Object.entries(props);

        if(propEntries.length === 0){
            container.innerHTML = '<p class="text-sm text-gray-500">Tidak ada properties tambahan.</p>';
        } else {
            propEntries.forEach(([key, val]) => {
                if (!reservedKeys.includes(key)) {
                    createRow(key, val);
                }
            });
        }
    }

    function createRow(key = '', val = ''){
        const row = document.createElement('div');
        row.className = 'flex gap-2 items-center';
        
        const keyInput = document.createElement('input');
        keyInput.value = key;
        keyInput.className = 'prop-key p-2 border rounded w-1/3 text-sm';
        keyInput.placeholder = 'Key';

        const valInput = document.createElement('input');
        valInput.value = (val === null || val === undefined) ? '' : String(val);
        valInput.className = 'prop-val p-2 border rounded flex-1 text-sm';
        valInput.placeholder = 'Value';

        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'text-sm text-red-600 hover:underline';
        delBtn.textContent = 'Hapus';
        delBtn.onclick = () => row.remove();
        
        row.append(keyInput, valInput, delBtn);
        container.appendChild(row);

        if (container.querySelector('p')) {
            container.querySelector('p').remove();
        }
    }

    addBtn.addEventListener('click', () => createRow());

    form.addEventListener('submit', () => {
        const finalProps = {};
        container.querySelectorAll('.flex.gap-2').forEach(row => {
            const key = row.querySelector('.prop-key').value.trim();
            const val = row.querySelector('.prop-val').value;
            if (key) {
                // Auto-detect type (number, boolean, string)
                if (val === 'true') finalProps[key] = true;
                else if (val === 'false') finalProps[key] = false;
                else if (val !== '' && !isNaN(val)) finalProps[key] = Number(val);
                else finalProps[key] = val;
            }
        });
        hiddenInput.value = JSON.stringify(finalProps);
    });

    render();
});
</script>
<script>
// Script untuk update style secara real-time
document.addEventListener('DOMContentLoaded', function () {
    function getStyleOptions() {
        return {
            color: document.getElementById('stroke_color').value,
            fillColor: document.getElementById('fill_color').value,
            fillOpacity: parseFloat(document.getElementById('opacity').value),
            weight: parseInt(document.getElementById('weight').value),
            radius: parseInt(document.getElementById('radius').value) || 300
        };
    }

    function updateLayerStyle() {
        if (!window.drawnLayer) return;

        const newStyles = getStyleOptions();
        
        const applyStyle = (layer) => {
            if (typeof layer.setStyle === 'function') {
                layer.setStyle(newStyles);
            }
            if (layer instanceof L.Circle) {
                layer.setRadius(newStyles.radius);
            }
            if (layer instanceof L.Marker) {
                const iconUrl = document.getElementById('icon_url').value;
                if (iconUrl) {
                    const newIcon = L.icon({ iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                    layer.setIcon(newIcon);
                }
            }
        };

        if (window.drawnLayer.eachLayer) {
            window.drawnLayer.eachLayer(applyStyle);
        } else {
            applyStyle(window.drawnLayer);
        }
    }

    ['stroke_color','fill_color','weight','opacity','radius','icon_url'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            const eventType = input.tagName.toLowerCase() === 'select' ? 'change' : 'input';
            input.addEventListener(eventType, updateLayerStyle);
        }
    });
});
</script>
@endsection