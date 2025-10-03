@extends('layouts.app')

@section('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <style>
        /* Gaya untuk peta pratinjau di dalam tabel */
        .preview-map {
            height: 120px; /* Ukuran lebih ringkas untuk tabel */
            width: 100%;
            border-radius: 0.5rem; /* 8px */
            border: 1px solid #e2e8f0; /* gray-200 */
        }

        /* Gaya untuk thumbnail gambar dan ikon */
        .map-thumbnail {
            width: 40px; /* Ukuran konsisten */
            height: 40px;
            object-fit: contain; /* Agar gambar tidak terdistorsi */
            border-radius: 0.25rem; /* 4px */
            border: 1px solid #cbd5e1; /* gray-300 */
        }

        /* Menonaktifkan interaksi pada peta pratinjau */
        .preview-map.leaflet-container {
            background-color: #f7fafc; /* gray-100 */
        }

        /* Gaya untuk tombol aksi */
        .action-buttons a {
            margin: 0 4px;
        }
        /* Gaya untuk dropdown show entries */
        .action-buttons a {
            margin: 0 4px;
        }

        /* [TAMBAHKAN INI] Fix untuk spasi dropdown DataTables */
        .dataTables_length select {
            margin-left: 0.5rem; /* 8px */
            margin-right: 0.5rem; /* 8px */
            padding-right: 2rem !important; /* Memberi ruang untuk ikon panah */
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
            <select name="kategori" class="border-gray-300 rounded-md shadow-sm w-full sm:w-48">
                <option value="">Semua</option>
                <option value="Ya" {{ request('kategori') == 'Ya' ? 'selected' : '' }}>Tampil di Peta Sisiraja (Ya)</option>
                <option value="Tidak" {{ request('kategori') == 'Tidak' ? 'selected' : '' }}>Tampil di Peta Sisiraja (Tidak)</option>
            </select>
            <button type="submit"
                class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700 transition">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table id="mapsTable" class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">Nama Peta</th>
                        <th scope="col" class="px-4 py-3">Jenis Fitur</th>
                        <th scope="col" class="px-4 py-3">Koordinat</th>
                        <th scope="col" class="px-4 py-3">Radius</th>
                        <th scope="col" class="px-4 py-3">Aset Visual</th>
                        <th scope="col" class="px-4 py-3">GeoJSON</th>
                        <th scope="col" class="px-4 py-3 min-w-[150px]">Pratinjau Peta</th>
                        <th scope="col" class="px-4 py-3">Kategori</th>
                        <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($maps as $map)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $map->name }}</td>
                            <td class="px-4 py-2">{{ ucfirst($map->layer_type) }}</td>
                            <td class="px-4 py-2">{{ $map->lat && $map->lng ? $map->lat . ', ' . $map->lng : '-' }}</td>
                            <td class="px-4 py-2">{{ $map->layer_type == 'circle' ? ($map->radius ?? '-') . ' m' : '-' }}</td>
                            <td class="px-4 py-2">
                                @if ($map->icon_url || $map->image_path)
                                    <div class="flex items-center gap-1">
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
                                    @foreach (['Ya', 'Tidak'] as $label)
                                        <label class="inline-flex items-center">
                                            <input type="radio" 
                                                name="kategori_{{ $map->id }}" 
                                                value="{{ $label }}"
                                                {{ $map->kategori == $label ? 'checked' : '' }}
                                                class="kategori-radio"
                                                data-id="{{ $map->id }}">
                                            <span class="ml-2">Tampil di Peta Sisiraja {{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center whitespace-nowrap action-buttons">
                                <a href="{{ route('maps.geometries.index', $map) }}" class="text-blue-600 hover:text-blue-900 font-medium">Geometri</a>
                                <a href="{{ route('maps.edit', $map) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                                <form action="{{ route('maps.destroy', $map) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peta ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- DataTables JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTables
            $('#mapsTable').DataTable({
                language: {
                    emptyTable: 'Belum ada data peta yang ditambahkan.'
                }
            });

            // Data semua peta dari controller
            const mapsData = @json($maps);

            // Helper functions
            const safeNumber = (v, fallback) => {
                const n = Number(v);
                return Number.isFinite(n) ? n : fallback;
            };

            // Helper: parse technical_info yang mungkin string JSON atau object
            const parseTechnicalInfo = (val) => {
                if (!val) return {};
                if (typeof val === 'object') return val;
                try {
                    return JSON.parse(val);
                } catch (e) {
                    return {};
                }
            };

            // Helper: get style dari technical_info atau fallback ke map data
            const getStyleFromTechnicalInfo = (techInfo, mapData, featureProps = {}) => {
                // Prioritas: technical_info -> feature properties -> map data -> default
                const stroke_color = techInfo.stroke_color || techInfo.color || featureProps.stroke_color || featureProps.color || mapData.stroke_color || '#3388ff';
                const fill_color = techInfo.fill_color || techInfo.color || featureProps.fill_color || featureProps.color || mapData.fill_color || stroke_color;
                const weight = safeNumber(techInfo.weight || featureProps.weight || mapData.weight, 3);
                const opacity = safeNumber(techInfo.opacity || featureProps.opacity || mapData.opacity, 0.8);
                const fillOpacity = safeNumber(techInfo.fill_opacity || featureProps.fill_opacity || (mapData.opacity * 0.7), 0.3);

                return {
                    color: stroke_color,
                    fillColor: fill_color,
                    weight,
                    opacity,
                    fillOpacity
                };
            };

            // Fungsi untuk inisialisasi setiap peta pratinjau
            const initPreviewMap = (mapData) => {
                const mapContainerId = `map-${mapData.id}`;
                const mapContainer = document.getElementById(mapContainerId);
                if (!mapContainer || mapContainer.classList.contains('leaflet-container')) return;

                const previewMap = L.map(mapContainerId, {
                    zoomControl: false, 
                    scrollWheelZoom: false, 
                    dragging: false, 
                    doubleClickZoom: false,
                    touchZoom: false, 
                    boxZoom: false, 
                    keyboard: false
                }).setView([-2.54, 118.01], 4);

                // Base layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap', 
                    maxZoom: 18
                }).addTo(previewMap);

                // Style dasar dari map data (fallback)
                const defaultStyle = {
                    color: mapData.stroke_color || '#3388ff',
                    fillColor: mapData.fill_color || '#3388ff',
                    weight: mapData.weight || 3,
                    opacity: mapData.opacity || 0.8,
                    fillOpacity: (mapData.opacity || 0.2) * 0.7
                };

                // Coba load GeoJSON
                const geojsonUrl = `{{ url('maps') }}/${mapData.id}/geojson`;
                
                fetch(geojsonUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('GeoJSON not found');
                        return response.json();
                    })
                    .then(geojsonData => {
                        const geoLayer = L.geoJSON(geojsonData, {
                            style: (feature) => {
                                const props = feature.properties || {};
                                const techInfo = parseTechnicalInfo(props.technical_info);
                                
                                return getStyleFromTechnicalInfo(techInfo, mapData, props);
                            },
                            pointToLayer: (feature, latlng) => {
                                const props = feature.properties || {};
                                const techInfo = parseTechnicalInfo(props.technical_info);
                                
                                // Tentukan jenis layer berdasarkan urutan prioritas:
                                // 1. technical_info.geometry_type
                                // 2. feature properties layer_type  
                                // 3. mapData.layer_type
                                // 4. geometry type asli
                                const layerType = techInfo.geometry_type || 
                                                props.layer_type || 
                                                mapData.layer_type || 
                                                (feature.geometry && feature.geometry.type === 'Point' ? 'marker' : 'marker');
                                
                                const style = getStyleFromTechnicalInfo(techInfo, mapData, props);
                                const iconUrl = techInfo.icon_url || props.icon_url || mapData.icon_url;
                                const radius = safeNumber(techInfo.radius || props.radius || mapData.radius, 300);
                                const pointRadius = safeNumber(techInfo.point_radius || props.point_radius || 6, 6);

                                console.log('Feature:', feature);
                                console.log('Technical Info:', techInfo);
                                console.log('Layer Type:', layerType);
                                console.log('Icon URL:', iconUrl);
                                console.log('Style:', style);

                                // Tentukan jenis layer berdasarkan tipe yang didapat
                                if (layerType === 'circle') {
                                    return L.circle(latlng, { 
                                        ...style, 
                                        radius: radius
                                    });
                                }
                                else if (layerType === 'circlemarker') {
                                    return L.circleMarker(latlng, { 
                                        ...style, 
                                        radius: pointRadius
                                    });
                                }
                                else if (layerType === 'marker') {
                                    if (iconUrl && iconUrl.trim() !== '') {
                                        const icon = L.icon({ 
                                            iconUrl: iconUrl, 
                                            iconSize: [18, 18], 
                                            iconAnchor: [9, 9] 
                                        });
                                        return L.marker(latlng, { icon });
                                    }
                                    // Default marker Leaflet (akan berwarna biru)
                                    return L.marker(latlng);
                                }
                                // Fallback untuk point geometry
                                else if (feature.geometry && feature.geometry.type === 'Point') {
                                    return L.circleMarker(latlng, { 
                                        ...style, 
                                        radius: pointRadius
                                    });
                                }
                                
                                // Default fallback
                                return L.marker(latlng);
                            }
                        }).addTo(previewMap);

                        // Fit bounds jika valid
                        const bounds = geoLayer.getBounds();
                        if (bounds && bounds.isValid()) {
                            previewMap.fitBounds(bounds, { padding: [10, 10], maxZoom: 12 });
                        } else {
                            // Fallback ke koordinat map jika ada
                            const lat = parseFloat(mapData.lat);
                            const lng = parseFloat(mapData.lng);
                            if (!isNaN(lat) && !isNaN(lng)) {
                                previewMap.setView([lat, lng], 10);
                            }
                        }
                    })
                    .catch(error => {
                        console.warn(`Tidak dapat memuat GeoJSON untuk peta ${mapData.id}:`, error);
                        
                        // Fallback: gunakan koordinat dasar dari map data
                        const lat = parseFloat(mapData.lat);
                        const lng = parseFloat(mapData.lng);
                        
                        if (!isNaN(lat) && !isNaN(lng)) {
                            const latlng = [lat, lng];
                            let fallbackLayer;
                            
                            // Buat layer berdasarkan tipe
                            if (mapData.layer_type === 'circle') {
                                fallbackLayer = L.circle(latlng, { 
                                    ...defaultStyle, 
                                    radius: safeNumber(mapData.radius, 1000) 
                                });
                            } 
                            else if (mapData.layer_type === 'circlemarker') {
                                fallbackLayer = L.circleMarker(latlng, { 
                                    ...defaultStyle, 
                                    radius: 6 
                                });
                            }
                            else if (mapData.layer_type === 'marker') {
                                if (mapData.icon_url && mapData.icon_url.trim() !== '') {
                                    const icon = L.icon({ 
                                        iconUrl: mapData.icon_url, 
                                        iconSize: [18, 18], 
                                        iconAnchor: [9, 9] 
                                    });
                                    fallbackLayer = L.marker(latlng, { icon });
                                } else {
                                    fallbackLayer = L.marker(latlng);
                                }
                            }
                            else {
                                // Default fallback
                                fallbackLayer = L.circleMarker(latlng, { ...defaultStyle, radius: 6 });
                            }
                            
                            fallbackLayer.addTo(previewMap);
                            previewMap.setView(latlng, 10);
                        }
                    })
                    .finally(() => {
                        // Pastikan ukuran peta benar
                        setTimeout(() => previewMap.invalidateSize(), 150);
                    });
            };

            // Inisialisasi peta untuk setiap data yang ada
            mapsData.forEach(initPreviewMap);

            // --- SCRIPT TAMBAHAN UNTUK UPDATE RADIO BUTTON ---
            document.querySelectorAll('.kategori-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const mapId = this.dataset.id;
                    const kategori = this.value;

                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('kategori', kategori);

                    fetch(`/maps/${mapId}/update-kategori`, {
                        method: 'POST', // Laravel akan baca sebagai PUT karena _method diisi
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