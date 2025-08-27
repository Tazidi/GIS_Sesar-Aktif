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
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peta atau layer..."
                class="border-gray-300 rounded-md shadow-sm w-full sm:w-64">
            <select name="kategori" class="border-gray-300 rounded-md shadow-sm w-full sm:w-48">
                <option value="">Semua Kategori</option>
                <option value="Peta SISIRAJA" {{ request('kategori') == 'Peta SISIRAJA' ? 'selected' : '' }}>Peta SISIRAJA</option>
                <option value="Galeri Peta" {{ request('kategori') == 'Galeri Peta' ? 'selected' : '' }}>Galeri Peta</option>
                <option value="Peta SISIRAJA & Galeri Peta" {{ request('kategori') == 'Peta SISIRAJA & Galeri Peta' ? 'selected' : '' }}>Peta SISIRAJA & Galeri Peta</option>
            </select>
            <button type="submit"
                class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700 transition">Cari</button>
        </form>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3">Nama Peta</th>
                            <th scope="col" class="px-4 py-3">Layers</th>
                            <th scope="col" class="px-4 py-3">Jenis Fitur</th>
                            <th scope="col" class="px-4 py-3">Koordinat</th>
                            <th scope="col" class="px-4 py-3">Radius</th>
                            <th scope="col" class="px-4 py-3">Aset Visual</th>
                            <th scope="col" class="px-4 py-3">GeoJSON</th>
                            <th scope="col" class="px-4 py-3 min-w-[200px]">Pratinjau Peta</th>
                            <th scope="col" class="px-4 py-3">Kategori</th>
                            <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maps as $map)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $map->name }}</td>
                                <td class="px-4 py-2">{{ $map->layers->pluck('nama_layer')->join(', ') }}</td>
                                <td class="px-4 py-2">{{ ucfirst($map->layer_type ?? 'N/A') }}</td>
                                <td class="px-4 py-2">{{ $map->lat && $map->lng ? $map->lat . ', ' . $map->lng : '-' }}</td>
                                <td class="px-4 py-2">{{ $map->layer_type == 'circle' ? ($map->radius ?? '-') . ' m' : '-' }}</td>
                                <td class="px-4 py-2">
                                    @if ($map->icon_url || $map->image_path)
                                        <div class="flex items-center gap-2">
                                            @if ($map->icon_url)
                                                <img src="{{ $map->icon_url }}" alt="Ikon" class="map-thumbnail">
                                            @endif
                                            @if ($map->image_path)
                                                <img src="{{ asset('storage/' . $map->image_path) }}" alt="Gambar" class="map-thumbnail">
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    @if($map->features->isNotEmpty() || $map->geometry)
                                        <a href="{{ route('maps.geojson', $map) }}" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    <div id="map-{{ $map->id }}" class="preview-map"></div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex flex-col gap-1">
                                        @foreach (['Peta SISIRAJA', 'Galeri Peta', 'Peta SISIRAJA & Galeri Peta'] as $kategori)
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="kategori_{{ $map->id }}" value="{{ $kategori }}"
                                                    {{ $map->kategori == $kategori ? 'checked' : '' }}
                                                    class="text-blue-600 border-gray-300 focus:ring-blue-500 kategori-radio"
                                                    data-id="{{ $map->id }}">
                                                <span class="ml-2">{{ $kategori }}</span>
                                            </label>
                                        @endforeach
                                    </div>
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
                                <td colspan="10" class="text-center py-6 text-gray-500">
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
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapsData = @json($maps);

            const initPreviewMap = (mapData) => {
                const mapContainerId = `map-${mapData.id}`;
                const mapContainer = document.getElementById(mapContainerId);
                if (!mapContainer || mapContainer.classList.contains('leaflet-container')) return;

                const previewMap = L.map(mapContainerId, {
                    zoomControl: false, scrollWheelZoom: false, dragging: false, doubleClickZoom: false,
                    touchZoom: false, boxZoom: false, keyboard: false
                }).setView([-2.54, 118.01], 4);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap', maxZoom: 18
                }).addTo(previewMap);
                
                const style = {
                    color: mapData.stroke_color || '#3388ff',
                    fillColor: mapData.fill_color || '#3388ff',
                    weight: mapData.weight || 3,
                    opacity: mapData.opacity || 1.0,
                    fillOpacity: (mapData.opacity || 0.2) * 0.7
                };

                const geojsonUrl = `{{ url('maps') }}/${mapData.id}/geojson`;
                fetch(geojsonUrl)
                    .then(response => response.json())
                    .then(geojsonData => {
                        const geoLayer = L.geoJSON(geojsonData, {
                            style: () => style,
                            pointToLayer: (feature, latlng) => {
                                const layerType = mapData.layer_type || 'marker';
                                const iconUrl = mapData.icon_url;
                                if (layerType === 'circle') {
                                    return L.circle(latlng, { ...style, radius: mapData.radius || 300 });
                                }
                                if (layerType === 'marker' && iconUrl) {
                                    const icon = L.icon({ iconUrl: iconUrl, iconSize: [28, 28], iconAnchor: [14, 14] });
                                    return L.marker(latlng, { icon });
                                }
                                return L.circleMarker(latlng, { ...style, radius: 6 });
                            }
                        }).addTo(previewMap);

                        if (geoLayer.getBounds().isValid()) {
                            previewMap.fitBounds(geoLayer.getBounds(), { padding: [20, 20], maxZoom: 16 });
                        }
                    })
                    .catch(error => console.error(`Gagal memuat GeoJSON untuk peta ${mapData.id}:`, error));
                
                setTimeout(() => previewMap.invalidateSize(), 100);
            };

            mapsData.forEach(initPreviewMap);

            document.querySelectorAll('.kategori-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const mapId = this.dataset.id;
                    const kategori = this.value;

                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('kategori', kategori);

                    fetch(`/maps/${mapId}/update-kategori`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Kategori diperbarui:', data);
                    })
                    .catch(err => console.error(err));
                });
            });
        });
    </script>
@endsection
