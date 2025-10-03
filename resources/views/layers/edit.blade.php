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
            <p class="text-sm text-gray-500 mt-1 mb-6">Ubah nama dan deskripsi utama untuk layer ini.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_layer" class="block text-sm font-medium text-gray-700">Nama Layer</label>
                    <input type="text" name="nama_layer" id="nama_layer" value="{{ old('nama_layer', $layer->nama_layer) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi Layer</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('deskripsi', $layer->deskripsi) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Perulangan untuk setiap Fitur/Geometri --}}
        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Geometri Individual</h2>
        <div class="space-y-8">
            @foreach ($layer->mapFeatures as $feature)
                @php
                    $props = $feature->properties ?? [];
                    $tech  = $feature->technical_info ?? [];
                    $pivot = $feature->pivot;
                @endphp
                <div class="feature-card bg-white p-6 shadow-md">
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
                                <input type="text" name="features[{{$feature->id}}][name]" id="name-{{$feature->id}}" value="{{ old("features.{$feature->id}.name", $props['name'] ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="desc-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Deskripsi Fitur</label>
                                <textarea name="features[{{$feature->id}}][description]" id="desc-{{$feature->id}}" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old("features.{$feature->id}.description", $props['description'] ?? '') }}</textarea>
                            </div>

                            {{-- Input Styling --}}
                            <div class="border-t pt-4">
                                <h4 class="text-md font-medium text-gray-800 mb-2">Styling</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="stroke-{{$feature->id}}">Warna Garis</label>
                                        <input type="color" name="features[{{$feature->id}}][stroke_color]" id="stroke-{{$feature->id}}" value="{{ old("features.{$feature->id}.stroke_color", $pivot->stroke_color ?? '#3388ff') }}" class="mt-1 block w-full h-10">
                                    </div>
                                    <div>
                                        <label for="fill-{{$feature->id}}">Warna Isi</label>
                                        <input type="color" name="features[{{$feature->id}}][fill_color]" id="fill-{{$feature->id}}" value="{{ old("features.{$feature->id}.fill_color", $pivot->fill_color ?? '#3388ff') }}" class="mt-1 block w-full h-10">
                                    </div>
                                    <div>
                                        <label for="weight-{{$feature->id}}">Tebal Garis</label>
                                        <input type="number" name="features[{{$feature->id}}][weight]" id="weight-{{$feature->id}}" value="{{ old("features.{$feature->id}.weight", $pivot->weight ?? 3) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label for="opacity-{{$feature->id}}">Transparansi</label>
                                        <input type="number" name="features[{{$feature->id}}][opacity]" id="opacity-{{$feature->id}}" value="{{ old("features.{$feature->id}.opacity", $pivot->opacity ?? 0.5) }}" step="0.1" min="0" max="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    {{-- ... Tambahkan input lain untuk radius dan icon_url jika perlu ... --}}
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Peta, Gambar & Info Teknis --}}
                        <div class="space-y-6">
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Peta Geometri</label>
                                <div id="map-{{ $feature->id }}" class="h-64 w-full"></div>
                                <input type="hidden" name="features[{{$feature->id}}][geometry]" id="geometry-{{$feature->id}}" value="{{ old("features.{$feature->id}.geometry", $feature->geometry) }}">
                             </div>
                             <div>
                                <label for="image-{{$feature->id}}" class="block text-sm font-medium text-gray-700">Gambar Fitur</label>
                                <input type="file" name="features[{{$feature->id}}][image]" id="image-{{$feature->id}}" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0">
                                @if ($feature->image_path)
                                    <div class="mt-2">
                                        <img src="{{ asset($feature->image_path) }}" class="w-24 h-24 object-cover rounded-md">
                                        <label class="flex items-center mt-1">
                                            <input type="checkbox" name="features[{{$feature->id}}][remove_image]" value="1" class="rounded">
                                            <span class="ml-2 text-sm text-red-600">Hapus gambar</span>
                                        </label>
                                    </div>
                                @endif
                             </div>
                             <div>
                                 {{-- ... Tambahkan input untuk caption dan info teknis di sini, dengan nama array yang sama ... --}}
                                 {{-- Contoh: name="features[{{$feature->id}}][caption]" --}}
                                 {{-- Contoh: name="features[{{$feature->id}}][technical_info][panjang_sesar]" --}}
                             </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-8 flex justify-end space-x-4 bg-white p-4 rounded-xl shadow-lg border">
            <a href="{{ route('layers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fungsi ini akan menginisialisasi satu peta editor untuk satu fitur
    function initializeFeatureEditor(featureCard) {
        const featureId = featureCard.dataset.featureId;
        const mapContainer = featureCard.querySelector('.leaflet-container') ? null : featureCard.querySelector(`#map-${featureId}`);
        if (!mapContainer) return; // Jika map sudah diinisialisasi, jangan ulangi

        const geometryInput = featureCard.querySelector(`#geometry-${featureId}`);
        
        const map = L.map(mapContainer).setView([-6.9175, 107.6191], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const drawnItems = new L.FeatureGroup().addTo(map);

        try {
            const initialGeoJson = JSON.parse(geometryInput.value);
            const initialLayer = L.geoJSON(initialGeoJson).addTo(drawnItems);
            map.fitBounds(initialLayer.getBounds(), { paddingTopLeft: [50, 50], paddingBottomRight: [50, 50] });
        } catch (e) {
            console.error(`Gagal memuat GeoJSON untuk fitur #${featureId}:`, e);
        }

        const drawControl = new L.Control.Draw({
            edit: { featureGroup: drawnItems },
            draw: {
                polygon: true,
                polyline: true,
                rectangle: false,
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
                // Kirim hanya geometri dari fitur pertama (karena kita hanya edit 1 per 1)
                geometryInput.value = JSON.stringify(geoJsonData.features[0].geometry);
            } else {
                geometryInput.value = ''; // Kosongkan jika tidak ada gambar
            }
        }
    }

    // Inisialisasi editor untuk setiap kartu fitur
    document.querySelectorAll('.feature-card').forEach(card => {
        // Tambahkan data-feature-id ke card untuk referensi
        const featureId = card.querySelector('input[type=hidden]')?.name.match(/\[(\d+)\]/)[1];
        if (featureId) {
            card.dataset.featureId = featureId;
            initializeFeatureEditor(card);
        }
    });
});
</script>
@endsection