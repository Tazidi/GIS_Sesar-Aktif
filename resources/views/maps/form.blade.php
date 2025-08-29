@extends('layouts.app')

@section('title', $map->exists ? 'Edit Peta' : 'Tambah Peta Baru')

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
    </style>
@endsection

@section('content')
<div class="container mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-4 text-gray-800">{{ $map->exists ? 'Edit Peta' : 'Tambah Peta' }}</h1>

    <form action="{{ $map->exists ? route('maps.update', $map) : route('maps.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($map->exists)
        @method('PUT')
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
        {{-- Grid Layout Utama --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Judul Map</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('name', $map->name) }}" required>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" rows="4">{{ old('description', $map->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Pilih Layer</label>
                    <div class="mt-2 space-y-2 border border-gray-200 rounded-md p-4 max-h-48 overflow-y-auto">
                        @foreach ($layers as $layer)
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="layers[]"
                                    value="{{ $layer->id }}" 
                                    id="layer-{{ $layer->id }}" 
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    @if(is_array(old('layers')) && in_array($layer->id, old('layers')))
                                        checked
                                    @elseif($map->exists && $map->layers->contains($layer->id))
                                        checked 
                                    @endif
                                >
                                <label for="layer-{{ $layer->id }}" class="ml-3 block text-sm text-gray-800">
                                    {{ $layer->nama_layer }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="kategori" class="block text-sm font-medium text-gray-700">Tampil di Peta Sisiraja?</label>
                    <select name="kategori" id="kategori" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="Ya" {{ old('kategori', $map->kategori) == 'Ya' ? 'selected' : '' }}>Ya</option>
                        <option value="Tidak" {{ old('kategori', $map->kategori) == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                    <div>
                        <label for="image_path" class="block text-sm font-medium text-gray-700">Upload Gambar (Thumbnail)</label>
                        <input type="file" name="image_path" id="image_path" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        
                        <div id="image-preview-container" class="mt-2">
                            <img id="image-preview" src="#" alt="Image preview" class="w-full rounded-md shadow-sm" style="display: none;"/>
                            @if ($map->exists && $map->image_path)
                                <img id="existing-image" src="{{ asset('map_images/' . $map->image_path) }}" alt="Gambar lama" class="w-full rounded-md shadow-sm">
                            @endif
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="geojson_file" class="block text-sm font-medium text-gray-700">Upload GeoJSON</label>
                        <input type="file" name="geojson_file" id="geojson_file" accept=".geojson,.json"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div id="feature-images-container" class="mt-6 space-y-4 hidden">
                        <label class="block text-sm font-medium text-gray-700">Upload Gambar untuk Tiap Fitur</label>
                        <div id="feature-images-list" class="space-y-3"></div>
                    </div>
                </div>
            </div>

            <div>
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700">Gambar Fitur di Peta</label>
                    
                    <div id="drawing-toolbar" class="mt-1 mb-2 p-1 bg-gray-100 border border-gray-200 rounded-md inline-flex items-center space-x-1">
                        <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="marker" title="Marker">📍</button>
                        <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="polyline" title="Polyline">〰️</button>
                        <button type="button" class="draw-tool-btn px-3 py-2 rounded hover:bg-gray-200 text-sm" data-type="polygon" title="Polygon">⬠</button>
                        <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="circle" title="Circle">⭕</button>
                    </div>

                    {{-- MODIFIED: Wrapper for Point (Marker/Circle) manual input --}}
                    <div id="point-manual-input-wrapper" class="my-2">
                        <button type="button" id="toggle-manual-coords" class="text-sm text-blue-600 hover:text-blue-800 font-semibold focus:outline-none">
                            Tambahkan Latitude & Longitude Manual
                        </button>
                        <div id="manual-coords-container" class="hidden grid grid-cols-2 gap-4 mt-2 p-3 border border-dashed rounded-md">
                            <div>
                                <label for="manual-lat" class="block text-xs font-medium text-gray-600">Latitude</label>
                                <input type="number" step="any" id="manual-lat" class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="manual-lng" class="block text-xs font-medium text-gray-600">Longitude</label>
                                <input type="number" step="any" id="manual-lng" class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                        </div>
                    </div>
                    
                    {{-- NEW: Container for Polygon/Polyline vertex editing on Edit Mode --}}
                    <div id="poly-manual-input-container" class="my-2 hidden">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Edit Titik Koordinat</h4>
                        <div id="poly-coords-list" class="space-y-2 max-h-48 overflow-y-auto p-2 border border-dashed rounded-md bg-gray-50">
                            {{-- Dynamic vertex inputs will be injected here by JavaScript --}}
                        </div>
                        <button type="button" id="add-poly-point" class="mt-2 text-xs text-blue-600 hover:text-blue-800 font-semibold">+ Tambah Titik</button>
                    </div>
                    {{-- END NEW --}}

                    <div id="select-map" class="w-full h-[450px] rounded-md border border-gray-300 shadow-sm"></div>
                    <p class="text-xs text-gray-500 mt-1">Pilih alat gambar, lalu klik di peta atau masukkan koordinat manual.</p>
                </div>
            </div>
        </div>

        @php
            $firstLayer = $map->layers->first();
        @endphp

        <div id="dynamic-options-container" class="mt-6 border border-gray-300 rounded-md p-4 space-y-4" style="display: none;">
            <h3 class="text-sm font-medium text-gray-900">Opsi Fitur Terpilih</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="layer-dependent" id="radius-field">
                    <label class="block text-sm font-medium text-gray-700">Radius (m)</label>
                    <input type="number" step="1" name="radius"
                        value="{{ old('radius', $firstLayer->pivot->radius ?? 300) }}"
                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="layer-dependent" id="weight-field">
                    <label class="block text-sm font-medium text-gray-700">Tebal Garis</label>
                    <input type="number" step="1" min="0" name="weight"
                        value="{{ old('weight', $firstLayer->pivot->weight ?? 3) }}"
                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="layer-dependent" id="opacity-field">
                    <label class="block text-sm font-medium text-gray-700">Opacity (0-1)</label>
                    <input type="number" step="0.1" max="1" min="0" name="opacity"
                        value="{{ old('opacity', $firstLayer->pivot->opacity ?? 0.5) }}"
                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="layer-dependent" id="stroke-field">
                    <label class="block text-sm font-medium text-gray-700">Warna Garis</label>
                    <input type="color" name="stroke_color"
                        value="{{ old('stroke_color', $firstLayer->pivot->stroke_color ?? '#3388ff') }}"
                        class="mt-1 h-8 w-full border-gray-300 rounded-md">
                </div>
                <div class="layer-dependent" id="fill-field">
                    <label class="block text-sm font-medium text-gray-700">Warna Isi</label>
                    <input type="color" name="fill_color"
                        value="{{ old('fill_color', $firstLayer->pivot->fill_color ?? '#3388ff') }}"
                        class="mt-1 h-8 w-full border-gray-300 rounded-md">
                </div>
                <div class="layer-dependent" id="icon-field">
                    <label class="block text-sm font-medium text-gray-700">Ikon Marker</label>
                    @php
                        $selectedIcon = old('icon_url', $firstLayer->pivot->icon_url ?? '');
                    @endphp
                    <select name="icon_url" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
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
            </div>
        </div>

        <input type="hidden" name="geometry" id="geometry" value="{{ old('geometry', $map->geometry) }}">
        <input type="hidden" name="layer_type" id="layer_type" value="{{ old('layer_type', $map->layers->first()->pivot->layer_type ?? 'marker') }}">

        {{-- Footer Form dengan Tombol Simpan dan Batal --}}
        <div class="mt-8 pt-5 border-t border-gray-200">
            <div class="flex justify-end items-center">
                <a href="{{ route('maps.index') }}" class="text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-3">
                    Batal
                </a>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ $map->exists ? 'Perbarui Peta' : 'Simpan Peta' }}
                </button>
            </div>
        </div>
    </div>
</form>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isEditMode = {{ $map->exists ? 'true' : 'false' }};
    const map = L.map('select-map').setView([-7.5, 107.5], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let drawnLayer = null, polygonPoints = [], currentLayerType = "{{ old('layer_type', $map->layers->first()->pivot->layer_type ?? 'marker') }}";

    const geometryInput = document.getElementById('geometry');
    const layerTypeInput = document.getElementById('layer_type');
    const toolbarButtons = document.querySelectorAll('.draw-tool-btn');
    const optionsContainer = document.getElementById('dynamic-options-container');
    const dynamicFields = { 
        radius: document.getElementById('radius-field'), 
        icon: document.getElementById('icon-field'), 
        stroke: document.getElementById('stroke-field'), 
        fill: document.getElementById('fill-field'), 
        opacity: document.getElementById('opacity-field'), 
        weight: document.getElementById('weight-field') 
    };

    const imageInput = document.getElementById('image_path');
    const imagePreview = document.getElementById('image-preview');
    const existingImage = document.getElementById('existing-image');
    const geojsonInput = document.getElementById('geojson_file');
    
    // {{-- Get DOM elements for manual coordinates --}}
    const pointInputWrapper = document.getElementById('point-manual-input-wrapper');
    const toggleCoordsBtn = document.getElementById('toggle-manual-coords');
    const manualCoordsContainer = document.getElementById('manual-coords-container');
    const manualLatInput = document.getElementById('manual-lat');
    const manualLngInput = document.getElementById('manual-lng');
    
    // {{-- NEW: Get DOM elements for polygon/polyline vertex editor --}}
    const polyInputContainer = document.getElementById('poly-manual-input-container');
    const polyCoordsList = document.getElementById('poly-coords-list');
    const addPolyPointBtn = document.getElementById('add-poly-point');


    // {{-- Function to create a point layer from manual lat/lng input --}}
    function updateMapFromManualCoords() {
        const lat = parseFloat(manualLatInput.value);
        const lng = parseFloat(manualLngInput.value);

        if (!isNaN(lat) && !isNaN(lng)) {
            if (currentLayerType !== 'marker' && currentLayerType !== 'circle') {
                alert('Input manual hanya untuk tipe Marker atau Circle. Silakan pilih salah satu alat tersebut.');
                return;
            }

            clearDrawing();
            const latlng = L.latLng(lat, lng);
            
            const style = getStyleOptions();
            
            if (currentLayerType === 'marker') {
                const iconUrl = document.querySelector('select[name="icon_url"]').value;
                if (iconUrl) {
                    const icon = L.icon({ iconUrl: iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                    drawnLayer = L.marker(latlng, { icon }).addTo(map);
                } else {
                    drawnLayer = L.circleMarker(latlng, { ...style, radius: 5 }).addTo(map);
                }
            } else if (currentLayerType === 'circle') {
                drawnLayer = L.circle(latlng, { ...style, weight: 1 }).addTo(map);
            }

            if (drawnLayer) {
                const geojson = drawnLayer.toGeoJSON().geometry;
                geometryInput.value = JSON.stringify(geojson);
                map.panTo(latlng);
            }
        }
    }

    // {{-- Helper function to get current style options from the form --}}
    function getStyleOptions() {
        return {
            color: document.querySelector('input[name="stroke_color"]').value || '#3388ff',
            fillColor: document.querySelector('input[name="fill_color"]').value || '#3388ff',
            fillOpacity: parseFloat(document.querySelector('input[name="opacity"]').value) || 0.5,
            weight: parseInt(document.querySelector('input[name="weight"]').value) || 3,
            radius: parseInt(document.querySelector('input[name="radius"]').value) || 300
        };
    }

    // Function to extract center from any geometry type
    function extractCenterFromGeometry(geometry) {
        if (!geometry || !geometry.coordinates) return null;
        if (geometry.type === 'Point') {
            return { lat: geometry.coordinates[1], lng: geometry.coordinates[0] };
        }
        
        const coordinates = geometry.coordinates;
        let lats = [];
        let lngs = [];
        
        const flattenCoords = (arr) => {
            arr.forEach(item => {
                if (Array.isArray(item) && typeof item[0] === 'number' && typeof item[1] === 'number') {
                    lngs.push(item[0]);
                    lats.push(item[1]);
                } else if (Array.isArray(item)) {
                    flattenCoords(item);
                }
            });
        };
        
        flattenCoords(coordinates);
        
        if (lats.length > 0 && lngs.length > 0) {
            return {
                lat: lats.reduce((a, b) => a + b, 0) / lats.length,
                lng: lngs.reduce((a, b) => a + b, 0) / lngs.length
            };
        }
        
        return null;
    }

    if (geojsonInput) {
        geojsonInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                try {
                    const geojson = JSON.parse(e.target.result);
                    if (!geojson || geojson.type !== 'FeatureCollection' || !geojson.features || geojson.features.length === 0) {
                        alert('File GeoJSON tidak valid atau kosong!');
                        return;
                    }

                    clearDrawing(); 

                    const firstFeature = geojson.features[0];
                    const featureType = firstFeature.geometry.type.toLowerCase();
                    let toolType = '';

                    if (featureType === 'point') {
                        toolType = 'circle';
                    } else if (featureType.includes('polygon')) { 
                        toolType = 'polygon';
                    } else if (featureType.includes('linestring')) { 
                        toolType = 'polyline';
                    }

                    if (!toolType) {
                        alert('Tipe geometri tidak didukung: ' + featureType);
                        return;
                    }

                    const styleOptions = getStyleOptions();

                    drawnLayer = L.geoJSON(geojson, {
                        style: function(feature) { return styleOptions; },
                        pointToLayer: function (feature, latlng) {
                            if (toolType === 'circle') {
                                const radius = feature.properties.radius || parseInt(document.querySelector('input[name="radius"]').value) || 500;
                                return L.circle(latlng, { ...styleOptions, radius: radius });
                            }
                            const iconUrl = document.querySelector('select[name="icon_url"]')?.value || '';
                            if (iconUrl) {
                                const icon = L.icon({ iconUrl: iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                                return L.marker(latlng, { icon: icon });
                            }
                            return L.circleMarker(latlng, { ...styleOptions, radius: 5 });
                        }
                    }).addTo(map);

                    map.fitBounds(drawnLayer.getBounds());

                    const featureImagesContainer = document.getElementById('feature-images-container');
                    const featureImagesList = document.getElementById('feature-images-list');
                    featureImagesList.innerHTML = '';
                    const existingExpandButton = featureImagesContainer.querySelector('.expand-button');
                    if (existingExpandButton) existingExpandButton.remove();
                    
                    featureImagesContainer.classList.remove('hidden');

                    geojson.features.forEach((feature, index) => {
                        const div = document.createElement('div');
                        div.className = 'feature-item flex flex-col mb-4 p-3 border border-gray-300 rounded-lg bg-gray-50';
                        if (index > 0) div.classList.add('hidden');

                        const props = feature.properties || {};
                        const featureLabel = props.PopupInfo || props.Name || `Fitur #${index + 1}`;
                        const label = document.createElement('label');
                        label.textContent = `Gambar untuk: ${featureLabel}`;
                        label.className = 'text-sm font-medium text-gray-700 mb-1';
                        div.appendChild(label);

                        const input = document.createElement('input');
                        input.type = 'file';
                        input.name = 'feature_images[]';
                        input.accept = 'image/*';
                        input.className = 'form-input mb-2';
                        div.appendChild(input);

                        const captionInput = document.createElement('input');
                        captionInput.type = 'text';
                        captionInput.name = `feature_captions[${index}]`;
                        captionInput.placeholder = 'Caption foto (opsional)';
                        captionInput.className = 'mb-2 block w-full text-sm text-gray-600 border-gray-300 rounded-md shadow-sm';
                        div.appendChild(captionInput);

                        const createTechInput = (labelText, name, placeholder, parent) => {
                            const label = document.createElement('label');
                            label.textContent = labelText;
                            label.className = 'text-xs font-medium text-gray-600';
                            parent.appendChild(label);
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.name = name;
                            input.placeholder = placeholder;
                            input.className = 'mt-1 mb-2 block w-full text-sm text-gray-600 border-gray-300 rounded-md shadow-sm';
                            parent.appendChild(input);
                        };

                        const techFieldsWrapper = document.createElement('div');
                        techFieldsWrapper.className = 'mt-2 border-t pt-2';
                        const techHeader = document.createElement('p');
                        techHeader.textContent = `Informasi Teknis untuk: ${featureLabel}`;
                        techHeader.className = 'text-sm font-medium text-gray-700 mb-1';
                        div.appendChild(techHeader);
                        createTechInput('Panjang Sesar', `feature_properties[${index}][panjang_sesar]`, 'Contoh: 10 km', techFieldsWrapper);
                        createTechInput('Lebar Sesar', `feature_properties[${index}][lebar_sesar]`, 'Contoh: 5 m', techFieldsWrapper);
                        createTechInput('Tipe', `feature_properties[${index}][tipe]`, 'Contoh: Sesar Naik', techFieldsWrapper);
                        createTechInput('MMAX', `feature_properties[${index}][mmax]`, 'Contoh: 6.5', techFieldsWrapper);
                        div.appendChild(techFieldsWrapper);
                        featureImagesList.appendChild(div);
                    });

                    if (geojson.features.length > 1) {
                        const expandButton = document.createElement('button');
                        expandButton.type = 'button';
                        expandButton.textContent = `Tampilkan ${geojson.features.length - 1} Fitur Lainnya...`;
                        expandButton.className = 'expand-button mt-2 text-sm text-blue-600 hover:text-blue-800 font-semibold focus:outline-none';
                        expandButton.addEventListener('click', function() {
                            featureImagesList.querySelectorAll('.feature-item.hidden').forEach(item => { item.classList.remove('hidden'); });
                            this.style.display = 'none';
                        });
                        featureImagesContainer.appendChild(expandButton);

                    }

                    geometryInput.value = JSON.stringify(geojson);
                    setActiveTool(toolType);
                    toggleFormFields(toolType);

                } catch (err) {
                    console.error("Error parsing GeoJSON:", err);
                    alert('Gagal membaca atau mem-parsing file GeoJSON. Pastikan formatnya benar.');
                }
            };
            reader.readAsText(file);
        });
    }

    imageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.style.display = 'block';
                imagePreview.src = e.target.result;
                if (existingImage) existingImage.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });

    // {{-- Event listeners for manual coordinate controls --}}
    toggleCoordsBtn.addEventListener('click', function() {
        const isHidden = manualCoordsContainer.classList.toggle('hidden');
        this.textContent = isHidden ? 'Tambahkan Latitude & Longitude Manual' : 'Sembunyikan Input Manual';
    });
    manualLatInput.addEventListener('input', updateMapFromManualCoords);
    manualLngInput.addEventListener('input', updateMapFromManualCoords);

    function setActiveTool(type) {
        toolbarButtons.forEach(btn => btn.classList.toggle('bg-blue-200', btn.dataset.type === type));
        currentLayerType = type;
        layerTypeInput.value = type;
        if (type !== 'marker') {
            const iconSelect = document.querySelector('select[name="icon_url"]');
            if (iconSelect) iconSelect.value = '';
        }
        toggleFormFields(type);
        // {{-- MODIFIED: Also control visibility of manual inputs --}}
        toggleManualInputVisibility(type);
    }
    
    // {{-- NEW: Function to control which manual input is shown --}}
    function toggleManualInputVisibility(type) {
        if (type === 'marker' || type === 'circle') {
            pointInputWrapper.style.display = 'block';
            polyInputContainer.classList.add('hidden');
        } else if (type === 'polygon' || type === 'polyline') {
            pointInputWrapper.style.display = 'none';
            // Only show the vertex editor when in edit mode
            if (isEditMode) {
                polyInputContainer.classList.remove('hidden');
            } else {
                polyInputContainer.classList.add('hidden');
            }
        } else {
            // Hide both for safety if no type is selected
            pointInputWrapper.style.display = 'none';
            polyInputContainer.classList.add('hidden');
        }
    }

    function toggleFormFields(type) {
        optionsContainer.style.display = 'none';
        for (const key in dynamicFields) {
            if (dynamicFields[key]) dynamicFields[key].style.display = 'none';
        }
        if (!type) return;

        optionsContainer.style.display = 'block';
        const fieldsToShow = {
            marker: ['icon'],
            circle: ['radius', 'stroke', 'fill', 'opacity', 'weight'],
            polygon: ['stroke', 'fill', 'opacity', 'weight'],
            polyline: ['stroke', 'opacity', 'weight']
        };
        if (fieldsToShow[type]) {
            fieldsToShow[type].forEach(key => dynamicFields[key] && (dynamicFields[key].style.display = 'block'));
        }
        if (type !== 'marker' && dynamicFields.icon) {
            dynamicFields.icon.style.display = 'none';
        }
    }

    function clearDrawing() {
        if (drawnLayer) map.removeLayer(drawnLayer);
        drawnLayer = null; 
        polygonPoints = []; 
        geometryInput.value = '';
        
        // {{-- MODIFIED: Clear and reset all manual inputs --}}
        manualLatInput.value = '';
        manualLngInput.value = '';
        polyCoordsList.innerHTML = '';
        polyInputContainer.classList.add('hidden');
        pointInputWrapper.style.display = 'block'; // Default state
        manualCoordsContainer.classList.add('hidden');
        toggleCoordsBtn.textContent = 'Tambahkan Latitude & Longitude Manual';
    }

    toolbarButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            setActiveTool(type); 
            clearDrawing(); 
        });
    });

    map.on('click', function (e) {
        if (!currentLayerType) return;
        
        const style = getStyleOptions();

        if (currentLayerType === 'marker' || currentLayerType === 'circle') {
            clearDrawing();
            if (currentLayerType === 'marker') {
                const iconUrl = document.querySelector('select[name="icon_url"]').value;
                if (iconUrl) {
                    const icon = L.icon({ iconUrl: iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                    drawnLayer = L.marker(e.latlng, { icon }).addTo(map);
                } else {
                    drawnLayer = L.circleMarker(e.latlng, { ...style, radius: 5 }).addTo(map);
                }
            } else {
                drawnLayer = L.circle(e.latlng, { ...style, weight: 1 }).addTo(map);
            }
            // {{-- Update manual inputs when clicking the map --}}
            manualLatInput.value = e.latlng.lat.toFixed(6);
            manualLngInput.value = e.latlng.lng.toFixed(6);

        } else if (['polygon', 'polyline'].includes(currentLayerType)) {
            polygonPoints.push(e.latlng);
            if (drawnLayer) map.removeLayer(drawnLayer);
            if (currentLayerType === 'polygon') {
                drawnLayer = L.polygon(polygonPoints, style).addTo(map);
            } else {
                drawnLayer = L.polyline(polygonPoints, style).addTo(map);
            }
        }

        if (drawnLayer) {
            const geojson = drawnLayer.toGeoJSON().geometry;
            geometryInput.value = JSON.stringify(geojson);
        }
    });

    // --- NEW: Functions for managing Polygon/Polyline vertex editor ---
    
    function updateGeometryFromPolyInputs() {
        const rows = polyCoordsList.querySelectorAll('.poly-coord-row');
        const newCoords = [];
        rows.forEach(row => {
            const lat = parseFloat(row.querySelector('.poly-lat-input').value);
            const lng = parseFloat(row.querySelector('.poly-lng-input').value);
            if (!isNaN(lat) && !isNaN(lng)) {
                newCoords.push([lng, lat]); // GeoJSON format is [lng, lat]
            }
        });

        if (drawnLayer && newCoords.length > 0) {
            let finalCoords = newCoords;
            let leafletCoords;

            if (currentLayerType === 'polygon') {
                if (newCoords.length >= 3) {
                    // Create a copy to avoid modifying the original array in a loop
                    const closedCoords = [...newCoords, [...newCoords[0]]];
                    leafletCoords = L.GeoJSON.coordsToLatLngs(closedCoords, 1);
                    drawnLayer.setLatLngs(leafletCoords);
                }
            } else { // Polyline
                if (newCoords.length >= 2) {
                    leafletCoords = L.GeoJSON.coordsToLatLngs(newCoords);
                    drawnLayer.setLatLngs(leafletCoords);
                }
            }

            if (drawnLayer.getLatLngs().length > 0) {
                 const geojson = drawnLayer.toGeoJSON().geometry;
                 geometryInput.value = JSON.stringify(geojson);
            }
        }
    }

    function populatePolyCoords(geometry) {
        polyCoordsList.innerHTML = '';
        if (!geometry || !geometry.coordinates) return;

        const coords = (geometry.type === 'Polygon') ? geometry.coordinates[0] : geometry.coordinates;
        // For polygons, don't show the redundant closing point in the editor
        const pointsToShow = (geometry.type === 'Polygon') ? coords.slice(0, -1) : coords;

        pointsToShow.forEach((point, index) => {
            // point is [lng, lat]
            createPolyCoordRow(point[1], point[0], index);
        });

        polyInputContainer.classList.remove('hidden');
    }
    
    function createPolyCoordRow(lat, lng, index) {
        const row = document.createElement('div');
        row.className = 'poly-coord-row grid grid-cols-12 gap-2 items-center';
        row.innerHTML = `
            <span class="col-span-1 text-xs text-gray-500 font-semibold">${index + 1}.</span>
            <div class="col-span-5">
                <input type="number" step="any" value="${lat.toFixed(6)}" class="poly-lat-input w-full border-gray-300 rounded-md shadow-sm text-xs p-1">
            </div>
            <div class="col-span-5">
                <input type="number" step="any" value="${lng.toFixed(6)}" class="poly-lng-input w-full border-gray-300 rounded-md shadow-sm text-xs p-1">
            </div>
            <button type="button" class="remove-poly-point col-span-1 text-red-500 hover:text-red-700 font-bold text-lg focus:outline-none">&times;</button>
        `;
        polyCoordsList.appendChild(row);

        row.querySelector('.poly-lat-input').addEventListener('input', updateGeometryFromPolyInputs);
        row.querySelector('.poly-lng-input').addEventListener('input', updateGeometryFromPolyInputs);
        row.querySelector('.remove-poly-point').addEventListener('click', function() {
            row.remove();
            reindexPolyCoords();
            updateGeometryFromPolyInputs();
        });
    }

    function reindexPolyCoords() {
        polyCoordsList.querySelectorAll('.poly-coord-row').forEach((row, index) => {
            row.querySelector('span').textContent = `${index + 1}.`;
        });
    }

    addPolyPointBtn.addEventListener('click', function() {
        const lastRow = polyCoordsList.querySelector('.poly-coord-row:last-child');
        let newLat = map.getCenter().lat, newLng = map.getCenter().lng; // Default to map center
        if (lastRow) {
             newLat = parseFloat(lastRow.querySelector('.poly-lat-input').value) + 0.001; // Offset a bit
             newLng = parseFloat(lastRow.querySelector('.poly-lng-input').value) + 0.001;
        }
        createPolyCoordRow(newLat, newLng, polyCoordsList.children.length);
        updateGeometryFromPolyInputs();
    });
    // --- END NEW ---

    // {{-- MODIFIED: On Edit Mode --}}
    @if ($map->exists && $map->geometry)
        const geojsonData = {!! $map->geometry !!};
        const initialLayerType = "{{ $map->layers->first()->pivot->layer_type ?? 'marker' }}";
        const style = {
            color: "{{ old('stroke_color', $map->layers->first()->pivot->stroke_color ?? '#3388ff') }}",
            fillColor: "{{ old('fill_color', $map->layers->first()->pivot->fill_color ?? '#3388ff') }}",
            fillOpacity: {{ old('opacity', $map->layers->first()->pivot->opacity ?? 0.5) }},
            weight: {{ old('weight', $map->layers->first()->pivot->weight ?? 3) }}
        };

        drawnLayer = L.geoJSON(geojsonData, {
            style: style,
            pointToLayer: function (feature, latlng) {
                if (initialLayerType === 'circle') {
                    return L.circle(latlng, { ...style, radius: {{ old('radius', $map->layers->first()->pivot->radius ?? 300) }} });
                }
                const iconUrl = "{{ old('icon_url', $map->layers->first()->pivot->icon_url ?? '') }}";
                if (iconUrl && initialLayerType === 'marker') {
                    const icon = L.icon({ iconUrl: iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] });
                    return L.marker(latlng, { icon: icon });
                }
                return L.circleMarker(latlng, { ...style, radius: 5 });
            }
        }).addTo(map);
        
        try { 
            map.fitBounds(drawnLayer.getBounds()); 
        } catch (e) { console.error('Error fitting bounds:', e); }
        
        setActiveTool(initialLayerType);

        // {{-- MODIFIED: Populate appropriate manual inputs on edit --}}
        if (initialLayerType === 'marker' || initialLayerType === 'circle') {
            const center = extractCenterFromGeometry(geojsonData);
            if (center) {
                manualLatInput.value = center.lat.toFixed(6);
                manualLngInput.value = center.lng.toFixed(6);
                manualCoordsContainer.classList.remove('hidden');
                toggleCoordsBtn.textContent = 'Sembunyikan Input Manual';
            }
        } else if (initialLayerType === 'polygon' || initialLayerType === 'polyline') {
            populatePolyCoords(geojsonData);
        }
    @endif
});
</script>
@endsection