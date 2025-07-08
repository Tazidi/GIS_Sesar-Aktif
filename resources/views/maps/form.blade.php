<form action="{{ $map->exists ? route('maps.update', $map) : route('maps.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($map->exists)
        @method('PUT')
    @endif

    <div class="mb-4">
        <label class="block font-medium">Nama Peta</label>
        <input type="text" name="name" class="w-full border rounded px-2 py-1" value="{{ old('name', $map->name) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Klik Lokasi di Peta</label>
        <div id="select-map" class="w-full h-64 rounded border"></div>
        <p class="text-sm text-gray-500 mt-2">Klik di peta untuk memilih lokasi. Koordinat akan otomatis terisi.</p>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Deskripsi Peta</label>
        <textarea name="description" class="w-full border rounded px-2 py-1" rows="4">{{ old('description', $map->description) }}</textarea>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Latitude</label>
        <input type="number" step="0.000001" name="lat" class="w-full border rounded px-2 py-1" value="{{ old('lat', $map->lat) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Longitude</label>
        <input type="number" step="0.000001" name="lng" class="w-full border rounded px-2 py-1" value="{{ old('lng', $map->lng) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Jarak (meter)</label>
        <input type="number" step="0.01" name="distance" class="w-full border rounded px-2 py-1" value="{{ old('distance', $map->distance) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">File Peta (GeoJSON/Shape/CSV)</label>
        <input type="file" name="file" class="w-full border rounded px-2 py-1">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Jenis Layer</label>
        <select name="layer_type" class="w-full border rounded px-2 py-1" required>
            <option value="">-- Pilih --</option>
            @foreach (['marker', 'polyline', 'polygon', 'circle'] as $type)
                <option value="{{ $type }}" {{ old('layer_type', $map->layer_type) === $type ? 'selected' : '' }}>
                    {{ ucfirst($type) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label class="block font-medium mb-1">Layer</label>
        <select name="layer_id" id="layer-select" class="w-full border rounded px-2 py-1" required>
            <option value="">-- Pilih Layer --</option>
            @foreach ($layers as $layer)
                <option value="{{ $layer->id }}"
                    {{ old('layer_id', $map->layer_id) == $layer->id ? 'selected' : '' }}>
                    {{ $layer->nama_layer }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Warna Garis (Stroke)</label>
        <input type="color" name="stroke_color" value="{{ old('stroke_color', $map->stroke_color ?? '#000000') }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Warna Isi (Fill)</label>
        <input type="color" name="fill_color" value="{{ old('fill_color', $map->fill_color ?? '#ff0000') }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Opacity</label>
        <input type="number" step="0.1" max="1" min="0" name="opacity" value="{{ old('opacity', $map->opacity ?? 0.8) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Ketebalan Garis</label>
        <input type="number" step="1" min="0" name="weight" value="{{ old('weight', $map->weight ?? 2) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Radius (untuk Circle)</label>
        <input type="number" step="1" name="radius" value="{{ old('radius', $map->radius ?? 300) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Upload Gambar</label>
        <input type="file" name="image_path" class="w-full border rounded px-2 py-1">
        @if ($map->image_path)
            <img src="{{ asset($map->image_path) }}" alt="Gambar" class="w-32 mt-2 rounded">
        @endif
    </div>

    <div class="mb-4">
        <label class="block font-medium">Pilih Icon</label>
        <select name="icon_url" class="w-full border rounded px-2 py-1">
            <option value="">-- Pilih --</option>
            @foreach ([
                'https://cdn.jsdelivr.net/npm/@vectorial1024/leaflet-color-markers/img/marker-icon-2x-green.png' => 'Hijau',
                'https://cdn.jsdelivr.net/npm/@vectorial1024/leaflet-color-markers/img/marker-icon-2x-yellow.png' => 'Kuning',
                'https://cdn.jsdelivr.net/npm/@vectorial1024/leaflet-color-markers/img/marker-icon-2x-red.png' => 'Merah'
            ] as $value => $label)
                <option value="{{ $value }}" {{ old('icon_url', $map->icon_url) == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">
        {{ $map->exists ? 'Perbarui' : 'Simpan' }}
    </button>
</form>

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('select-map').setView([-7.5, 107.5], 8);
            let marker = null;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const latInput = document.querySelector('input[name="lat"]');
            const lngInput = document.querySelector('input[name="lng"]');
            const featureTypeSelect = document.querySelector('select[name="feature_type"]');

            @if (old('lat', $map->lat) && old('lng', $map->lng))
                const existingLatLng = L.latLng({{ old('lat', $map->lat) }}, {{ old('lng', $map->lng) }});
                marker = L.marker(existingLatLng).addTo(map);
                map.setView(existingLatLng, 13);
            @endif

            map.on('click', function (e) {
                const lat = e.latlng.lat.toFixed(6);
                const lng = e.latlng.lng.toFixed(6);

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }

                latInput.value = lat;
                lngInput.value = lng;
            });

            featureTypeSelect.addEventListener('change', () => {
                if (featureTypeSelect.value !== 'point') {
                    if (marker) {
                        map.removeLayer(marker);
                        marker = null;
                    }
                    latInput.value = '';
                    lngInput.value = '';
                }
            });

            // === Script Tombol Layer ===
            const layerButtons = document.querySelectorAll('.layer-option');
            const layerSelect = document.getElementById('layer-select');

            layerButtons.forEach(button => {
                button.addEventListener('click', () => {
                    layerSelect.value = button.dataset.layerId;
                });
            });
        });
    </script>
@endsection