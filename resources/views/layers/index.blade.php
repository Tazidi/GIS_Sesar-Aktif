@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .preview-map {
            height: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }
        .layer-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        .layer-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }
        .feature-count {
            background-color: #3b82f6;
            color: white;
            border-radius: 9999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .map-container {
            position: relative;
            overflow: hidden;
        }
        .map-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .map-container:hover .map-overlay {
            opacity: 1;
        }
        .empty-map-label {
            background: transparent !important;
            border: none !important;
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

    {{-- Header Halaman --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Manajemen Layer
            </h1>
            <p class="text-sm text-gray-600 mt-1">
                Kelola layer dan lihat pratinjau geometri pada peta
            </p>
        </div>
        <a href="{{ route('layers.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Layer
        </a>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Layer</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $layers->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Fitur</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalFeatures }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Layer Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeLayersCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Container untuk Layer Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse ($layers as $layer)
            <div class="layer-card bg-white rounded-lg shadow-sm overflow-hidden">
                {{-- Header Card --}}
                <div class="p-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 text-lg mb-1">{{ $layer->nama_layer }}</h3>
                            <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                                {{ $layer->deskripsi ?? 'Tidak ada deskripsi' }}
                            </p>
                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                <span class="feature-count">
                                    {{ $layer->map_features_count }} Fitur
                                </span>
                                <span>
                                    Dibuat: {{ $layer->created_at->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pratinjau Peta --}}
                <div class="map-container">
                    <div id="preview-map-{{ $layer->id }}" class="preview-map"></div>
                    <div class="map-overlay">
                        <span class="text-sm">Klik untuk melihat detail</span>
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="p-4 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('layers.edit', $layer) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </a>
                        <div class="flex space-x-2">
                            <button onclick="showLayerDetails({{ $layer->id }})"
                                    class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Detail
                            </button>
                            <form action="{{ route('layers.destroy', $layer) }}" method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus layer ini? Semua data terkait akan ikut terhapus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow-sm p-8 text-center">
                <div class="flex flex-col items-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada layer</h3>
                    <p class="text-gray-500 mb-4">Silakan tambahkan layer baru untuk memulai.</p>
                    <a href="{{ route('layers.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Tambah Layer Pertama
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($layers->hasPages())
        <div class="mt-6 bg-white px-4 py-3 rounded-lg shadow-sm">
            {{ $layers->links() }}
        </div>
    @endif
</div>

{{-- Modal untuk Detail Layer --}}
<div id="layerDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Detail Layer</h3>
                <button onclick="closeLayerDetails()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modalContent">
                {{-- Konten akan diisi oleh JavaScript --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Data layer untuk JavaScript
const layersData = @json($layers->keyBy('id'));

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi peta pratinjau untuk setiap layer
    @foreach($layers as $layer)
        @if($layer->map_features_count > 0)
            initPreviewMap({{ $layer->id }}, @json($layer->mapFeatures));
        @else
            initEmptyMap({{ $layer->id }});
        @endif
    @endforeach
});

function initPreviewMap(layerId, features) {
    const map = L.map(`preview-map-${layerId}`, {
        zoomControl: false,
        attributionControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false
    }).setView([-6.9175, 107.6191], 10);

    // Tambahkan tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Grup untuk menampung semua fitur
    const featureGroup = L.featureGroup().addTo(map);

    // Render setiap fitur
    features.forEach(feature => {
        try {
            const geometry = typeof feature.geometry === 'string' ? 
                JSON.parse(feature.geometry) : feature.geometry;
            const properties = typeof feature.properties === 'string' ? 
                JSON.parse(feature.properties) : (feature.properties || {});
            const technicalInfo = feature.technical_info ? 
                (typeof feature.technical_info === 'string' ? 
                    JSON.parse(feature.technical_info) : feature.technical_info) : {};

            if (geometry && geometry.type) {
                let layer;

                switch (geometry.type.toLowerCase()) {
                    case 'point':
                        const radius = technicalInfo.radius || properties.radius;
                        if (radius) {
                            const circleStyle = {
                                color: technicalInfo.stroke_color || properties.stroke_color || '#3388ff',
                                fillColor: technicalInfo.fill_color || properties.fill_color || '#3388ff',
                                weight: technicalInfo.weight || properties.weight || 2,
                                opacity: technicalInfo.opacity || properties.opacity || 0.7,
                                fillOpacity: technicalInfo.opacity || properties.opacity || 0.2
                            };
                            layer = L.circle([geometry.coordinates[1], geometry.coordinates[0]], { ...circleStyle, radius });
                        } else {
                            const iconUrl = technicalInfo.icon_url || properties.icon_url || 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png';
                            const icon = L.icon({
                                iconUrl: iconUrl,
                                iconSize: [15, 25],
                                iconAnchor: [7, 25],
                                popupAnchor: [0, -25]
                            });
                            layer = L.marker([geometry.coordinates[1], geometry.coordinates[0]], { icon });
                        }
                        break;

                    case 'multipoint':
                        const iconUrlMulti = technicalInfo.icon_url || properties.icon_url || 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png';
                        const iconMulti = L.icon({
                            iconUrl: iconUrlMulti,
                            iconSize: [15, 25],
                            iconAnchor: [7, 25],
                            popupAnchor: [0, -25]
                        });
                        geometry.coordinates.forEach(coord => {
                            featureGroup.addLayer(L.marker([coord[1], coord[0]], { icon: iconMulti }));
                        });
                        break;

                    case 'linestring':
                        const polylineStyle = {
                            color: technicalInfo.stroke_color || properties.stroke_color || '#3388ff',
                            weight: technicalInfo.weight || properties.weight || 3,
                            opacity: technicalInfo.opacity || properties.opacity || 0.7
                        };
                        const lineCoords = geometry.coordinates.map(coord => [coord[1], coord[0]]);
                        layer = L.polyline(lineCoords, polylineStyle);
                        break;

                    case 'multilinestring':
                        const multiPolylineStyle = {
                            color: technicalInfo.stroke_color || properties.stroke_color || '#3388ff',
                            weight: technicalInfo.weight || properties.weight || 3,
                            opacity: technicalInfo.opacity || properties.opacity || 0.7
                        };
                        const multiLineCoords = geometry.coordinates.map(line => 
                            line.map(coord => [coord[1], coord[0]])
                        );
                        // PERBAIKAN: Gunakan L.polyline (bukan L.multiPolyline)
                        layer = L.polyline(multiLineCoords, multiPolylineStyle);
                        break;

                    case 'polygon':
                        const polygonStyle = {
                            color: technicalInfo.stroke_color || properties.stroke_color || '#3388ff',
                            fillColor: technicalInfo.fill_color || properties.fill_color || '#3388ff',
                            weight: technicalInfo.weight || properties.weight || 2,
                            opacity: technicalInfo.opacity || properties.opacity || 0.7,
                            fillOpacity: technicalInfo.opacity || properties.opacity || 0.2
                        };
                        const polyCoords = geometry.coordinates[0].map(coord => [coord[1], coord[0]]);
                        layer = L.polygon(polyCoords, polygonStyle);
                        break;
                        
                    case 'multipolygon':
                        const multiPolygonStyle = {
                            color: technicalInfo.stroke_color || properties.stroke_color || '#3388ff',
                            fillColor: technicalInfo.fill_color || properties.fill_color || '#3388ff',
                            weight: technicalInfo.weight || properties.weight || 2,
                            opacity: technicalInfo.opacity || properties.opacity || 0.7,
                            fillOpacity: technicalInfo.opacity || properties.opacity || 0.2
                        };
                        const multiPolyCoords = geometry.coordinates.map(polygon => 
                            polygon.map(ring => 
                                ring.map(coord => [coord[1], coord[0]])
                            )
                        );
                        // PERBAIKAN: Gunakan L.polygon (bukan L.multiPolygon)
                        layer = L.polygon(multiPolyCoords, multiPolygonStyle);
                        break;
                }

                if (layer) {
                    featureGroup.addLayer(layer);
                }
            }
        } catch (error) {
            console.error('Error rendering feature:', error);
        }
    });

    // Fit bounds ke semua fitur
    if (featureGroup.getLayers().length > 0) {
        map.fitBounds(featureGroup.getBounds(), { padding: [5, 5] });
    } else {
        map.setView([-6.9175, 107.6191], 5);
    }
}

function initEmptyMap(layerId) {
    const map = L.map(`preview-map-${layerId}`, {
        zoomControl: false,
        attributionControl: false,
        dragging: false,
        scrollWheelZoom: false
    }).setView([-6.9175, 107.6191], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Tambahkan marker dengan ikon khusus untuk layer kosong
    const emptyIcon = L.divIcon({
        html: '<div class="text-center text-gray-500 text-xs"><svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>Tidak ada geometri</div>',
        className: 'empty-map-label',
        iconSize: [100, 40],
        iconAnchor: [50, 20]
    });
    
    L.marker([-6.9175, 107.6191], {icon: emptyIcon}).addTo(map);
}

function showLayerDetails(layerId) {
    const layer = layersData[layerId];
    if (!layer) return;

    const modal = document.getElementById('layerDetailModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');

    modalTitle.textContent = `Detail: ${layer.nama_layer}`;
    
    let content = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <p class="mt-1 text-sm text-gray-900">${layer.deskripsi || 'Tidak ada deskripsi'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jumlah Fitur</label>
                <p class="mt-1 text-sm text-gray-900">${layer.map_features_count} fitur</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Dibuat Pada</label>
                <p class="mt-1 text-sm text-gray-900">${new Date(layer.created_at).toLocaleDateString('id-ID')}</p>
            </div>
        </div>
    `;

    modalContent.innerHTML = content;
    modal.classList.remove('hidden');
}

function closeLayerDetails() {
    document.getElementById('layerDetailModal').classList.add('hidden');
}

// Tutup modal ketika klik di luar
window.onclick = function(event) {
    const modal = document.getElementById('layerDetailModal');
    if (event.target === modal) {
        closeLayerDetails();
    }
}
</script>
@endsection