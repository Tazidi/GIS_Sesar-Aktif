@extends('layouts.app')

@section('title', 'Tambah Geometri - ' . $map->name)

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
        #dynamic-options-container .layer-dependent {
            display: none; /* Hide all options by default */
        }
    </style>
@endsection

@section('content')
<div class="container mx-auto py-8 px-4">
    {{-- Breadcrumb --}}
    <div class="mb-5">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('maps.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Maps</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('maps.geometries.index', $map) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Kelola Geometri</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Tambah Geometri</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <h1 class="text-3xl font-bold mb-4 text-gray-800">Tambah Geometri ke Map</h1>
    <p class="text-gray-600 mb-6">Map: <strong>{{ $map->name }}</strong></p>

    <form action="{{ route('maps.geometries.store', $map) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

                {{-- Left Column: Form Fields --}}
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Geometri</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('name') }}" placeholder="Contoh: Titik Survey A" required>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Deskripsi singkat tentang geometri ini">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih Layer</label>
                        <div class="mt-2 space-y-2">
                            @foreach ($layers as $layer)
                                <label class="flex items-center">
                                    <input type="checkbox" name="layer_ids[]" value="{{ $layer->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        {{ in_array($layer->id, old('layer_ids', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ $layer->nama_layer }}
                                        @if($layer->deskripsi) - {{ $layer->deskripsi }} @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Boleh pilih lebih dari satu layer</p>
                    </div>

                    {{-- Dynamic Options Container --}}
                    <div id="dynamic-options-container" class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-medium text-gray-900">Opsi Styling</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="layer-dependent" id="icon-field">
                                <label for="icon_url" class="block text-sm font-medium text-gray-700">Ikon Marker</label>
                                @php
                                    $selectedIcon = old('icon_url', $firstLayer->pivot->icon_url ?? '');
                                @endphp
                                <select name="icon_url" id="icon_url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
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
                            <div class="layer-dependent" id="radius-field">
                                <label for="radius" class="block text-sm font-medium text-gray-700">Radius (meter)</label>
                                <input type="number" name="radius" id="radius" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="200" min="1">
                            </div>
                            <div class="layer-dependent" id="stroke-color-field">
                                <label for="stroke_color" class="block text-sm font-medium text-gray-700">Warna Garis</label>
                                <input type="color" name="stroke_color" id="stroke_color" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm" value="#3388ff">
                            </div>
                            <div class="layer-dependent" id="fill-color-field">
                                <label for="fill_color" class="block text-sm font-medium text-gray-700">Warna Isi</label>
                                <input type="color" name="fill_color" id="fill_color" class="mt-1 block w-full h-10 border-gray-300 rounded-md shadow-sm" value="#3388ff">
                            </div>
                            <div class="layer-dependent" id="weight-field">
                                <label for="weight" class="block text-sm font-medium text-gray-700">Ketebalan Garis</label>
                                <input type="number" name="weight" id="weight" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="3" min="1" max="10">
                            </div>
                             <div class="layer-dependent" id="opacity-field">
                                <label for="opacity" class="block text-sm font-medium text-gray-700">Transparansi</label>
                                <input type="number" name="opacity" id="opacity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="0.5" min="0" max="1" step="0.1">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="feature_image" class="block text-sm font-medium text-gray-700">Gambar Fitur (opsional)</label>
                        <input type="file" name="feature_image" id="feature_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div>
                        <label for="caption" class="block text-sm font-medium text-gray-700">Caption Gambar</label>
                        <input type="text" name="caption" id="caption" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ old('caption') }}" placeholder="Caption untuk gambar fitur">
                    </div>

                    <div id="technical-info-wrapper" class="hidden border-t pt-4 space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 -mb-2">Info Teknis</h3>
                        <p class="text-sm text-gray-600">Properti teknis berikut diambil dari setiap fitur dalam file GeoJSON Anda.</p>
                        <div id="technical-info-container" class="space-y-4"></div>
                        <div id="show-more-container" class="mt-2"></div>
                   </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Peta Interaktif</label>
                        <div class="mb-2">
                            <select id="drawing-tool-select" class="block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="" disabled selected>-- Pilih Alat Gambar --</option>
                                <option value="marker">Marker / Titik (📍)</option>
                                <option value="polyline">Polyline / Garis (〰️)</option>
                                <option value="polygon">Polygon / Area (⬠)</option>
                                <option value="circle">Circle / Lingkaran (⭕)</option>
                            </select>
                        </div>
                        <div id="map" style="height: 400px; border: 1px solid #ccc; border-radius: 8px;"></div>
                        <div id="map-notification" class="mt-2 p-3 bg-blue-100 border border-blue-400 text-blue-700 rounded"></div>
                    </div>
                    <div>
                        <label for="geojson_file" class="block text-sm font-medium text-gray-700">ATAU Upload GeoJSON</label>
                        <input type="file" name="geojson_file" id="geojson_file" accept=".geojson,.json" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div id="feature-images-container" class="mt-6 space-y-4 hidden">
                        <label class="block text-sm font-medium text-gray-700">Gambar & Info Fitur</label>
                        <div id="feature-images-list" class="space-y-3"></div>
                        <div id="feature-images-showmore" class="mt-2"></div>
                    </div>
                    <input type="hidden" name="geometry" id="geometry-input" required>
                    <input type="hidden" name="geometry_type" id="geometry_type_input" required>
                    <input type="hidden" name="properties" id="properties-input">
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('maps.geometries.index', $map) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Simpan Geometri
                </button>
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

    const drawnItems = new L.FeatureGroup().addTo(map);

    let drawnLayer = null;
    let currentDrawingLayer = null;
    let polygonPoints = [];

    const form = document.querySelector('form');
    const geometryInput = document.getElementById('geometry-input');
    const geometryTypeInput = document.getElementById('geometry_type_input');
    const propertiesInput = document.getElementById('properties-input');
    const toolbarButtons = document.querySelectorAll('.draw-tool-btn');
    const geojsonFileInput = document.getElementById('geojson_file');
    const techInfoWrapper = document.getElementById('technical-info-wrapper');
    const techInfoContainer = document.getElementById('technical-info-container');
    const showMoreContainer = document.getElementById('show-more-container');

    const optionsContainer = document.getElementById('dynamic-options-container');
    const dynamicFields = { 
        icon_url: document.getElementById('icon-field'),
        radius: document.getElementById('radius-field'),
        stroke_color: document.getElementById('stroke-color-field'),
        fill_color: document.getElementById('fill-color-field'),
        weight: document.getElementById('weight-field'),
        opacity: document.getElementById('opacity-field')
    };

    function showNotification(message, duration = 4000) {
        const notification = document.getElementById('map-notification');
        notification.innerHTML = message;
        notification.classList.add('show');
        setTimeout(() => notification.classList.remove('show'), duration);
    }

    function getStyleOptions() {
        const style = {};
        const getValue = (selector, defaultValue, isFloat = false) => {
            const el = document.querySelector(selector);
            if (el && el.value !== '') {
                const val = isFloat ? parseFloat(el.value) : parseInt(el.value);
                return isNaN(val) ? defaultValue : val;
            }
            return defaultValue;
        };

        style.color = document.querySelector('[name="stroke_color"]').value || '#3388ff';
        style.fillColor = document.querySelector('[name="fill_color"]').value || '#3388ff';
        style.weight = getValue('[name="weight"]', 3);
        style.opacity = getValue('[name="opacity"]', 0.5, true);
        style.fillOpacity = style.opacity;
        style.radius = getValue('[name="radius"]', 200);
        return style;
    }

    function toggleFormFields(type) {
        for (const key in dynamicFields) {
            if (dynamicFields[key]) dynamicFields[key].style.display = 'none';
        }
        if (!type) return;

        const fieldsToShow = {
            marker: ['icon_url'],
            circle: ['radius', 'stroke_color', 'fill_color', 'weight', 'opacity'],
            polygon: ['stroke_color', 'fill_color', 'weight', 'opacity'],
            polyline: ['stroke_color', 'weight', 'opacity']
        };

        if (fieldsToShow[type]) {
            fieldsToShow[type].forEach(key => {
                if (dynamicFields[key]) dynamicFields[key].style.display = 'block';
            });
        }
    }
    
    function clearDrawing() {
        if (drawnLayer) map.removeLayer(drawnLayer);
        drawnLayer = null;
        polygonPoints = [];
        geometryInput.value = '';
        geojsonFileInput.value = '';
        propertiesInput.value = '';
        techInfoContainer.innerHTML = '';
        showMoreContainer.innerHTML = '';
        techInfoWrapper.classList.add('hidden');
    }

    function setActiveTool(tool) {
        clearDrawing();
        currentTool = tool;
        geometryTypeInput.value = tool;
        toolbarButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.type === tool));
        toggleFormFields(tool);
        showNotification(`Mode gambar <strong>${tool}</strong> aktif. Klik di peta untuk memulai.`);
    }

    function updateActiveToolUI(tool) {
        currentTool = tool;
        geometryTypeInput.value = tool;
        toolbarButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.type === tool));
        toggleFormFields(tool);
    }

    const toolSelect = document.getElementById('drawing-tool-select');
    toolSelect.addEventListener('change', function() {
        // Reset gambar yang sedang berlangsung (jika ada) saat ganti alat
        if (currentDrawingLayer) {
            map.removeLayer(currentDrawingLayer);
            currentDrawingLayer = null;
            polygonPoints = [];
        }
        
        currentTool = this.value;
        geometryTypeInput.value = currentTool;
        toggleFormFields(currentTool); // fungsi ini tetap berguna
        if (currentTool) {
            showNotification(`Mode gambar <strong>${currentTool}</strong> aktif. Klik di peta untuk memulai.`);
        }
    });
    map.on('click', function (e) {
        if (!currentTool) return;
        const style = getStyleOptions();
        let newLayer;

        if (currentTool === 'marker') {
            const iconUrl = document.querySelector('[name="icon_url"]').value;
            const icon = L.icon({ iconUrl: iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
            newLayer = L.marker(e.latlng, { icon });
            
            // Langsung tambahkan ke grup utama
            drawnItems.addLayer(newLayer);

            // Reset polygonPoints karena bukan polyline/polygon
            polygonPoints = [];

        } else if (currentTool === 'circle') {
            newLayer = L.circle(e.latlng, style);
            
            // Langsung tambahkan ke grup utama
            drawnItems.addLayer(newLayer);

            // Reset polygonPoints karena bukan polyline/polygon
            polygonPoints = [];

        } else if (['polygon', 'polyline'].includes(currentTool)) {
            polygonPoints.push(e.latlng);
            
            // Hapus layer temporer yang lama
            if (currentDrawingLayer) {
                map.removeLayer(currentDrawingLayer);
            }
            
            // Buat layer temporer yang baru dengan titik tambahan
            if (polygonPoints.length > 1) { // Hanya gambar jika ada minimal 2 titik
                currentDrawingLayer = (currentTool === 'polygon') 
                    ? L.polygon(polygonPoints, style).addTo(map) 
                    : L.polyline(polygonPoints, style).addTo(map);
            }
            
            // Beri tahu pengguna cara menyelesaikan gambar
            if (polygonPoints.length === 1) {
                showNotification('Titik pertama ditambahkan. Klik lagi untuk menambah titik, klik dua kali (double-click) pada titik terakhir untuk selesai.');
            }
        }

        // Update geometry_type_input setiap klik
        geometryTypeInput.value = currentTool;

        // Update geometryInput dengan drawnItems + currentDrawingLayer jika ada
        let geojsonToSet;
        if (currentDrawingLayer && ['polygon', 'polyline'].includes(currentTool)) {
            // Gabungkan drawnItems + currentDrawingLayer ke GeoJSON FeatureCollection
            const drawnGeoJSON = drawnItems.toGeoJSON();
            const currentGeoJSON = currentDrawingLayer.toGeoJSON();
            if (drawnGeoJSON.type === 'FeatureCollection') {
                drawnGeoJSON.features.push(currentGeoJSON);
                geojsonToSet = drawnGeoJSON;
            } else {
                geojsonToSet = currentGeoJSON;
            }
        } else {
            geojsonToSet = drawnItems.toGeoJSON();
        }
        geometryInput.value = JSON.stringify(geojsonToSet);
    });


    map.on('dblclick', function(e) {
        if (currentDrawingLayer && ['polygon', 'polyline'].includes(currentTool)) {
            // Hapus layer temporer
            map.removeLayer(currentDrawingLayer);
            currentDrawingLayer = null;

            // Buat layer final dan tambahkan ke grup utama
            const finalLayer = (currentTool === 'polygon')
                ? L.polygon(polygonPoints, getStyleOptions())
                : L.polyline(polygonPoints, getStyleOptions());
            
            drawnItems.addLayer(finalLayer);

            // Reset untuk gambar berikutnya
            polygonPoints = [];
            
            // Update hidden input
            geometryInput.value = JSON.stringify(drawnItems.toGeoJSON());
            geometryTypeInput.value = currentTool;
            showNotification(`Gambar <strong>${currentTool}</strong> selesai.`);
        }
    });

    form.addEventListener('submit', function(e) {
        // Jika ada gambar polyline/polygon yang belum diselesaikan, finalize dulu
        if (currentDrawingLayer && ['polygon', 'polyline'].includes(currentTool)) {
            finalizeDrawing();
        }
        // Update geometryInput dengan drawnItems GeoJSON terbaru
        geometryInput.value = JSON.stringify(drawnItems.toGeoJSON());
        // Panggil fungsi prepareAndSubmitData untuk set properties dll
        prepareAndSubmitData();
    });
    
    geojsonFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const geojson = JSON.parse(e.target.result);
                if (!geojson || geojson.type !== 'FeatureCollection' || !geojson.features || geojson.features.length === 0) {
                    showNotification('Error: File GeoJSON harus berformat FeatureCollection dan tidak boleh kosong.', 5000);
                    return;
                }

                clearDrawing();

                const firstFeature = geojson.features[0];
                const geomType = firstFeature.geometry.type.toLowerCase();
                let detectedToolType = '';

                const geomTypeLower = geomType.toLowerCase();
                if (geomTypeLower === 'point') {
                    detectedToolType = firstFeature.properties && firstFeature.properties.radius ? 'circle' : 'marker';
                } else if (geomTypeLower.includes('polygon')) {
                    detectedToolType = 'polygon';
                } else if (geomTypeLower.includes('linestring')) {
                    detectedToolType = 'polyline';
                } else if (geomTypeLower === 'circle') {
                    detectedToolType = 'circle';
                }

                if (!detectedToolType) {
                    showNotification(`Tipe geometri tidak didukung: ${geomType}`, 5000);
                    return;
                }

                updateActiveToolUI(detectedToolType);

                // render geojson ke peta (tetap pakai geoJSON group supaya fitBounds mudah)
                if (drawnLayer) map.removeLayer(drawnLayer);
                let layerRefs = [];
                drawnLayer = L.geoJSON(geojson, {
                    style: getStyleOptions,
                    pointToLayer: function(feature, latlng) {
                        if (detectedToolType === 'circle') {
                            const radius = (feature.properties && feature.properties.radius) || getStyleOptions().radius;
                            return L.circle(latlng, { ...getStyleOptions(), radius: radius });
                        }
                        const iconUrl = document.querySelector('[name="icon_url"]').value;
                        if (iconUrl) {
                            const icon = L.icon({ iconUrl: iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                            return L.marker(latlng, { icon: icon });
                        }
                        return L.marker(latlng);
                    },
                    onEachFeature: function(feature, layer) {
                        const index = geojson.features.indexOf(feature);
                        layerRefs[index] = layer;

                        // klik layer → scroll ke input
                        layer.on('click', () => {
                            const targetDiv = document.getElementById(`feature-input-${index}`);
                            if (targetDiv) {
                                targetDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                targetDiv.classList.add('ring-2', 'ring-blue-400');
                                setTimeout(() => targetDiv.classList.remove('ring-2', 'ring-blue-400'), 2000);
                            }
                        });
                    }
                }).addTo(map);

                try { map.fitBounds(drawnLayer.getBounds()); } catch(e){ /* ignore */ }

                // set geometry hidden input (kita kirim seluruh FeatureCollection ke backend)
                geometryInput.value = JSON.stringify(geojson);

                // --- NEW: Generate per-feature image & caption & technical inputs ---
                const featureImagesContainer = document.getElementById('feature-images-container');
                const featureImagesList = document.getElementById('feature-images-list');
                const featureImagesShowmore = document.getElementById('feature-images-showmore');

                featureImagesList.innerHTML = '';
                featureImagesShowmore.innerHTML = '';
                featureImagesContainer.classList.remove('hidden');

                geojson.features.forEach((feature, index) => {
                    const props = feature.properties || {};
                    const featureLabel = props.PopupInfo || props.Name || props.name || `Fitur #${index + 1}`;
                    const div = document.createElement('div');
                    div.className = 'feature-item flex flex-col mb-4 p-3 border border-gray-300 rounded-lg bg-gray-50';
                    div.id = `feature-input-${index}`;
                    if (index > 0) div.classList.add('hidden', 'extra-feature-input');

                    div.innerHTML = `
                        <label class="text-sm font-medium text-gray-700 mb-1">Gambar untuk: ${featureLabel}</label>
                        <input type="file" name="feature_images[${index}]" accept="image/*" class="form-input mb-2">
                        <input type="text" name="feature_captions[${index}]" placeholder="Caption foto (opsional)" class="mb-2 block w-full text-sm border-gray-300 rounded-md shadow-sm">
                        <div class="mt-2 border-t pt-2">
                            <label class="block text-xs text-gray-600">Panjang Sesar</label>
                            <input type="text" name="feature_properties[${index}][panjang_sesar]" value="${props.panjang_sesar || ''}" class="mb-2 w-full text-sm border-gray-300 rounded-md shadow-sm">
                            <label class="block text-xs text-gray-600">Lebar Sesar</label>
                            <input type="text" name="feature_properties[${index}][lebar_sesar]" value="${props.lebar_sesar || ''}" class="mb-2 w-full text-sm border-gray-300 rounded-md shadow-sm">
                            <label class="block text-xs text-gray-600">Tipe</label>
                            <input type="text" name="feature_properties[${index}][tipe]" value="${props.tipe || ''}" class="mb-2 w-full text-sm border-gray-300 rounded-md shadow-sm">
                            <label class="block text-xs text-gray-600">MMAX</label>
                            <input type="text" name="feature_properties[${index}][mmax]" value="${props.mmax || ''}" class="mb-2 w-full text-sm border-gray-300 rounded-md shadow-sm">
                            <button type="button" class="hapus-fitur text-xs text-red-600 hover:text-red-800">Hapus fitur ini</button>
                        </div>
                    `;
                    featureImagesList.appendChild(div);
                    div.querySelector('.hapus-fitur').addEventListener('click', () => {
                        // hapus input
                        div.remove();
                        // hapus layer dari peta
                        if (layerRefs[index]) {
                            map.removeLayer(layerRefs[index]);
                            delete geojson.features[index]; // optional: hilangkan dari geojson juga
                        }
                    });
                });

                // jika lebih dari 1 fitur, buat tombol "Tampilkan n data lainnya"
                if (geojson.features.length > 1) {
                    const remaining = geojson.features.length - 1;
                    const showMoreBtn = document.createElement('button');
                    showMoreBtn.type = 'button';
                    showMoreBtn.className = 'text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline';
                    showMoreBtn.textContent = `Tampilkan ${remaining} data lainnya...`;
                    showMoreBtn.addEventListener('click', () => {
                        document.querySelectorAll('.extra-feature-input').forEach(el => el.classList.remove('hidden'));
                        showMoreBtn.remove();
                    });
                    featureImagesShowmore.appendChild(showMoreBtn);
                }

                // Jika GeoJSON memiliki properti name di fitur pertama, isi nama form
                if (geojson.features[0].properties) {
                    document.getElementById('name').value = geojson.features[0].properties.name || document.getElementById('name').value;
                }

                // tetap tampilkan info teknis (yang sudah ada di kode kamu)
                let techInfoHtml = '';
                geojson.features.forEach((feature, index) => {
                    const props = feature.properties || {};
                    const extraClasses = index > 0 ? 'hidden extra-tech-info' : '';
                    techInfoHtml += `
                        <div class="tech-info-block p-4 border rounded-md bg-gray-50 shadow-sm ${extraClasses}">
                            <p class="text-sm font-semibold text-gray-800 mb-2">Info Teknis untuk Fitur #${index + 1}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label for="panjang_sesar_${index}" class="block text-sm font-medium text-gray-700">Panjang Sesar</label><input type="text" name="technical_info[${index}][panjang_sesar]" id="panjang_sesar_${index}" value="${props.panjang_sesar || ''}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                                <div><label for="lebar_sesar_${index}" class="block text-sm font-medium text-gray-700">Lebar Sesar</label><input type="text" name="technical_info[${index}][lebar_sesar]" id="lebar_sesar_${index}" value="${props.lebar_sesar || ''}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                                <div><label for="tipe_${index}" class="block text-sm font-medium text-gray-700">Tipe</label><input type="text" name="technical_info[${index}][tipe]" id="tipe_${index}" value="${props.tipe || ''}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                                <div><label for="mmax_${index}" class="block text-sm font-medium text-gray-700">MMAX</label><input type="text" name="technical_info[${index}][mmax]" id="mmax_${index}" value="${props.mmax || ''}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                            </div>
                        </div>
                    `;
                });
                techInfoContainer.innerHTML = techInfoHtml;
                techInfoWrapper.classList.remove('hidden');

                showNotification(`File GeoJSON dengan ${geojson.features.length} fitur berhasil dimuat!`);
            } catch (error) {
                console.error(error);
                showNotification('Error: Gagal memproses file GeoJSON!', 5000);
            }
        };
        reader.readAsText(file);
    });

    function updateLayerStyle() {
        if (!drawnLayer) return;

        const newStyles = getStyleOptions();
        const iconUrl = document.querySelector('[name="icon_url"]').value;

        const applyStyle = (layer) => {
            if (layer.setStyle) {
                layer.setStyle(newStyles);
            }
            // Untuk marker, update icon jika diganti
            if (layer instanceof L.Marker && iconUrl) {
                const newIcon = L.icon({ iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                layer.setIcon(newIcon);
            }
        };

        if (drawnLayer.eachLayer) {
            drawnLayer.eachLayer(applyStyle);
        } else {
            applyStyle(drawnLayer);
        }
    }
    
    const styleInputs = [
        'input[name="stroke_color"]',
        'input[name="fill_color"]',
        'input[name="weight"]',
        'input[name="opacity"]',
        'input[name="radius"]',
        'select[name="icon_url"]'
    ];

    styleInputs.forEach(selector => {
        const input = document.querySelector(selector);
        if (input) {
            const eventType = input.tagName.toLowerCase() === 'select' ? 'change' : 'input';
            input.addEventListener(eventType, updateLayerStyle);
        }
    });

    function prepareAndSubmitData() {
        let properties = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value,
            layer_id: document.getElementById('layer_id').value,
        };
        
        // Add styling properties based on geometry type
        const geometryType = document.getElementById('geometry_type_input').value;
        
        if (geometryType === 'marker') {
            properties.icon_url = document.querySelector('[name="icon_url"]').value;
        } else if (geometryType === 'polyline') {
            properties.stroke_color = document.querySelector('[name="stroke_color"]').value;
            properties.weight = document.querySelector('[name="weight"]').value;
            properties.opacity = document.querySelector('[name="opacity"]').value;
        } else if (geometryType === 'polygon') {
            properties.stroke_color = document.querySelector('[name="stroke_color"]').value;
            properties.fill_color = document.querySelector('[name="fill_color"]').value;
            properties.weight = document.querySelector('[name="weight"]').value;
            properties.opacity = document.querySelector('[name="opacity"]').value;
        } else if (geometryType === 'circle') {
            properties.radius = document.querySelector('[name="radius"]').value;
            properties.stroke_color = document.querySelector('[name="stroke_color"]').value;
            properties.fill_color = document.querySelector('[name="fill_color"]').value;
            properties.weight = document.querySelector('[name="weight"]').value;
            properties.opacity = document.querySelector('[name="opacity"]').value;
        }
        
        // Collect technical info only if user has entered values
        const technicalInfo = [];
        document.querySelectorAll('.tech-info-block').forEach((block, index) => {
            const panjangSesar = block.querySelector(`[name="technical_info[${index}][panjang_sesar]"]`).value;
            const lebarSesar = block.querySelector(`[name="technical_info[${index}][lebar_sesar]"]`).value;
            const tipe = block.querySelector(`[name="technical_info[${index}][tipe]"]`).value;
            const mmax = block.querySelector(`[name="technical_info[${index}][mmax]"]`).value;
            
            // Only add if at least one field has a value
            if (panjangSesar || lebarSesar || tipe || mmax) {
                technicalInfo.push({
                    panjang_sesar: panjangSesar,
                    lebar_sesar: lebarSesar,
                    tipe: tipe,
                    mmax: mmax,
                });
            }
        });
        
        // Add technical info to form data if it exists
        if (technicalInfo.length > 0) {
            // Create hidden input for technical info
            let techInfoInput = document.createElement('input');
            techInfoInput.type = 'hidden';
            techInfoInput.name = 'technical_info';
            techInfoInput.value = JSON.stringify(technicalInfo);
            form.appendChild(techInfoInput);
        }

        document.querySelectorAll('#dynamic-options-container input, #dynamic-options-container select')
            .forEach(el => {
                el.addEventListener('change', () => {
                    drawnItems.eachLayer(layer => {
                        if (layer.setStyle) {
                            // untuk circle, polygon, polyline
                            layer.setStyle(getStyleOptions());
                        } else if (layer.setIcon) {
                            // untuk marker
                            const iconUrl = document.querySelector('[name="icon_url"]').value;
                            const icon = L.icon({ iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                            layer.setIcon(icon);
                        }
                    });
                    geometryInput.value = JSON.stringify(drawnItems.toGeoJSON());
                });
            });
        
        propertiesInput.value = JSON.stringify(properties);
    }

    function appendHidden(name, value) {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    form.addEventListener('submit', prepareAndSubmitData);
    toggleFormFields(null); // Hide all fields on initial load
});
</script>
@endsection