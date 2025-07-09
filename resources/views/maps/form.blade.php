<form action="{{ $map->exists ? route('maps.update', $map) : route('maps.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($map->exists)
        @method('PUT')
    @endif

    <div class="mb-4">
        <label class="block font-medium">Nama Peta</label>
        <input type="text" name="name" class="w-full border rounded px-2 py-1" value="{{ old('name', $map->name) }}" required>
    </div>

    <input type="hidden" name="geometry" id="geometry" value="{{ old('geometry', $map->geometry) }}">

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
        <label class="block font-medium">Klik Lokasi di Peta</label>
        <div id="select-map" class="w-full h-64 rounded border"></div>
        <p class="text-sm text-gray-500 mt-2">Klik di peta untuk memilih lokasi. Koordinat akan otomatis terisi.</p>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Deskripsi Peta</label>
        <textarea name="description" class="w-full border rounded px-2 py-1" rows="4">{{ old('description', $map->description) }}</textarea>
    </div>

    <div class="mb-4 layer-dependent" id="lat-field">
        <label class="block font-medium">Latitude</label>
        <input type="number" step="0.000001" name="lat" class="w-full border rounded px-2 py-1" value="{{ old('lat', $map->lat) }}">
    </div>

    <div class="mb-4 layer-dependent" id="lng-field">
        <label class="block font-medium">Longitude</label>
        <input type="number" step="0.000001" name="lng" class="w-full border rounded px-2 py-1" value="{{ old('lng', $map->lng) }}">
    </div>

    {{-- <div class="mb-4">
        <label class="block font-medium">Jarak (meter)</label>
        <input type="number" step="0.01" name="distance" class="w-full border rounded px-2 py-1" value="{{ old('distance', $map->distance) }}">
    </div> --}}

    <div class="mb-4" id="file-field">
        <label class="block font-medium">File Peta (GeoJSON/Shape/CSV)</label>
        <input type="file" name="file" class="w-full border rounded px-2 py-1">
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

    <div class="mb-4 layer-dependent" id="stroke-field">
        <label class="block font-medium">Warna Garis (Stroke)</label>
        <input type="color" name="stroke_color" value="{{ old('stroke_color', $map->stroke_color ?? '#000000') }}">
    </div>

    <div class="mb-4 layer-dependent" id="fill-field">
        <label class="block font-medium">Warna Isi (Fill)</label>
        <input type="color" name="fill_color" value="{{ old('fill_color', $map->fill_color ?? '#ff0000') }}">
    </div>

    <div class="mb-4 layer-dependent" id="opacity-field">
        <label class="block font-medium">Opacity</label>
        <input type="number" step="0.1" max="1" min="0" name="opacity" value="{{ old('opacity', $map->opacity ?? 0.8) }}">
    </div>

    <div class="mb-4 layer-dependent" id="weight-field">
        <label class="block font-medium">Ketebalan Garis</label>
        <input type="number" step="1" min="0" name="weight" value="{{ old('weight', $map->weight ?? 2) }}">
    </div>

    <div class="mb-4 layer-dependent" id="radius-field">
        <label class="block font-medium">Radius</label>
        <input type="number" step="1" name="radius" class="w-full border rounded px-2 py-1" value="{{ old('radius', $map->radius ?? 300) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Upload Gambar</label>
        <input type="file" name="image_path" class="w-full border rounded px-2 py-1">
        @if ($map->image_path)
            <img src="{{ asset($map->image_path) }}" alt="Gambar" class="w-32 mt-2 rounded">
        @endif
    </div>

    <div class="mb-4" id="icon-field">
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
<script src="https://unpkg.com/leaflet-editable@1.2.0/src/Leaflet.Editable.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('select-map', {
            editable: true
        }).setView([-7.5, 107.5], 8);
        let drawnLayer = null;
        let vertexMarkers = [];
        let polygonPoints = [];

        const layerTypeSelect = document.querySelector('select[name="layer_type"]');
        const latInput = document.querySelector('input[name="lat"]');
        const lngInput = document.querySelector('input[name="lng"]');
        const radiusInput = document.querySelector('input[name="radius"]');

        const latField = document.getElementById('lat-field');
        const lngField = document.getElementById('lng-field');
        const radiusField = document.getElementById('radius-field');
        const iconField = document.getElementById('icon-field');

        const strokeField = document.getElementById('stroke-field');
        const fillField = document.getElementById('fill-field');
        const opacityField = document.getElementById('opacity-field');
        const weightField = document.getElementById('weight-field');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        function showVertices(geometry) {
            vertexMarkers.forEach(marker => map.removeLayer(marker));
            vertexMarkers = [];

            if (geometry.type === "Polygon") {
                const coords = geometry.coordinates[0];
                coords.forEach((coord, index) => {
                    const latlng = L.latLng(coord[1], coord[0]);
                    const marker = L.circleMarker(latlng, {
                        radius: 5,
                        color: '#1D4ED8',
                        fillColor: '#3B82F6',
                        fillOpacity: 1,
                        weight: 1
                    }).addTo(map);
                    marker.bindPopup(`Titik ${index + 1}<br>Lat: ${latlng.lat.toFixed(6)}<br>Lng: ${latlng.lng.toFixed(6)}`);
                    vertexMarkers.push(marker);
                });
            } else if (geometry.type === "LineString") {
                geometry.coordinates.forEach((coord, index) => {
                    const latlng = L.latLng(coord[1], coord[0]);
                    const marker = L.circleMarker(latlng, {
                        radius: 5,
                        color: '#1D4ED8',
                        fillColor: '#3B82F6',
                        fillOpacity: 1,
                        weight: 1
                    }).addTo(map);
                    marker.bindPopup(`Titik ${index + 1}<br>Lat: ${latlng.lat.toFixed(6)}<br>Lng: ${latlng.lng.toFixed(6)}`);
                    vertexMarkers.push(marker);
                });
            }
        }

        // Tampilkan data awal jika ada
        @if ($map->geometry)
            const geojsonData = {!! $map->geometry !!};

            const layerType = "{{ old('layer_type', $map->layer_type) }}";
            const strokeColor = "{{ old('stroke_color', $map->stroke_color ?? '#000000') }}";
            const fillColor = "{{ old('fill_color', $map->fill_color ?? '#ff0000') }}";
            const opacity = {{ old('opacity', $map->opacity ?? 0.8) }};
            const weight = {{ old('weight', $map->weight ?? 2) }};
            const radius = {{ old('radius', $map->radius ?? 300) }};

            drawnLayer = L.geoJSON(geojsonData, {
                style: {
                    color: strokeColor,
                    fillColor: fillColor,
                    fillOpacity: opacity,
                    weight: weight
                },
                pointToLayer: function (feature, latlng) {
                    if (layerType === 'marker') {
                        return L.marker(latlng);
                    } else if (layerType === 'circle') {
                        return L.circle(latlng, {
                            radius: radius,
                            color: strokeColor,
                            fillColor: fillColor,
                            fillOpacity: opacity
                        });
                    }
                }
            }).addTo(map);

            try {
                map.fitBounds(drawnLayer.getBounds());
            } catch (e) {
                try {
                    map.setView(drawnLayer.getLatLng(), 13);
                } catch (e) {
                    map.setView([-7.5, 107.5], 10);
                }
            }

            showVertices(geojsonData);
        @endif

        function toggleFormFieldsByLayerType(type) {
            latField.style.display = 'none';
            lngField.style.display = 'none';
            radiusField.style.display = 'none';
            iconField.style.display = 'none';
            strokeField.style.display = 'none';
            fillField.style.display = 'none';
            opacityField.style.display = 'none';
            weightField.style.display = 'none';

            const mapContainer = document.getElementById('select-map').parentElement;

            if (type === 'marker') {
                latField.style.display = 'block';
                lngField.style.display = 'block';
                iconField.style.display = 'block';
            } else if (type === 'circle') {
                latField.style.display = 'block';
                lngField.style.display = 'block';
                radiusField.style.display = 'block';
                strokeField.style.display = 'block';
                fillField.style.display = 'block';
                opacityField.style.display = 'block';
            } else if (type === 'polygon') {
                latField.style.display = 'block';
                lngField.style.display = 'block';
                strokeField.style.display = 'block';
                fillField.style.display = 'block';
                opacityField.style.display = 'block';
            } else if (type === 'polyline') {
                strokeField.style.display = 'block';
                weightField.style.display = 'block';
                opacityField.style.display = 'block';
            }

            mapContainer.style.display = 'block';
        }

        map.on('click', function (e) {
            const type = layerTypeSelect.value;
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            const strokeColor = document.querySelector('input[name="stroke_color"]').value;
            const fillColor = document.querySelector('input[name="fill_color"]').value;
            const opacity = parseFloat(document.querySelector('input[name="opacity"]').value || 0.6);
            const weight = parseFloat(document.querySelector('input[name="weight"]').value || 2);
            const radius = parseFloat(radiusInput.value) || 300;

            latInput.value = lat;
            lngInput.value = lng;

            if (drawnLayer) {
                map.removeLayer(drawnLayer);
            }
            vertexMarkers.forEach(marker => map.removeLayer(marker));
            vertexMarkers = [];

            if (type === 'marker') {
                drawnLayer = L.marker(e.latlng).addTo(map);
                const geojson = drawnLayer.toGeoJSON();
                geojson.properties = { layer_type: 'marker' };
                document.getElementById('geometry').value = JSON.stringify(geojson);

            } else if (type === 'circle') {
                drawnLayer = L.circle(e.latlng, {
                    radius: radius,
                    color: strokeColor,
                    fillColor: fillColor,
                    fillOpacity: opacity
                }).addTo(map);

                const geojson = drawnLayer.toGeoJSON();
                geojson.properties = {
                    layer_type: 'circle',
                    radius: radius,
                    stroke: strokeColor,
                    fill: fillColor,
                    opacity: opacity
                };
                document.getElementById('geometry').value = JSON.stringify(geojson.geometry);

            } else if (type === 'polygon') {
                polygonPoints.push(e.latlng);

                if (drawnLayer) {
                    map.removeLayer(drawnLayer);
                }

                drawnLayer = L.polygon(polygonPoints, {
                    color: strokeColor,
                    fillColor: fillColor,
                    fillOpacity: opacity
                }).addTo(map);

                const centroid = getCentroid(polygonPoints);
                latInput.value = centroid.lat.toFixed(6);
                lngInput.value = centroid.lng.toFixed(6);

                const geojson = drawnLayer.toGeoJSON();
                geojson.properties = {
                    layer_type: 'polygon',
                    stroke: strokeColor,
                    fill: fillColor,
                    opacity: opacity
                };
                document.getElementById('geometry').value = JSON.stringify(geojson.geometry);
                showVertices(geojson.geometry);

            } else if (type === 'polyline') {
                polygonPoints.push(e.latlng);

                if (drawnLayer) {
                    map.removeLayer(drawnLayer);
                }

                drawnLayer = L.polyline(polygonPoints, {
                    color: strokeColor,
                    opacity: opacity,
                    weight: weight
                }).addTo(map);

                const geojson = drawnLayer.toGeoJSON();
                geojson.properties = {
                    layer_type: 'polyline',
                    stroke: strokeColor,
                    opacity: opacity,
                    weight: weight
                };
                document.getElementById('geometry').value = JSON.stringify(geojson.geometry);
                showVertices(geojson.geometry);
            }
        });

        function getCentroid(points) {
            let x = 0, y = 0;
            for (let i = 0; i < points.length; i++) {
                x += points[i].lat;
                y += points[i].lng;
            }
            return L.latLng(x / points.length, y / points.length);
        }

        layerTypeSelect.addEventListener('change', function () {
            if (drawnLayer) {
                const geojson = drawnLayer.toGeoJSON();
                document.getElementById('geometry').value = JSON.stringify(geojson.geometry);
                map.removeLayer(drawnLayer);
                drawnLayer = null;
            }
            vertexMarkers.forEach(marker => map.removeLayer(marker));
            vertexMarkers = [];

            polygonPoints = [];
            latInput.value = '';
            lngInput.value = '';
            toggleFormFieldsByLayerType(this.value);
        });

        toggleFormFieldsByLayerType(layerTypeSelect.value);

        const layerButtons = document.querySelectorAll('.layer-option');
        const layerSelect = document.getElementById('layer-select');

        layerButtons.forEach(button => {
            button.addEventListener('click', () => {
                layerSelect.value = button.dataset.layerId;
            });
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            console.log('Geometry:', document.getElementById('geometry').value);
        });

    });
</script>
@endsection