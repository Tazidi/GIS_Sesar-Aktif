@extends('layouts.app')  {{-- Sesuaikan jika nama layout Anda berbeda --}}

@section('title', 'Tambah Peta Baru')

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
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Tambah Peta</h1>

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
                    <label for="layer-select" class="block text-sm font-medium text-gray-700">Layer</label>
                    <select name="layer_id" id="layer-select" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">-- Pilih Layer --</option>
                        @foreach ($layers as $layer)
                            <option value="{{ $layer->id }}" {{ old('layer_id', $map->layer_id) == $layer->id ? 'selected' : '' }}>
                                {{ $layer->nama_layer }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                    <div>
                        <label for="image_path" class="block text-sm font-medium text-gray-700">Upload Gambar (Thumbnail)</label>
                        <input type="file" name="image_path" id="image_path" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        
                        <div id="image-preview-container" class="mt-2">
                            <img id="image-preview" src="#" alt="Image preview" class="w-full rounded-md shadow-sm" style="display: none;"/>
                            @if ($map->exists && $map->image_path)
                                <img id="existing-image" src="{{ asset($map->image_path) }}" alt="Gambar lama" class="w-full rounded-md shadow-sm">
                            @endif
                        </div>
                    </div>
                    <div>
                        <label for="map_file" class="block text-sm font-medium text-gray-700">File Peta (GeoJSON/CSV)</label>
                        <input type="file" name="file" id="map_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                    </div>
                </div>
            </div>

            <div>
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700">Gambar Fitur di Peta</label>
                    
                    <div id="drawing-toolbar" class="mt-1 mb-2 p-1 bg-gray-100 border border-gray-200 rounded-md inline-flex items-center space-x-1">
                        <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="marker" title="Marker">📍</button>
                        <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="polyline" title="Polyline">〰️</button>
                        <button type="button" class="draw-tool-btn px-3 py-2 rounded hover:bg-gray-200 text-sm" data-type="polygon" title="Polygon">polygons</button>
                        <button type="button" class="draw-tool-btn p-2 rounded hover:bg-gray-200" data-type="circle" title="Circle">⭕</button>
                    </div>

                    <div id="select-map" class="w-full h-[450px] rounded-md border border-gray-300 shadow-sm"></div>
                    <p class="text-xs text-gray-500 mt-1">Pilih alat gambar, lalu klik di peta.</p>
                </div>
            </div>
        </div>

        <div id="dynamic-options-container" class="mt-6 border border-gray-300 rounded-md p-4 space-y-4" style="display: none;">
            <h3 class="text-sm font-medium text-gray-900">Opsi Fitur Terpilih</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="layer-dependent" id="lat-field"><label class="block text-sm font-medium text-gray-700">Latitude</label><input type="number" step="any" name="lat" class="mt-1 w-full border-gray-300 rounded-md shadow-sm"></div>
                <div class="layer-dependent" id="lng-field"><label class="block text-sm font-medium text-gray-700">Longitude</label><input type="number" step="any" name="lng" class="mt-1 w-full border-gray-300 rounded-md shadow-sm"></div>
                <div class="layer-dependent" id="radius-field"><label class="block text-sm font-medium text-gray-700">Radius (m)</label><input type="number" step="1" name="radius" class="mt-1 w-full border-gray-300 rounded-md shadow-sm" value="300"></div>
                <div class="layer-dependent" id="weight-field"><label class="block text-sm font-medium text-gray-700">Tebal Garis</label><input type="number" step="1" min="0" name="weight" class="mt-1 w-full border-gray-300 rounded-md shadow-sm" value="3"></div>
                <div class="layer-dependent" id="opacity-field"><label class="block text-sm font-medium text-gray-700">Opacity (0-1)</label><input type="number" step="0.1" max="1" min="0" name="opacity" class="mt-1 w-full border-gray-300 rounded-md shadow-sm" value="0.5"></div>
                <div class="layer-dependent" id="stroke-field"><label class="block text-sm font-medium text-gray-700">Warna Garis</label><input type="color" name="stroke_color" value="#3388ff" class="mt-1 h-8 w-full border-gray-300 rounded-md"></div>
                <div class="layer-dependent" id="fill-field"><label class="block text-sm font-medium text-gray-700">Warna Isi</label><input type="color" name="fill_color" value="#3388ff" class="mt-1 h-8 w-full border-gray-300 rounded-md"></div>
                <div class="layer-dependent" id="icon-field"><label class="block text-sm font-medium text-gray-700">Ikon Marker</label><select name="icon_url" class="mt-1 w-full border-gray-300 rounded-md shadow-sm"><option value="">-- Default --</option><option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png">Hijau</option><option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png">Kuning</option><option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png">Merah</option><option value="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png">Abu-abu</option></select></div>
            </div>
        </div>

        <input type="hidden" name="geometry" id="geometry" value="{{ old('geometry', $map->geometry) }}">
        <input type="hidden" name="layer_type" id="layer_type" value="{{ old('layer_type', $map->layer_type) }}">

        {{-- Footer Form dengan Tombol Simpan dan Batal --}}
        <div class="mt-8 pt-5 border-t border-gray-200">
            <div class="flex justify-end items-center">
                {{-- TOMBOL BATAL DITAMBAHKAN DI SINI --}}
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
    const map = L.map('select-map').setView([-7.5, 107.5], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let drawnLayer = null, polygonPoints = [], currentLayerType = "{{ old('layer_type', $map->layer_type) }}", notificationTimeout;

    const geometryInput = document.getElementById('geometry');
    const layerTypeInput = document.getElementById('layer_type');
    const latInput = document.querySelector('input[name="lat"]');
    const lngInput = document.querySelector('input[name="lng"]');
    const toolbarButtons = document.querySelectorAll('.draw-tool-btn');
    const optionsContainer = document.getElementById('dynamic-options-container');
    const dynamicFields = { lat: document.getElementById('lat-field'), lng: document.getElementById('lng-field'), radius: document.getElementById('radius-field'), icon: document.getElementById('icon-field'), stroke: document.getElementById('stroke-field'), fill: document.getElementById('fill-field'), opacity: document.getElementById('opacity-field'), weight: document.getElementById('weight-field') };
    
    const imageInput = document.getElementById('image_path');
    const imagePreview = document.getElementById('image-preview');
    const existingImage = document.getElementById('existing-image');

    imageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.style.display = 'block';
                imagePreview.src = e.target.result;
                if (existingImage) {
                    existingImage.style.display = 'none';
                }
            }
            reader.readAsDataURL(file);
        }
    });

    function setActiveTool(type) {
        toolbarButtons.forEach(btn => btn.classList.toggle('bg-blue-200', btn.dataset.type === type));
        currentLayerType = type;
        layerTypeInput.value = type;
    }

    function toggleFormFields(type) {
        optionsContainer.style.display = 'none';
        for (const key in dynamicFields) {
            if (dynamicFields[key]) dynamicFields[key].style.display = 'none';
        }
        if (!type) return;

        optionsContainer.style.display = 'block';
        const fieldsToShow = {
            marker: ['lat', 'lng', 'icon'],
            circle: ['lat', 'lng', 'radius', 'stroke', 'fill', 'opacity'],
            polygon: ['stroke', 'fill', 'opacity', 'weight'],
            polyline: ['stroke', 'opacity', 'weight']
        };
        if (fieldsToShow[type]) {
            fieldsToShow[type].forEach(key => dynamicFields[key].style.display = 'block');
        }
    }

    function clearDrawing() {
        if (drawnLayer) map.removeLayer(drawnLayer);
        drawnLayer = null; polygonPoints = []; geometryInput.value = ''; latInput.value = ''; lngInput.value = '';
    }

    toolbarButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            setActiveTool(type); clearDrawing(); toggleFormFields(type);
        });
    });

    map.on('click', function (e) {
        if (!currentLayerType) return;
        latInput.value = e.latlng.lat.toFixed(6);
        lngInput.value = e.latlng.lng.toFixed(6);

        const style = {
            color: document.querySelector('input[name="stroke_color"]').value,
            fillColor: document.querySelector('input[name="fill_color"]').value,
            fillOpacity: parseFloat(document.querySelector('input[name="opacity"]').value),
            weight: parseInt(document.querySelector('input[name="weight"]').value),
            radius: parseInt(document.querySelector('input[name="radius"]').value)
        };

        if (currentLayerType === 'marker' || currentLayerType === 'circle') {
            clearDrawing();
            drawnLayer = currentLayerType === 'marker' ? L.marker(e.latlng).addTo(map) : L.circle(e.latlng, { ...style, weight: 1 }).addTo(map);
        } else if (['polygon', 'polyline'].includes(currentLayerType)) {
            polygonPoints.push(e.latlng);
            if (drawnLayer) map.removeLayer(drawnLayer);
            drawnLayer = (currentLayerType === 'polygon') ? L.polygon(polygonPoints, style).addTo(map) : L.polyline(polygonPoints, style).addTo(map);
        }
        if (drawnLayer) geometryInput.value = JSON.stringify(drawnLayer.toGeoJSON().geometry);
    });

    @if ($map->geometry)
        const geojsonData = {!! $map->geometry !!};
        const initialLayerType = "{{ old('layer_type', $map->layer_type) }}";
        const style = {
            color: "{{ old('stroke_color', $map->stroke_color) }}",
            fillColor: "{{ old('fill_color', $map->fill_color) }}",
            fillOpacity: {{ old('opacity', $map->opacity) ?? 0.5 }},
            weight: {{ old('weight', $map->weight) ?? 3 }}
        };

        drawnLayer = L.geoJSON(geojsonData, {
            style: style,
            pointToLayer: function (feature, latlng) {
                 if (initialLayerType === 'circle') return L.circle(latlng, { ...style, radius: {{ old('radius', $map->radius) ?? 300 }} });
                 const iconUrl = "{{ old('icon_url', $map->icon_url) }}";
                 const icon = iconUrl ? L.icon({ iconUrl: iconUrl, iconSize: [25, 41], iconAnchor: [12, 41] }) : L.marker(latlng).getIcon();
                 return L.marker(latlng, { icon: icon });
            }
        }).addTo(map);
        try { map.fitBounds(drawnLayer.getBounds()); } catch (e) {}
        setActiveTool(initialLayerType);
        toggleFormFields(initialLayerType);
    @endif
});
</script>
@endsection