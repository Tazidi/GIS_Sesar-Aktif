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
                        <td class="border px-2 py-1">{{ $map->layer_type }}</td>
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
                                <a href="{{ asset($map->file_path) }}" target="_blank" class="text-blue-600 underline">Lihat File</a>
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
                    const divId = 'map-{{ $map->id }}';
                    const geojsonUrl = "{{ route('maps.geojson', $map->id) }}";
                    const mapInstance = L.map(divId, { zoomControl: false }).setView([-7.5, 107.5], 8);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 18
                    }).addTo(mapInstance);

                    const fallbackLat = {{ $map->lat ?? 'null' }};
                    const fallbackLng = {{ $map->lng ?? 'null' }};
                    const fallbackLatLng = (!isNaN(fallbackLat) && !isNaN(fallbackLng)) ? L.latLng(fallbackLat, fallbackLng) : null;

                    fetch(geojsonUrl)
                        .then(res => res.json())
                        .then(data => {
                            const geoLayer = L.geoJSON(data, {
                                style: function (feature) {
                                    const p = feature.properties || {};
                                    return {
                                        color: p.stroke || p.stroke_color || '#000000',
                                        fillColor: p.fill || p.fill_color || '#FF0000',
                                        opacity: p.opacity ?? 0.8,
                                        weight: p.weight ?? 2,
                                        fillOpacity: (p.opacity ?? 0.8) * 0.7
                                    };
                                },
                                pointToLayer: function (feature, latlng) {
                                    const p = feature.properties || {};
                                    const type = p.layer_type || 'marker';

                                    if (type === 'circle') {
                                        return L.circle(latlng, {
                                            radius: p.radius || 300,
                                            color: p.stroke || p.stroke_color || '#000000',
                                            fillColor: p.fill || p.fill_color || '#FF0000',
                                            fillOpacity: p.opacity ?? 0.6
                                        });
                                    }

                                    if (type === 'marker' && p.icon_url) {
                                        const icon = L.icon({
                                            iconUrl: p.icon_url,
                                            iconSize: [28, 28],
                                            iconAnchor: [14, 14]
                                        });
                                        return L.marker(latlng, { icon });
                                    }

                                    return L.circleMarker(latlng, {
                                        radius: 8,
                                        color: p.stroke || p.stroke_color || '#000000',
                                        fillColor: p.fill || p.fill_color || '#FF0000',
                                        fillOpacity: p.opacity ?? 0.6
                                    });
                                },
                                onEachFeature: function (feature, layer) {
                                    const props = feature.properties || {};
                                    let popupContent = `<strong>${props.name || props.title || 'Informasi'}</strong><br>`;
                                    popupContent += Object.entries(props)
                                        .filter(([k]) => !['name', 'title', 'photo', 'image', 'gambar', 'icon_url', 'layer_type', 'stroke_color', 'fill_color', 'opacity', 'weight', 'radius'].includes(k))
                                        .map(([k, v]) => `<b>${k}</b>: ${v}`).join('<br>');

                                    layer.bindPopup(popupContent);
                                }
                            }).addTo(mapInstance);

                            if (geoLayer.getBounds().isValid()) {
                                mapInstance.fitBounds(geoLayer.getBounds());
                            }
                        })
                        .catch(() => {
                            if (fallbackLatLng) {
                                let marker;
                                const iconUrl = "{{ $map->icon_url }}";
                                if (iconUrl) {
                                    const customIcon = L.icon({
                                        iconUrl: iconUrl.replace('public/', ''),
                                        iconSize: [28, 28],
                                        iconAnchor: [14, 28],
                                    });
                                    marker = L.marker(fallbackLatLng, { icon: customIcon });
                                } else {
                                    marker = L.marker(fallbackLatLng);
                                }
                                marker.bindPopup("{{ $map->name }}").addTo(mapInstance);
                                mapInstance.setView(fallbackLatLng, 13);
                            }
                        });
                })();
            @endforeach
        });
    </script>
@endsection