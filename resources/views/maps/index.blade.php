@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-map {
            height: 300px;
            width: 100%;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        img.thumbnail {
            width: 60px;
            border-radius: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-4">Daftar Peta</h1>
        <a href="{{ route('maps.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Tambah Peta</a>

        <table class="table-auto w-full mt-4 border text-sm">
            <thead>
                <tr>
                    <th class="border px-2 py-1">Nama</th>
                    <th class="border px-2 py-1">Fitur</th>
                    <th class="border px-2 py-1">Latitude</th>
                    <th class="border px-2 py-1">Longitude</th>
                    <th class="border px-2 py-1">Jarak</th>
                    <th class="border px-2 py-1">Gambar</th>
                    <th class="border px-2 py-1">File</th>
                    <th class="border px-2 py-1">Peta</th>
                    <th class="border px-2 py-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($maps as $map)
                    <tr>
                        <td class="border px-2 py-1">{{ $map->name }}</td>
                        <td class="border px-2 py-1">{{ $map->feature_type }}</td>
                        <td class="border px-2 py-1">{{ $map->lat ?? '-' }}</td>
                        <td class="border px-2 py-1">{{ $map->lng ?? '-' }}</td>
                        <td class="border px-2 py-1">{{ $map->distance ?? '-' }} m</td>
                        <td class="border px-2 py-1">
                            @if ($map->image_path)
                                <img src="{{ asset($map->image_path) }}" alt="img" class="thumbnail">
                            @else
                                <span class="text-gray-500 italic">-</span>
                            @endif
                        </td>
                        <td class="border px-2 py-1">
                            @if ($map->file_path ?? false)
                                <a href="{{ asset($map->file_path) }}" target="_blank"
                                    class="text-blue-600 underline">Lihat File</a>
                            @else
                                <span class="text-gray-500 italic">Tidak ada file</span>
                            @endif
                        </td>
                        <td class="border px-2 py-1 w-1/3">
                            <div id="map-{{ $map->id }}" class="leaflet-map"></div>
                        </td>
                        <td class="border px-2 py-1">
                            <a href="{{ route('maps.edit', $map) }}" class="text-yellow-600">Edit</a> |
                            <form action="{{ route('maps.destroy', $map) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin ingin menghapus?')" class="text-red-600">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach ($maps as $map)
                (function () {
                    const mapDivId = 'map-{{ $map->id }}';
                    const geojsonUrl = "{{ route('maps.geojson', $map->id) }}";
                    const mapLeaflet = L.map(mapDivId).setView([-7.5, 107.5], 8);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(mapLeaflet);

                    // Tambahkan marker manual jika lat/lng tersedia
                    @if ($map->lat && $map->lng)
                        const markerLatLng = [{{ $map->lat }}, {{ $map->lng }}];

                        @if ($map->icon_url)
                            const customIcon = L.icon({
                                iconUrl: "{{ asset($map->icon_url) }}".replace('public/', ''), // pastikan path benar
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                                shadowSize: [41, 41]
                            });

                            L.marker(markerLatLng, { icon: customIcon })
                                .addTo(mapLeaflet)
                                .bindPopup("{{ $map->name }}");
                        @else
                            L.marker(markerLatLng)
                                .addTo(mapLeaflet)
                                .bindPopup("{{ $map->name }}");
                        @endif

                        mapLeaflet.setView(markerLatLng, 13);
                    @endif

                    // Tambahkan layer dari GeoJSON (jika ada)
                    fetch(geojsonUrl)
                        .then(res => res.json())
                        .then(data => {
                            const layer = L.geoJSON(data, {
                                onEachFeature: function (feature, layer) {
                                    let props = feature.properties || {};
                                    let content = Object.entries(props).map(([k, v]) => `<b>${k}</b>: ${v}`).join('<br>');
                                    layer.bindPopup(content);
                                },
                                pointToLayer: function (feature, latlng) {
                                    return L.circleMarker(latlng, {
                                        radius: 6,
                                        fillColor: "blue",
                                        color: "#000",
                                        weight: 1,
                                        opacity: 1,
                                        fillOpacity: 0.8
                                    });
                                }
                            }).addTo(mapLeaflet);

                            if (layer.getBounds().isValid()) {
                                mapLeaflet.fitBounds(layer.getBounds());
                            }
                        })
                        .catch(err => {
                            console.error(`Gagal memuat GeoJSON untuk Map ID {{ $map->id }}`, err);
                        });
                })();
            @endforeach
        });
    </script>
@endsection
