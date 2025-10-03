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
                    <select name="map_id" id="map_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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
        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Geometri Individual</h2>
        <div class="space-y-8">
            @foreach ($layer->mapFeatures as $feature)
                @php
                    $props = !empty($feature->properties) ? json_decode($feature->properties, true) : [];
                    $tech  = !empty($feature->technical_info) ? json_decode($feature->technical_info, true) : [];
                    $pivot = $feature->pivot;
                @endphp
                <div class="feature-card bg-white p-6 shadow-md" data-feature-id="{{ $feature->id }}">
                    <input type="hidden" name="features[{{ $feature->id }}][id]" value="{{ $feature->id }}">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Mengedit Fitur: {{ $props['name'] ?? "Fitur #{$feature->id}" }}
                    </h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Kolom Kiri: Properti & Styling --}}
                        <div class="space-y-6">
                            {{-- Input Nama & Deskripsi Fitur --}}
                            <div>
                                <label for="name-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Nama Fitur</label>
                                <input type="text" name="features[{{$feature->id}}][name]" id="name-{{$feature->id}}" value="{{ old("features.{$feature->id}.name", $props['name'] ?? '') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="desc-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Deskripsi Fitur</label>
                                <textarea name="features[{{$feature->id}}][description]" id="desc-{{$feature->id}}" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old("features.{$feature->id}.description", $props['description'] ?? '') }}</textarea>
                            </div>

                            {{-- Input Styling --}}
                            <div class="border-t pt-4">
                                <h4 class="text-md font-medium text-gray-800 mb-2">Styling</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="stroke-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Warna Garis</label>
                                        <input type="color" name="features[{{$feature->id}}][stroke_color]" id="stroke-{{$feature->id}}" value="{{ old("features.{$feature->id}.stroke_color", $pivot->stroke_color ?? '#3388ff') }}" class="mt-1 block w-full h-10 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label for="fill-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Warna Isi</label>
                                        <input type="color" name="features[{{$feature->id}}][fill_color]" id="fill-{{$feature->id}}" value="{{ old("features.{$feature->id}.fill_color", $pivot->fill_color ?? '#3388ff') }}" class="mt-1 block w-full h-10 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label for="weight-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Tebal Garis</label>
                                        <input type="number" name="features[{{$feature->id}}][weight]" id="weight-{{$feature->id}}" value="{{ old("features.{$feature->id}.weight", $pivot->weight ?? 3) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="opacity-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Transparansi</label>
                                        <input type="number" name="features[{{$feature->id}}][opacity]" id="opacity-{{$feature->id}}" value="{{ old("features.{$feature->id}.opacity", $pivot->opacity ?? 0.5) }}" step="0.1" min="0" max="1" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Peta, Gambar & Info Teknis --}}
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Peta Geometri</label>
                                <div id="map-{{ $feature->id }}" class="h-64 w-full border border-gray-300 rounded"></div>
                                <input type="hidden" name="features[{{$feature->id}}][geometry]" id="geometry-{{$feature->id}}" value="{{ old("features.{$feature->id}.geometry", $feature->geometry) }}">
                            </div>
                            
                            <div>
                                <label for="image-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Gambar Fitur</label>
                                <input type="file" name="features[{{$feature->id}}][image]" id="image-{{$feature->id}}" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @if ($feature->image_path)
                                    <div class="mt-2">
                                        <img src="{{ asset($feature->image_path) }}" alt="Gambar fitur" class="w-24 h-24 object-cover rounded-md border border-gray-300">
                                        <label class="flex items-center mt-1">
                                            <input type="checkbox" name="features[{{$feature->id}}][remove_image]" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
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
    // Fungsi ini akan menginisialisasi satu peta editor untuk satu fitur
    function initializeFeatureEditor(featureCard) {
        const featureId = featureCard.dataset.featureId;
        const mapContainer = document.getElementById(`map-${featureId}`);
        const geometryInput = document.getElementById(`geometry-${featureId}`);
        
        if (!mapContainer || !geometryInput) return;

        const map = L.map(mapContainer).setView([-6.9175, 107.6191], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const drawnItems = new L.FeatureGroup().addTo(map);

        try {
            const initialGeoJson = JSON.parse(geometryInput.value);
            const initialLayer = L.geoJSON(initialGeoJson).addTo(drawnItems);
            map.fitBounds(initialLayer.getBounds(), { padding: [20, 20] });
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

        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            drawnItems.addLayer(e.layer);
            updateGeometryInput();
        });

        map.on(L.Draw.Event.EDITED, function (e) {
            updateGeometryInput();
        });

        map.on(L.Draw.Event.DELETED, function (e) {
            updateGeometryInput();
        });

        function updateGeometryInput() {
            const geoJsonData = drawnItems.toGeoJSON();
            if (geoJsonData.features.length > 0) {
                geometryInput.value = JSON.stringify(geoJsonData.features[0].geometry);
            } else {
                geometryInput.value = '';
            }
        }
    }

    // Inisialisasi editor untuk setiap kartu fitur
    document.querySelectorAll('.feature-card').forEach(card => {
        initializeFeatureEditor(card);
    });
});
</script>
@endsection