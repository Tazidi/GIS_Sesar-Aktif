@extends('layouts.app')

@section('title', 'Edit Fitur Peta')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        #map { height: 500px; }
    </style>
@endsection

@section('content')
<div class="container mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Edit Fitur untuk Peta: {{ $mapFeature->map->name }}</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('map-features.update', $mapFeature) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Kolom Kiri: Form Input --}}
                <div class="space-y-6">
                    <div>
                        <label for="properties" class="block text-sm font-medium text-gray-700">Properties (JSON)</label>
                        <textarea name="properties" id="properties" rows="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ old('properties', json_encode($mapFeature->properties, JSON_PRETTY_PRINT)) }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">Edit properti fitur dalam format JSON.</p>
                    </div>
                    <div>
                        <label for="technical_info" class="block text-sm font-medium text-gray-700">Informasi Teknis</label>
                        @php
                            $tech = $mapFeature->technical_info ? json_decode($mapFeature->technical_info, true) : [];
                        @endphp

                        <div class="mt-2 space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Panjang Sesar</label>
                                <input type="text" name="technical_info[panjang_sesar]"
                                    value="{{ old('technical_info.panjang_sesar', $tech['panjang_sesar'] ?? '') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600">Lebar Sesar</label>
                                <input type="text" name="technical_info[lebar_sesar]"
                                    value="{{ old('technical_info.lebar_sesar', $tech['lebar_sesar'] ?? '') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600">Tipe</label>
                                <input type="text" name="technical_info[tipe]"
                                    value="{{ old('technical_info.tipe', $tech['tipe'] ?? '') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600">MMAX</label>
                                <input type="text" name="technical_info[mmax]"
                                    value="{{ old('technical_info.mmax', $tech['mmax'] ?? '') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Opsional. Informasi teknis tambahan terkait fitur.</p>
                    </div>
                    <div>
                        <label for="feature_image" class="block text-sm font-medium text-gray-700">Upload Gambar Baru</label>
                        <input type="file" name="feature_image" id="feature_image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @if($mapFeature->image_path)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-600">Gambar Saat Ini:</p>
                                <img src="{{ asset('map_features/' . $mapFeature->image_path) }}" alt="Gambar Fitur" class="mt-2 w-1/2 rounded-md shadow-sm">
                            </div>
                        @endif
                    </div>

                    <div>
                        <label for="caption" class="block text-sm font-medium text-gray-700">Caption Gambar</label>
                        <input type="text" name="caption" id="caption"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                            value="{{ old('caption', $mapFeature->caption) }}">
                        <p class="mt-2 text-xs text-gray-500">Caption opsional yang akan ditampilkan di bawah gambar di modal.</p>
                    </div>
                </div>

                {{-- Kolom Kanan: Peta Leaflet --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Geometri Peta</label>
                    <div id="map" class="w-full rounded-md border border-gray-300 shadow-sm"></div>
                    <p class="mt-2 text-xs text-gray-500">Gunakan toolbar di peta untuk mengedit bentuk geometri.</p>
                </div>
            </div>

            {{-- Input tersembunyi untuk menyimpan data geometri --}}
            <input type="hidden" name="geometry" id="geometry" value="{{ old('geometry', json_encode($mapFeature->geometry)) }}">

            {{-- Tombol Aksi --}}
            <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end items-center">
                <a href="{{ route('map-features.index', $mapFeature->map_id) }}" class="text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-3">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const map = L.map('map').setView([-2.5, 118], 5); // Center di Indonesia
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const geometryInput = document.getElementById('geometry');
    const initialGeometry = JSON.parse(geometryInput.value);

    // Grup untuk menampung layer yang bisa diedit
    const editableLayers = new L.FeatureGroup();
    map.addLayer(editableLayers);

    // Tambahkan geometri yang sudah ada ke peta
    const existingLayer = L.geoJSON(initialGeometry);
    editableLayers.addLayer(existingLayer.getLayers()[0]); // Ambil layer internalnya

    // Zoom ke layer yang ada
    if (editableLayers.getLayers().length > 0) {
        map.fitBounds(editableLayers.getBounds().pad(0.1));
    }
    
    // Konfigurasi Leaflet.draw
    const drawControl = new L.Control.Draw({
        edit: {
            featureGroup: editableLayers, // Penting! Tentukan layer mana yang bisa diedit
            remove: false // Nonaktifkan tombol hapus di toolbar
        },
        draw: false // Nonaktifkan toolbar gambar baru, karena kita hanya edit
    });
    map.addControl(drawControl);

    // Event listener saat layer selesai diedit
    map.on(L.Draw.Event.EDITED, function (e) {
        e.layers.eachLayer(function (layer) {
            const geojson = layer.toGeoJSON().geometry;
            geometryInput.value = JSON.stringify(geojson);
            console.log('Geometri diperbarui:', geometryInput.value);
        });
    });
});
</script>
@endsection