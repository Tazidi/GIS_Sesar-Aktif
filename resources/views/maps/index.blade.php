@extends('layouts.app')

@section('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Gaya untuk peta pratinjau di dalam tabel */
        .preview-map {
            height: 150px; /* Ukuran lebih ringkas untuk tabel */
            width: 100%;
            border-radius: 0.5rem; /* 8px */
            border: 1px solid #e2e8f0; /* gray-200 */
        }

        /* Gaya untuk thumbnail gambar dan ikon */
        .map-thumbnail {
            width: 48px; /* Ukuran konsisten */
            height: 48px;
            object-fit: contain; /* Agar gambar tidak terdistorsi */
            border-radius: 0.25rem; /* 4px */
            border: 1px solid #cbd5e1; /* gray-300 */
        }

        /* Menonaktifkan interaksi pada peta pratinjau */
        .preview-map.leaflet-container {
            background-color: #f7fafc; /* gray-100 */
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Tombol Kembali ke Dashboard --}}
        <div class="mb-5">
        <a href="{{ route('dashboard') }}" 
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
            Kembali ke Dashboard
        </a>
    </div>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Peta</h1>
            <a href="{{ route('maps.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                Tambah Peta Baru
            </a>
        </div>

        <form method="GET" action="{{ route('maps.index') }}" class="mb-4 flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peta atau jenis fitur..."
                class="border-gray-300 rounded-md shadow-sm w-full sm:w-64">
            <button type="submit"
                class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700 transition">Cari</button>
        </form>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3">Nama Peta</th>
                            <th scope="col" class="px-4 py-3">Jenis Fitur</th>
                            <th scope="col" class="px-4 py-3">Koordinat</th>
                            <th scope="col" class="px-4 py-3">Radius</th>
                            <th scope="col" class="px-4 py-3">Aset Visual</th>
                            <th scope="col" class="px-4 py-3">GeoJSON</th>
                            <th scope="col" class="px-4 py-3 min-w-[200px]">Pratinjau Peta</th>
                            <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maps as $map)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $map->name }}</td>
                                <td class="px-4 py-2">{{ ucfirst($map->layer_type) }}</td>
                                <td class="px-4 py-2">{{ $map->lat && $map->lng ? $map->lat . ', ' . $map->lng : '-' }}</td>
                                <td class="px-4 py-2">{{ $map->layer_type == 'circle' ? ($map->radius ?? '-') . ' m' : '-' }}</td>
                                <td class="px-4 py-2">
                                    @if ($map->icon_url || $map->image_path)
                                        <div class="flex items-center gap-2">
                                            @if ($map->icon_url)
                                                <img src="{{ asset($map->icon_url) }}" alt="Ikon untuk {{ $map->name }}" title="Ikon: {{ basename($map->icon_url) }}" class="map-thumbnail">
                                            @endif
                                            @if ($map->image_path)
                                                <img src="{{ asset($map->image_path) }}" alt="Gambar untuk {{ $map->name }}" title="Gambar: {{ basename($map->image_path) }}" class="map-thumbnail">
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    @if ($map->file_path)
                                        <a href="{{ asset($map->file_path) }}" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    {{-- Container untuk peta pratinjau Leaflet --}}
                                    <div id="map-{{ $map->id }}" class="preview-map"></div>
                                </td>
                                <td class="px-4 py-2 text-center whitespace-nowrap">
                                    <a href="{{ route('map-features.index', ['map' => $map->id]) }}" class="text-green-600 hover:text-green-900 font-medium">Fitur</a>
                                    <a href="{{ route('maps.edit', $map) }}" class="text-indigo-600 hover:text-indigo-900 font-medium ml-2">Edit</a>
                                    <form action="{{ route('maps.destroy', $map) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peta ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-500">
                                    Belum ada data peta yang ditambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data semua peta dari controller
            const mapsData = @json($maps);

            // Fungsi untuk inisialisasi setiap peta pratinjau
            const initPreviewMap = (mapData) => {
                const mapContainerId = `map-${mapData.id}`;
                const geojsonUrl = `{{ url('maps') }}/${mapData.id}/geojson`;

                // Inisialisasi peta dengan opsi interaksi dinonaktifkan
                const previewMap = L.map(mapContainerId, {
                    zoomControl: false,
                    scrollWheelZoom: false,
                    dragging: false,
                    doubleClickZoom: false,
                    touchZoom: false,
                }).setView([-2.54, 118.01], 5); // Center of Indonesia

                // Tambahkan tile layer OpenStreetMap
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 18,
                }).addTo(previewMap);

                // Fungsi untuk membuat style layer dari properties
                const createStyle = (props = {}) => ({
                    color: props.stroke_color || mapData.stroke_color || '#3388ff',
                    fillColor: props.fill_color || mapData.fill_color || '#3388ff',
                    weight: props.weight || mapData.weight || 3,
                    opacity: props.opacity || mapData.opacity || 1.0,
                    fillOpacity: (props.fill_opacity || mapData.fill_opacity || 0.2) * 0.7, // Sedikit lebih transparan untuk pratinjau
                });
                
                // Fungsi untuk menampilkan fitur GeoJSON atau fallback
                const renderFeatures = async () => {
                    try {
                        const response = await fetch(geojsonUrl);
                        if (!response.ok) throw new Error('GeoJSON not found');
                        const geojsonData = await response.json();

                        const geoLayer = L.geoJSON(geojsonData, {
                            style: (feature) => createStyle(feature.properties),
                            pointToLayer: (feature, latlng) => {
                                const props = feature.properties || {};
                                const type = props.layer_type || mapData.layer_type;
                                const style = createStyle(props);
                                const iconUrl = props.icon_url || mapData.icon_url;

                                if (type === 'circle') {
                                    return L.circle(latlng, { ...style, radius: props.radius || mapData.radius || 300 });
                                }
                                if (type === 'marker' && iconUrl) {
                                    const icon = L.icon({ iconUrl: iconUrl, iconSize: [28, 28], iconAnchor: [14, 14] });
                                    return L.marker(latlng, { icon });
                                }
                                return L.circleMarker(latlng, { ...style, radius: 6 });
                            },
                            onEachFeature: (feature, layer) => {
                                const title = feature.properties.name || mapData.name || 'Info';
                                layer.bindPopup(`<b>${title}</b>`);
                            }
                        }).addTo(previewMap);

                        if (geoLayer.getBounds().isValid()) {
                            previewMap.fitBounds(geoLayer.getBounds(), { padding: [20, 20], maxZoom: 16 });
                        }

                    } catch (error) {
                        // Fallback: Gunakan data lat/lng dari database jika GeoJSON gagal dimuat
                        const lat = parseFloat(mapData.lat);
                        const lng = parseFloat(mapData.lng);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            const latlng = L.latLng(lat, lng);
                            const style = createStyle();
                            let fallbackLayer;

                            if (mapData.layer_type === 'circle') {
                                fallbackLayer = L.circle(latlng, { ...style, radius: mapData.radius || 300 });
                            } else if (mapData.layer_type === 'marker' && mapData.icon_url) {
                                const icon = L.icon({ iconUrl: mapData.icon_url, iconSize: [28, 28], iconAnchor: [14, 14] });
                                fallbackLayer = L.marker(latlng, { icon });
                            } else {
                                fallbackLayer = L.circleMarker(latlng, { ...style, radius: 6 });
                            }

                            fallbackLayer.bindPopup(`<b>${mapData.name}</b>`).addTo(previewMap);
                            previewMap.setView(latlng, 13);
                        }
                    } finally {
                        // Pastikan peta di-render ulang dengan ukuran yang benar
                        setTimeout(() => previewMap.invalidateSize(), 100);
                    }
                };

                renderFeatures();
            };

            // Inisialisasi peta untuk setiap data yang ada
            mapsData.forEach(initPreviewMap);
        });
    </script>
@endsection