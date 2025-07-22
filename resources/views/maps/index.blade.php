@extends('layouts.app')

@section('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Gaya untuk peta pratinjau di dalam tabel */
        .leaflet-map {
            height: 200px; /* Sedikit lebih kecil agar tabel tidak terlalu tinggi */
            width: 100%;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        /* Gaya untuk thumbnail gambar */
        img.thumbnail {
            width: 60px;
            height: auto;
            border-radius: 4px;
        }

        /* Nonaktifkan interaksi pada peta pratinjau */
        .leaflet-container {
            background-color: #f0f0f0;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-4">Daftar Peta</h1>
        <a href="{{ route('maps.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Tambah Peta</a>

        <div class="overflow-x-auto mt-4">
            <table class="table-auto w-full border text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-2 py-1">Nama</th>
                        <th class="border px-2 py-1">Fitur</th>
                        <th class="border px-2 py-1">Latitude</th>
                        <th class="border px-2 py-1">Longitude</th>
                        <th class="border px-2 py-1">Radius</th>
                        <th class="border px-2 py-1">Ikon/Gambar</th>
                        <th class="border px-2 py-1">File GeoJSON</th>
                        <th class="border px-2 py-1 w-1/4">Peta Pratinjau</th>
                        <th class="border px-2 py-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($maps as $map)
                        <tr>
                            <td class="border px-2 py-1 font-semibold">{{ $map->name }}</td>
                            <td class="border px-2 py-1">{{ ucfirst($map->layer_type) }}</td>
                            <td class="border px-2 py-1">{{ $map->lat ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $map->lng ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $map->layer_type == 'circle' ? ($map->radius ?? '-') . ' m' : '-' }}</td>
                            <td class="border px-2 py-1">
                                {{-- Cek apakah salah satu atau keduanya ada isinya --}}
                                @if ($map->icon_url || $map->image_path)
                                    <div class="flex items-center gap-2">
                                        {{-- Tampilkan Ikon jika ada --}}
                                        @if ($map->icon_url)
                                            <img src="{{ asset($map->icon_url) }}" alt="Ikon" title="Ikon: {{ basename($map->icon_url) }}" 
                                                style="width: 32px; height: 32px; border-radius: 4px; border: 1px solid #ddd; object-fit: contain;">
                                        @endif

                                        {{-- Tampilkan Gambar jika ada --}}
                                        @if ($map->image_path)
                                            <img src="{{ asset($map->image_path) }}" alt="Gambar" title="Gambar: {{ basename($map->image_path) }}" class="thumbnail">
                                        @endif
                                    </div>
                                @else
                                    {{-- Tampilkan placeholder jika keduanya tidak ada --}}
                                    <span class="text-gray-500 italic">-</span>
                                @endif
                            </td>
                            <td class="border px-2 py-1">
                                @if ($map->file_path)
                                    <a href="{{ asset($map->file_path) }}" target="_blank" class="text-blue-600 underline">Lihat File</a>
                                @else
                                    <span class="text-gray-500 italic">Tidak ada</span>
                                @endif
                            </td>
                            <td class="border px-2 py-1">
                                {{-- Container untuk peta Leaflet --}}
                                <div id="map-{{ $map->id }}" class="leaflet-map"></div>
                            </td>
                            <td class="border px-2 py-1 text-center">
                                <a href="{{ route('map-features.index', ['map' => $map->id]) }}" class="text-green-600 hover:underline">Lihat Fitur</a> |
                                <a href="{{ route('maps.edit', $map) }}" class="text-yellow-600 hover:underline">Edit</a> |
                                <form action="{{ route('maps.destroy', $map) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus peta ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center border py-4">Belum ada data peta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Loop melalui setiap data peta dari controller
            @foreach ($maps as $map)
                (function() {
                    // Mengambil semua data map sebagai objek JSON untuk digunakan di JS
                    const mapData = @json($map);
                    const divId = `map-${mapData.id}`;
                    const geojsonUrl = "{{ route('maps.geojson', ':id') }}".replace(':id', mapData.id);

                    // Inisialisasi peta pratinjau
                    // Interaksi pengguna seperti zoom dan drag dinonaktifkan
                    const mapInstance = L.map(divId, {
                        zoomControl: false,
                        scrollWheelZoom: false,
                        dragging: false,
                        doubleClickZoom: false,
                        touchZoom: false
                    }).setView([-7.5, 107.5], 5);

                    // Menambahkan base layer OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap',
                        maxZoom: 18
                    }).addTo(mapInstance);

                    // Fungsi untuk membuat style layer (garis, poligon, lingkaran)
                    function createStyle(props) {
                        const p = props || {};
                        return {
                            color: p.stroke_color || mapData.stroke_color || '#3388ff',
                            fillColor: p.fill_color || mapData.fill_color || '#3388ff',
                            weight: p.weight || mapData.weight || 3,
                            opacity: p.opacity || mapData.opacity || 1.0,
                            fillOpacity: (p.opacity || mapData.opacity || 0.2) * 0.7,
                        };
                    }

                    // Logika utama: coba muat file GeoJSON
                    fetch(geojsonUrl)
                        .then(res => {
                            if (!res.ok) { // Jika file tidak ditemukan (404), lempar error
                                throw new Error('GeoJSON not found, using fallback.');
                            }
                            return res.json();
                        })
                        .then(data => {
                            // Jika GeoJSON berhasil dimuat
                            const geoLayer = L.geoJSON(data, {
                                style: (feature) => createStyle(feature.properties),
                                pointToLayer: (feature, latlng) => {
                                    const props = feature.properties || {};
                                    const type = props.layer_type || mapData.layer_type;
                                    const style = createStyle(props);

                                    if (type === 'circle') {
                                        return L.circle(latlng, {
                                            ...style,
                                            radius: props.radius || mapData.radius || 300
                                        });
                                    }

                                    if (type === 'marker' && (props.icon_url || mapData.icon_url)) {
                                        const icon = L.icon({
                                            iconUrl: props.icon_url || mapData.icon_url,
                                            iconSize: [28, 28],
                                            iconAnchor: [14, 14]
                                        });
                                        return L.marker(latlng, { icon });
                                    }

                                    // Default-nya adalah marker lingkaran kecil
                                    return L.circleMarker(latlng, { ...style, radius: 6 });
                                },
                                onEachFeature: (feature, layer) => {
                                    const title = feature.properties.name || mapData.name || 'Info';
                                    layer.bindPopup(`<b>${title}</b>`);
                                }
                            }).addTo(mapInstance);

                            // Sesuaikan view peta agar pas dengan layer
                            if (geoLayer.getBounds().isValid()) {
                                mapInstance.fitBounds(geoLayer.getBounds(), { padding: [20, 20], maxZoom: 16 });
                            }
                        })
                        .catch(() => {
                            // FALLBACK: Jika GeoJSON gagal dimuat atau tidak ada
                            // Gunakan data lat/lng dari database
                            const fallbackLat = parseFloat(mapData.lat);
                            const fallbackLng = parseFloat(mapData.lng);

                            if (!isNaN(fallbackLat) && !isNaN(fallbackLng)) {
                                const latlng = L.latLng(fallbackLat, fallbackLng);
                                let fallbackLayer;
                                const style = createStyle();
                                
                                if (mapData.layer_type === 'circle') {
                                    fallbackLayer = L.circle(latlng, {
                                        ...style,
                                        radius: mapData.radius || 300
                                    });
                                } else if (mapData.layer_type === 'marker' && mapData.icon_url) {
                                    const icon = L.icon({ iconUrl: mapData.icon_url, iconSize: [28, 28], iconAnchor: [14, 14] });
                                    fallbackLayer = L.marker(latlng, { icon });
                                } else {
                                    fallbackLayer = L.circleMarker(latlng, { ...style, radius: 6 });
                                }
                                
                                fallbackLayer.bindPopup(`<b>${mapData.name}</b>`).addTo(mapInstance);
                                mapInstance.setView(latlng, 13);
                            }
                        });

                    // Trik untuk memastikan peta di-render dengan ukuran yang benar
                    setTimeout(() => mapInstance.invalidateSize(), 100);

                })();
            @endforeach
        });
    </script>
@endsection