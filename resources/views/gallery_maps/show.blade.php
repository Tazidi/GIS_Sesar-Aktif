@extends('layouts.app')

@section('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Container & Layout - dipakai untuk mode Map */
        .map-gallery-container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
        }
        .map-header { 
            margin-bottom: 24px; 
            padding-bottom: 16px; 
            border-bottom: 1px solid #e5e7eb; 
        }
        .map-title { 
            font-size: 28px; 
            font-weight: 600; 
            color: #1f2937; 
            margin: 0 0 8px 0; 
            line-height: 1.2; 
        }
        .back-link { 
            display: inline-flex; 
            align-items: center; 
            color: #6b7280; 
            text-decoration: none; 
            font-size: 14px; 
            transition: color 0.2s ease; 
        }
        .back-link:hover { color: #374151; }
        .back-link::before { content: '←'; margin-right: 8px; }
        .map-wrapper { margin-bottom: 24px; }
        .map-container { 
            position: relative; 
            height: 600px; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); 
        }
        #map { height: 100%; width: 100%; }
        
        /* Modal Styles (mode Map) */
        .modal-overlay { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); 
            backdrop-filter: blur(4px); 
            z-index: 1050; 
        }
        .modal-content { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
            background: white; 
            border-radius: 16px; 
            width: 90%; 
            max-width: 500px; 
            max-height: 80vh; 
            overflow: hidden; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); 
        }
        .modal-header { 
            padding: 20px 24px 16px; 
            border-bottom: 1px solid #f3f4f6; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .modal-title { 
            font-size: 20px; 
            font-weight: 600; 
            color: #1f2937; 
            margin: 0; 
        }
        .modal-close { 
            background: none; 
            border: none; 
            font-size: 24px; 
            color: #9ca3af; 
            cursor: pointer; 
            padding: 4px; 
            border-radius: 6px; 
            transition: all 0.2s ease; 
        }
        .modal-close:hover { 
            background: #f3f4f6; 
            color: #6b7280; 
        }
        .modal-body { 
            padding: 20px 24px 24px; 
            max-height: calc(80vh - 70px); 
            overflow-y: auto; 
        }
        .leaflet-popup-content-wrapper { 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
        }
        .leaflet-popup-content { 
            margin: 16px; 
            font-size: 14px; 
            line-height: 1.4; 
        }
        
        .detail-item { 
            margin-bottom: 12px; 
            border-bottom: 1px solid #f3f4f6; 
            padding-bottom: 8px; 
        }
        .detail-label { 
            font-weight: bold; 
            color: #374151; 
            margin-bottom: 4px; 
        }
        .detail-value { color: #6b7280; }

        /* ---- Mode Proyek (adopsi projects.show, view-only) ---- */
        .proj-wrap { 
            max-width: 1120px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .proj-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 16px; 
        }
        .proj-title { 
            font-size: 28px; 
            font-weight: 800; 
            color: #1f2937; 
        }
        .proj-desc { 
            color: #6b7280; 
            margin-top: 4px; 
        }
        .proj-map-card { 
            background: white; 
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); 
            border-radius: 12px; 
            padding: 16px; 
            margin-bottom: 24px; 
        }
        #proj-map { 
            height: 450px; 
            border-radius: 8px; 
        }
        .loc-grid { 
            display: grid; 
            grid-template-columns: repeat(1, minmax(0, 1fr)); 
            gap: 16px; 
        }
        @media (min-width:768px) { 
            .loc-grid { 
                grid-template-columns: repeat(2, minmax(0, 1fr)); 
            } 
        }
        @media (min-width:1024px) { 
            .loc-grid { 
                grid-template-columns: repeat(3, minmax(0, 1fr)); 
            } 
        }
        .loc-card { 
            background: #fff; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); 
            display: flex; 
            flex-direction: column; 
        }
        .loc-img { 
            width: 100%; 
            height: 190px; 
            object-fit: cover; 
            background: #f3f4f6; 
            display: block; 
        }
        .loc-body { 
            padding: 14px; 
            display: flex; 
            flex-direction: column; 
            flex: 1; 
        }
        .loc-title { font-weight: 700; }
        .loc-text { 
            font-size: 14px; 
            color: #6b7280; 
            margin-top: 6px; 
            flex: 1; 
        }
        .loc-geo { 
            font-size: 12px; 
            color: #9ca3af; 
            margin-top: 8px; 
        }
    
        /* Kontainer untuk custom layer control */
        .custom-layer-control {
            max-height: 300px;
            overflow-y: auto;
            background: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            font-size: 14px;
        }
        .custom-layer-control h4 {
            margin: 6px 0;
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }
        .custom-layer-control label {
            display: block;
            margin-bottom: 4px;
        }

        /* Improved Legend box styles */
        .legend-box {
            position: absolute;
            bottom: 12px;
            left: 12px;
            z-index: 1000;
            max-height: 300px;
            min-width: 200px;
            overflow-y: auto;
            background: rgba(255,255,255,0.95);
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            font-size: 13px;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .legend-title {
            font-weight: bold; 
            margin-bottom: 12px;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 6px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .legend-item:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .legend-item:last-child {
            margin-bottom: 0;
        }

        .legend-symbol {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            border: 2px solid #333;
            border-radius: 4px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .legend-symbol img {
            width: 16px;
            height: 16px;
            object-fit: cover;
        }

        .legend-text {
            flex: 1;
            line-height: 1.3;
        }

        .legend-text small {
            color: #666;
            font-size: 11px;
            display: block;
            margin-top: 2px;
        }

        .legend-item.inactive {
            opacity: 0.4;
            background-color: rgba(128,128,128,0.1);
        }

        .legend-item.inactive .legend-text {
            color: #999;
        }

        /* Improved button styles */
        .btn-detail {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 12px;
            display: block;
            width: 100%;
            text-align: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-detail:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
        }

        .btn-detail:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .legend-box { 
                max-width: 45vw; 
                font-size: 12px;
                padding: 10px 12px;
                min-width: 180px;
            }
            
            .legend-symbol {
                width: 16px;
                height: 16px;
                margin-right: 6px;
            }
            
            .legend-symbol img {
                width: 14px;
                height: 14px;
            }
            
            .legend-text small {
                font-size: 10px;
            }
        }

        /* Custom scrollbar for legend */
        .legend-box::-webkit-scrollbar {
            width: 6px;
        }

        .legend-box::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.1);
            border-radius: 3px;
        }

        .legend-box::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.3);
            border-radius: 3px;
        }

        .legend-box::-webkit-scrollbar-thumb:hover {
            background: rgba(0,0,0,0.5);
        }
    </style>
@endsection

@section('content')

{{-- ===================== MODE: MAP (eksisting) ===================== --}}
@if(isset($map))
    <div class="map-gallery-container">
        <header class="map-header">
            <h1 class="map-title">{{ $map->name }}</h1>
            <a href="{{ route('gallery_maps.index') }}" class="back-link">Kembali ke Galeri Peta</a>
        </header>

        @php
            // KUNCI PERBAIKAN: Filter layer di sisi server.
            $activeLayerIds = $map->features->pluck('layers')->flatten()->pluck('id')->unique();
            $rawActiveLayers = $map->layers->whereIn('id', $activeLayerIds);
            
            // PERBAIKAN BARU: Pastikan layer unik berdasarkan nama untuk tampilan di Blade.
            // Ini mencegah duplikasi di legenda jika ada >1 layer dengan nama yang sama.
            $activeLayers = $rawActiveLayers->unique(function ($item) {
                return $item['nama_layer'] ?? $item['name'];
            });
        @endphp

        <div class="main-content-wrapper">
            <!-- Kolom Peta dan Legenda -->
            <div class="map-wrapper">
                <div class="map-container">
                    <div id="map"></div>
                    
                    <div class="legend-box" id="legend-box">
                        <div class="legend-title">Keterangan Peta</div>
                        <div id="legend-content">
                            @forelse($activeLayers as $layer)
                                @php 
                                    $p = $layer->pivot ?? null; 
                                    $layerType = $p->layer_type ?? 'marker';
                                    $fillColor = $p->fill_color ?? $map->fill_color ?? '#ff0000';
                                    $strokeColor = $p->stroke_color ?? $map->stroke_color ?? '#000000';
                                    $opacity = $p->opacity ?? $map->opacity ?? 0.8;
                                    $weight = $p->weight ?? $map->weight ?? 2;
                                    $iconUrl = $p->icon_url ?? $map->icon_url ?? '';
                                @endphp
                                
                                <div class="legend-item" data-legend-layer="{{ $layer->nama_layer ?? $layer->name }}">
                                    <div class="legend-symbol" style="
                                        @if ($layerType == 'marker')
                                            background-color: {{ $fillColor }}; border-color: {{ $strokeColor }};
                                        @elseif ($layerType == 'circle')
                                            border-color: {{ $strokeColor }}; background-color: {{ $fillColor }}; opacity: {{ $opacity }}; border-width: {{ $weight }}px;
                                        @elseif ($layerType == 'polyline')
                                            background-color: {{ $strokeColor }}; height: {{ min($weight, 18) }}px; border-width: 0;
                                        @else
                                            background-color: {{ $fillColor }}; border-color: {{ $strokeColor }}; opacity: {{ $opacity }}; border-width: {{ $weight }}px;
                                        @endif
                                    ">
                                        @if($layerType == 'marker' && $iconUrl)
                                            <img src="{{ $iconUrl }}" alt="icon" loading="lazy">
                                        @endif
                                    </div>
                                    <div class="legend-text">
                                        {{ $layer->nama_layer ?? $layer->name ?? 'Layer '.$layer->id }}
                                        <small>
                                            @if ($layerType == 'marker') 📍 Penanda Lokasi
                                            @elseif ($layerType == 'circle') ⭕ Lingkaran
                                            @elseif ($layerType == 'polyline') 📏 Garis/Jalur
                                            @else 🗺️ Area/Wilayah
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @empty
                                @if($map->features->isNotEmpty())
                                <div class="legend-item" data-legend-layer="Fitur Peta">
                                    <div class="legend-symbol" style="background-color: {{ $map->fill_color ?? '#ff0000' }}; border-color: {{ $map->stroke_color ?? '#000000' }};"></div>
                                    <div class="legend-text">
                                        Fitur Peta
                                        <small>📍 Data Peta</small>
                                    </div>
                                </div>
                                @endif
                            @endforelse

                            <div class="legend-item" data-legend-layer="BMKG: 15 Gempa">
                                <div class="legend-symbol" style="background: transparent; border: none; padding: 1px;">
                                    <img src="{{ asset('bmkg/earthquake.png') }}" alt="BMKG" loading="lazy">
                                </div>
                                <div class="legend-text">
                                    BMKG: 15 Gempa
                                    <small>🌍 Info Gempa Terkini</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto bg-white p-6 shadow-lg rounded-lg mt-6">
            <h2 class="text-lg font-bold text-gray-800 mb-2">Deskripsi Peta</h2>
            <p class="text-sm text-gray-600">{{ $map->description ?? 'Belum ada deskripsi yang tersedia untuk peta ini.' }}</p>
        </div>

        <div id="detail-modal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Detail Informasi</h2>
                    <button class="modal-close" onclick="closeModal()">×</button>
                </div>
                <div class="modal-body">
                    <div id="detail-content"></div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="container text-center py-5">
        <p>Peta tidak ditemukan.</p>
    </div>
@endif

{{-- ===================== MODE: PROYEK (adopsi projects.show – view-only) ===================== --}}
@if(isset($project))
    <div class="proj-wrap">
        <div class="mb-3">
            <a href="{{ route('gallery_maps.index') }}" class="back-link">Kembali ke Galeri Peta</a>
        </div>

        <div class="proj-header">
            <div>
                <h1 class="proj-title">{{ $project->name }}</h1>
                <p class="proj-desc">{{ $project->description }}</p>
            </div>
        </div>

        <div class="proj-map-card">
            <div id="proj-map"></div>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Lokasi</h2>
        <div class="loc-grid">
            @forelse ($project->surveyLocations as $location)
                <a href="{{ route('gallery_maps.projects.locations.show', [$project, $location->id]) }}" class="loc-card" style="text-decoration: none; color: inherit;">
                    @if($location->primary_image)
                        <img class="loc-img" src="{{ asset('survey/' . $location->primary_image) }}" alt="{{ $location->nama }}">
                    @else
                        <div class="loc-img"></div>
                    @endif
                    <div class="loc-body">
                        <div class="loc-title">{{ $location->nama }}</div>
                        <p class="loc-text">{{ $location->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                        <p class="loc-geo">Lat: {{ $location->geometry['lat'] ?? 'N/A' }}, Lng: {{ $location->geometry['lng'] ?? 'N/A' }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white p-8 rounded-lg shadow-md text-center">
                    <p class="text-gray-500">Belum ada lokasi yang ditambahkan ke proyek ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Data untuk peta proyek (adopsi dari projects.show) --}}
    @php
        $locationsForMap = $project->surveyLocations->map(function($loc) {
            return [
                'lat' => $loc->geometry['lat'] ?? 0,
                'lng' => $loc->geometry['lng'] ?? 0,
                'nama' => $loc->nama,
                'image' => $loc->primary_image ? asset('survey/' . $loc->primary_image) : null,
                'user_name' => $loc->user->name ?? 'Tidak diketahui'
            ];
        });
    @endphp
@endif

@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @if(isset($map))
        {{-- Siapkan data di sisi PHP --}}
        @php
            $mapsData = collect([$map])->map(function($mapItem) {
                return [
                    'unique_id'   => "map-{$mapItem->id}",
                    'id'          => $mapItem->id,
                    'name'        => $mapItem->name,
                    'description' => $mapItem->description,
                    'image_path'  => $mapItem->image_path ? asset($mapItem->image_path) : '',
                    'layer_type'  => $mapItem->layer_type ?? 'marker',
                    'stroke_color'=> $mapItem->stroke_color ?? '#3388ff',
                    'fill_color'  => $mapItem->fill_color ?? '#3388ff',
                    'opacity'     => $mapItem->opacity ?? 0.8,
                    'weight'      => $mapItem->weight ?? 2,
                    'radius'      => $mapItem->radius ?? 300,
                    'icon_url'    => $mapItem->icon_url ?? '',
                    'lat'         => $mapItem->lat ?? null,
                    'lng'         => $mapItem->lng ?? null,
                    'geometry'    => $mapItem->geometry ? (is_string($mapItem->geometry) ? json_decode($mapItem->geometry, true) : $mapItem->geometry) : null,
                    'caption'     => $mapItem->caption ?? '',
                    'technical_info' => $mapItem->technical_info ?? '',
                    'features'    => $mapItem->features->map(function($feature) {
                        return [
                            'geometry' => $feature->geometry ? (is_string($feature->geometry) ? json_decode($feature->geometry, true) : $feature->geometry) : null,
                            'properties' => $feature->properties ? (is_string($feature->properties) ? json_decode($feature->properties, true) : $feature->properties) : null,
                            'image_path' => $feature->image_path ? asset($feature->image_path) : '',
                            'caption' => $feature->caption ?? '',
                            'technical_info' => $feature->technical_info ?? '',
                            'layer_ids' => $feature->layers->pluck('id')->toArray()
                        ];
                    })->toArray(),
                    'layers'      => $mapItem->layers->map(function($layer) {
                        return [
                            'id' => $layer->id,
                            'name' => $layer->nama_layer ?? $layer->name ?? 'Layer '.$layer->id,
                            'nama_layer' => $layer->nama_layer ?? $layer->name ?? 'Layer '.$layer->id,
                            'type' => $layer->pivot->layer_type ?? 'marker',
                            'stroke_color' => $layer->pivot->stroke_color ?? '',
                            'fill_color' => $layer->pivot->fill_color ?? '',
                            'opacity' => $layer->pivot->opacity ?? null,
                            'weight' => $layer->pivot->weight ?? null,
                            'radius' => $layer->pivot->radius ?? null,
                            'icon_url' => $layer->pivot->icon_url ?? '',
                            'lat' => $layer->pivot->lat ?? null,
                            'lng' => $layer->pivot->lng ?? null
                        ];
                    })->toArray()
                ];
            })->toArray();
        @endphp

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapsData = @json($mapsData);

            // helper: parse technical_info yang mungkin string JSON atau object
            const parseTechnicalInfo = (val) => {
                if (!val) return {};
                if (typeof val === 'object') return val;
                try {
                    return JSON.parse(val);
                } catch (e) {
                    return {};
                }
            };

            const safeNumber = (v, fallback) => {
                const n = Number(v);
                return Number.isFinite(n) ? n : fallback;
            };

            const BMKG_ICON_URL = "{{ asset('bmkg/earthquake.png') }}";

            // Initialize main map
            const mapEl = document.getElementById('map');
            if (!mapEl) {
                console.warn('No #map element found in DOM');
                return;
            }

            const map = L.map('map', { 
                preferCanvas: true, 
                zoomControl: true 
            }).setView([-2.5, 117], 5);

            // Base layers
            const baseLayers = {
                "Google Maps": L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { 
                    maxZoom: 20, 
                    subdomains: ['mt0','mt1','mt2','mt3'], 
                    attribution: '&copy; Google' 
                }),
                "Google Satellite": L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { 
                    maxZoom: 20, 
                    subdomains: ['mt0','mt1','mt2','mt3'], 
                    attribution: '&copy; Google' 
                }),
                "OpenTopoMap": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { 
                    maxZoom: 17, 
                    attribution: '© OpenTopoMap' 
                })
            };
            baseLayers["Google Satellite"].addTo(map);

            const layerGroups = {};
            const allBounds = [];
            const allMarkers = [];

            const getStyleFrom = (props = {}, tech = {}, mapInfo = {}) => {
                // kemungkinan field warna bisa bernama stroke_color, fill_color, color, atau color_hex
                const stroke_color = tech.stroke_color || tech.color || tech.color_hex || props.stroke_color || props.color || mapInfo.stroke_color || '#3388ff';
                const fill_color   = tech.fill_color   || tech.color || tech.color_hex || props.fill_color   || props.color || mapInfo.fill_color   || stroke_color;
                const weight       = safeNumber(tech.weight ?? props.weight ?? mapInfo.weight, 3);
                const opacity      = (typeof tech.opacity !== 'undefined' ? tech.opacity : (typeof props.opacity !== 'undefined' ? props.opacity : (mapInfo.opacity ?? 0.8)));
                const fillOpacity  = (typeof tech.fill_opacity !== 'undefined' ? tech.fill_opacity : (typeof props.fill_opacity !== 'undefined' ? props.fill_opacity : (mapInfo.fill_opacity ?? 0.3)));

                return {
                    color: stroke_color,
                    fillColor: fill_color,
                    weight,
                    opacity,
                    fillOpacity: fillOpacity,
                };
            };

            const escapeHtml = (text) => {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            };

            const attachDetailPopup = (layerOrMarker, props = {}, data = {}) => {
                const merged = { ...data, ...props };
                const encoded = encodeURIComponent(JSON.stringify({ props: merged, data: merged })).replace(/'/g, "%27");
                const title = merged.Name || merged.name || merged.title || merged.nama || 'Fitur';

                if (layerOrMarker && layerOrMarker.bindPopup) {
                    const popupContent = `
                        <div style="font-weight:bold; margin-bottom: 8px;">${escapeHtml(title)}</div>
                        <button class="btn-detail" onclick="window.openDetailModal('${encoded}')">Selengkapnya</button>
                    `;
                    layerOrMarker.bindPopup(popupContent, { minWidth: 220 });
                }
            };

            const addProducedLayerToGroup = (produced, targetGroup) => {
                if (!produced || !targetGroup) return;
                if (typeof produced.getLayers === 'function') {
                    produced.getLayers().forEach(ch => {
                        targetGroup.addLayer(ch);
                        if (ch.getLatLng) {
                            allMarkers.push(ch);
                        }
                    });
                } else {
                    targetGroup.addLayer(produced);
                    if (produced.getLatLng) {
                        allMarkers.push(produced);
                    }
                }
            };

            const leafletLayerControl = L.control.layers(baseLayers, {}, { 
                collapsed: true, 
                position: 'topright' 
            }).addTo(map);

            // Process map data and features
            mapsData.forEach(mapInfo => {
                (mapInfo.layers || []).forEach(layerInfo => {
                    const layerName = layerInfo.nama_layer || layerInfo.name || ('Layer ' + layerInfo.id);
                    if (!layerGroups[layerName]) {
                        layerGroups[layerName] = L.layerGroup();
                    }
                });

                if ((!mapInfo.layers || mapInfo.layers.length === 0) && (mapInfo.features || []).length > 0) {
                    if (!layerGroups['Fitur']) layerGroups['Fitur'] = L.layerGroup();
                }

                const locationTracker = new Map();

                (mapInfo.features || []).forEach(featureData => {
                    try {
                        const props = featureData.properties || {};
                        const featureLayerIds = featureData.layer_ids || [];
                        let assigned = false;

                        featureLayerIds.forEach(layerId => {
                            const matched = (mapInfo.layers || []).find(x => String(x.id) === String(layerId));
                            if (matched) {
                                const layerName = matched.nama_layer || matched.name || ('Layer ' + matched.id);
                                if (!layerGroups[layerName]) {
                                    layerGroups[layerName] = L.layerGroup();
                                }
                                
                                let producedClone = null;
                                if (featureData.geometry) {
                                    const gj = { 
                                        type: 'Feature', 
                                        geometry: featureData.geometry, 
                                        properties: featureData.properties || {} 
                                    };
                                    
                                    const tech = parseTechnicalInfo(featureData.technical_info);
                                    const style = getStyleFrom(props, tech, mapInfo);
                                    
                                    producedClone = L.geoJSON(gj, {
                                        pointToLayer: (feature, latlng) => {
                                            const featureTech = parseTechnicalInfo(feature.properties?.technical_info);
                                            const featureProps = feature.properties || {};
                                            const featureStyle = getStyleFrom(featureProps, featureTech, mapInfo);

                                            // Tentukan jenis geometri yang diharapkan:
                                            const geomTypeRaw = (featureTech.geometry_type || featureProps.geometry_type || featureProps.layer_type || matched.type || mapInfo.layer_type || (feature.geometry && feature.geometry.type === 'Point' ? 'marker' : '')).toString().toLowerCase();
                                            const geomType = geomTypeRaw.replace(/[_\-]/g, '').trim();

                                            const iconUrl = featureTech.icon_url || featureProps.icon_url || matched.icon_url || mapInfo.icon_url || '';
                                            const radius = safeNumber(featureTech.radius ?? featureProps.radius ?? matched.radius ?? mapInfo.radius, 300);
                                            const pointRadius = safeNumber(featureTech.point_radius ?? featureProps.point_radius ?? featureProps.radius ?? matched.point_radius ?? matched.radius ?? mapInfo.point_radius ?? mapInfo.radius ?? 5, 5);

                                            if (geomType === 'circle') {
                                                return L.circle(latlng, {
                                                    ...featureStyle,
                                                    radius: radius
                                                });
                                            }

                                            if (geomType === 'circlemarker' || geomType === 'circlemark') {
                                                return L.circleMarker(latlng, {
                                                    ...featureStyle,
                                                    radius: pointRadius
                                                });
                                            }

                                            if (geomType === 'marker' || geomType === 'point') {
                                                if (iconUrl && iconUrl.toString().trim() !== '' && !iconUrl.includes('marker-survey.png')) {
                                                    const icon = L.icon({
                                                        iconUrl: iconUrl,
                                                        iconSize: [18, 18],
                                                        iconAnchor: [9, 9]
                                                    });
                                                    return L.marker(latlng, { icon });
                                                }
                                                return L.marker(latlng);
                                            }

                                            // Fallback untuk Point geometry
                                            if (feature.geometry && feature.geometry.type && feature.geometry.type.toLowerCase() === 'point') {
                                                return L.circleMarker(latlng, {
                                                    ...featureStyle,
                                                    radius: pointRadius
                                                });
                                            }

                                            return L.marker(latlng);
                                        },
                                        style: (feature) => {
                                            const featureTech = parseTechnicalInfo(feature.properties?.technical_info);
                                            const featureProps = feature.properties || {};
                                            const featureStyle = getStyleFrom(featureProps, featureTech, mapInfo);
                                            
                                            const geomType = feature.geometry?.type || '';
                                            if (geomType === 'Polygon' || geomType === 'MultiPolygon') {
                                                return {
                                                    color: featureStyle.color,
                                                    fillColor: featureStyle.fillColor,
                                                    weight: featureStyle.weight,
                                                    opacity: featureStyle.opacity,
                                                    fillOpacity: featureStyle.fillOpacity
                                                };
                                            } else if (geomType === 'LineString' || geomType === 'MultiLineString') {
                                                return {
                                                    color: featureStyle.color,
                                                    weight: featureStyle.weight,
                                                    opacity: featureStyle.opacity,
                                                    fillOpacity: 0
                                                };
                                            }
                                            return featureStyle;
                                        },
                                        onEachFeature: (feature, layer) => {
                                            attachDetailPopup(layer, { 
                                                ...featureData.properties, 
                                                feature_image_path: featureData.image_path || '', 
                                                caption: featureData.caption || '', 
                                                technical_info: featureData.technical_info || '',
                                                layer_name: matched.nama_layer || matched.name || 'Unknown Layer'
                                            });
                                        }
                                    });
                                }
                                
                                if (producedClone) {
                                    addProducedLayerToGroup(producedClone, layerGroups[layerName]);
                                    assigned = true;

                                    if (producedClone.getBounds && typeof producedClone.getBounds === 'function') {
                                        const b = producedClone.getBounds();
                                        if (b && b.isValid && b.isValid()) {
                                            allBounds.push(producedClone);
                                        }
                                    } else if (producedClone.getLayers) {
                                        producedClone.getLayers().forEach(ch => { 
                                            if (ch.getBounds) allBounds.push(ch); 
                                        });
                                    }
                                }
                            }
                        });

                        // Fallback untuk fitur tanpa layer_ids
                        if (!assigned && layerGroups['Fitur']) {
                            let producedClone = null;
                            if (featureData.geometry) {
                                const gj = { 
                                    type: 'Feature', 
                                    geometry: featureData.geometry, 
                                    properties: featureData.properties || {} 
                                };
                                const tech = parseTechnicalInfo(featureData.technical_info);
                                const style = getStyleFrom(props, tech, mapInfo);
                                
                                producedClone = L.geoJSON(gj, {
                                    pointToLayer: (feature, latlng) => {
                                        const featureTech = parseTechnicalInfo(feature.properties?.technical_info);
                                        const featureProps = feature.properties || {};
                                        const featureStyle = getStyleFrom(featureProps, featureTech, mapInfo);

                                        const geomTypeRaw = (featureTech.geometry_type || featureProps.geometry_type || featureProps.layer_type || mapInfo.layer_type || (feature.geometry && feature.geometry.type === 'Point' ? 'marker' : '')).toString().toLowerCase();
                                        const geomType = geomTypeRaw.replace(/[_\-]/g, '').trim();

                                        const iconUrl = featureTech.icon_url || featureProps.icon_url || mapInfo.icon_url || '';
                                        const radius = safeNumber(featureTech.radius ?? featureProps.radius ?? mapInfo.radius, 300);
                                        const pointRadius = safeNumber(featureTech.point_radius ?? featureProps.point_radius ?? featureProps.radius ?? mapInfo.point_radius ?? mapInfo.radius ?? 5, 5);

                                        if (geomType === 'circle') {
                                            return L.circle(latlng, {
                                                ...featureStyle,
                                                radius: radius
                                            });
                                        }

                                        if (geomType === 'circlemarker' || geomType === 'circlemark') {
                                            return L.circleMarker(latlng, {
                                                ...featureStyle,
                                                radius: pointRadius
                                            });
                                        }

                                        if (geomType === 'marker' || geomType === 'point') {
                                            if (iconUrl && iconUrl.toString().trim() !== '' && !iconUrl.includes('marker-survey.png')) {
                                                const icon = L.icon({
                                                    iconUrl: iconUrl,
                                                    iconSize: [18, 18],
                                                    iconAnchor: [9, 9]
                                                });
                                                return L.marker(latlng, { icon });
                                            }
                                            return L.marker(latlng);
                                        }

                                        if (feature.geometry && feature.geometry.type && feature.geometry.type.toLowerCase() === 'point') {
                                            return L.circleMarker(latlng, {
                                                ...featureStyle,
                                                radius: pointRadius
                                            });
                                        }

                                        return L.marker(latlng);
                                    },
                                    style: (feature) => {
                                        const featureTech = parseTechnicalInfo(feature.properties?.technical_info);
                                        const featureProps = feature.properties || {};
                                        const featureStyle = getStyleFrom(featureProps, featureTech, mapInfo);
                                        
                                        const geomType = feature.geometry?.type || '';
                                        if (geomType === 'Polygon' || geomType === 'MultiPolygon') {
                                            return {
                                                color: featureStyle.color,
                                                fillColor: featureStyle.fillColor,
                                                weight: featureStyle.weight,
                                                opacity: featureStyle.opacity,
                                                fillOpacity: featureStyle.fillOpacity
                                            };
                                        } else if (geomType === 'LineString' || geomType === 'MultiLineString') {
                                            return {
                                                color: featureStyle.color,
                                                weight: featureStyle.weight,
                                                opacity: featureStyle.opacity,
                                                fillOpacity: 0
                                            };
                                        }
                                        return featureStyle;
                                    },
                                    onEachFeature: (feature, layer) => {
                                        attachDetailPopup(layer, { 
                                            ...featureData.properties, 
                                            feature_image_path: featureData.image_path || '', 
                                            caption: featureData.caption || '', 
                                            technical_info: featureData.technical_info || '',
                                            layer_name: 'Fitur'
                                        });
                                    }
                                });
                            }
                            
                            if (producedClone) {
                                addProducedLayerToGroup(producedClone, layerGroups['Fitur']);
                                if (producedClone.getBounds && typeof producedClone.getBounds === 'function') {
                                    const b = producedClone.getBounds();
                                    if (b && b.isValid && b.isValid()) {
                                        allBounds.push(producedClone);
                                    }
                                } else if (producedClone.getLayers) {
                                    producedClone.getLayers().forEach(ch => { 
                                        if (ch.getBounds) allBounds.push(ch); 
                                    });
                                }
                            }
                        }

                    } catch (err) {
                        console.error('Error processing feature', err, featureData);
                    }
                });
            });

            // Add layer groups to map and layer control
            Object.keys(layerGroups).forEach(layerName => {
                leafletLayerControl.addOverlay(layerGroups[layerName], layerName);
                layerGroups[layerName].addTo(map);
            });

            // BMKG overlay
            const bmkgGroup = L.layerGroup();
            leafletLayerControl.addOverlay(bmkgGroup, "BMKG: 15 Gempa");
            bmkgGroup.addTo(map);

            // BMKG data
            fetch('https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.json')
                .then(r => r.json())
                .then(data => {
                    const list = data?.Infogempa?.gempa?.slice?.(0,15) || [];
                    const gempaIcon = L.icon({ 
                        iconUrl: BMKG_ICON_URL, 
                        iconSize:[36,36], 
                        iconAnchor:[18,36] 
                    });
                    
                    list.forEach(g => {
                        try {
                            const [lat, lng] = (g.Coordinates || '').split(',').map(s => parseFloat(s.trim()));
                            if (!isNaN(lat) && !isNaN(lng)) {
                                const m = L.marker([lat, lng], { icon: gempaIcon }).bindPopup(`
                                    <div style="font-weight:bold">Gempa Bumi</div>
                                    <div style="font-size:13px">
                                        <div><b>Tanggal:</b> ${escapeHtml(g.Tanggal)}</div>
                                        <div><b>Jam:</b> ${escapeHtml(g.Jam)}</div>
                                        <div><b>Magnitude:</b> ${escapeHtml(g.Magnitude)}</div>
                                        <div><b>Kedalaman:</b> ${escapeHtml(g.Kedalaman)}</div>
                                        <div><b>Wilayah:</b> ${escapeHtml(g.Wilayah)}</div>
                                    </div>
                                `);
                                bmkgGroup.addLayer(m);
                                allBounds.push(m);
                            }
                        } catch (e) { /* Abaikan */ }
                    });
                })
                .catch(err => console.warn('BMKG fetch failed', err));

            // Fit bounds setelah semua layer dimuat
            setTimeout(() => {
                try {
                    if (allBounds.length > 0) {
                        const featureLayers = allBounds.filter(x => x && (x.getBounds || x.getLatLng));
                        if (featureLayers.length > 0) {
                            const fg = L.featureGroup(featureLayers);
                            map.fitBounds(fg.getBounds(), { padding: [40,40], maxZoom: 14 });
                        }
                    }
                } catch (e) { 
                    console.warn('Error fitting bounds:', e);
                }
            }, 500);

            // Modal functions (tetap sama seperti sebelumnya)
            function openModal(data = {}, props = {}) {
                try {
                    const modal = document.getElementById('detail-modal');
                    const content = document.getElementById('detail-content');
                    if (!modal || !content) return console.warn('Modal elements missing');

                    const merged = { ...data, ...props };
                    
                    function capitalizeKey(str) {
                        return str
                            .replace(/_/g, ' ')
                            .replace(/\w\S*/g, w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase());
                    }

                    const title = merged.Name || merged.name || merged.title || merged.nama || '';
                    const image = merged.feature_image_path || merged.image_path || merged.image || '';
                    const caption = merged.caption || merged.keterangan || merged.description || '';
                    const layerName = merged.layer_name || '';

                    let html = '';

                    if (title) html += `
                        <div class="detail-item">
                            <div class="detail-label">Nama</div>
                            <div class="detail-value">${escapeHtml(title)}</div>
                        </div>`;

                    if (layerName) html += `
                        <div class="detail-item">
                            <div class="detail-label">Layer</div>
                            <div class="detail-value">${escapeHtml(layerName)}</div>
                        </div>`;
                    
                    let tech = merged.technical_info;
                    if (tech) {
                        try {
                            const parsed = typeof tech === 'string' ? JSON.parse(tech) : tech;
                            if (parsed && typeof parsed === 'object') {
                                const allowedKeys = ['panjang_sesar', 'lebar_sesar', 'tipe', 'nmax'];
                                const filteredEntries = Object.entries(parsed)
                                    .filter(([k, v]) => allowedKeys.includes(k) && v !== null && v !== '');
                                if (filteredEntries.length > 0) {
                                    const listItems = filteredEntries
                                        .map(([k, v]) => {
                                            let label = capitalizeKey(k);
                                            if (k === 'nmax') label = 'Potensi Magnitudo Maksimum';
                                            return `<li><b>${escapeHtml(label)}</b>: ${escapeHtml(String(v))}</li>`;
                                        })
                                        .join('');
                                    html += `
                                    <div class="detail-item">
                                        <div class="detail-label">Informasi Teknis</div>
                                        <div class="detail-value"><ul>${listItems}</ul></div>
                                    </div>`;
                                }
                            } else if (tech.trim() !== '') {
                                html += `<div class="detail-item">
                                            <div class="detail-label">Technical Info</div>
                                            <div class="detail-value">${escapeHtml(tech)}</div>
                                        </div>`;
                            }
                        } catch (e) {
                            html += `<div class="detail-item">
                                        <div class="detail-label">Technical Info</div>
                                        <div class="detail-value">${escapeHtml(tech)}</div>
                                    </div>`;
                        }
                    }

                    Object.keys(merged).forEach(key => {
                        if (['feature_image_path','caption','technical_info','layer_name','name','nama','title','Name','icon_url','image','image_path'].includes(key)) return;
                        const val = merged[key];
                        if (val !== null && val !== '' && val !== undefined) {
                            html += `<div class="detail-item">
                                        <div class="detail-label">${escapeHtml(capitalizeKey(key))}</div>
                                        <div class="detail-value">${escapeHtml(String(val))}</div>
                                    </div>`;
                        }
                    });
                    
                    if (image) html += `
                        <div class="detail-item">
                            <div class="detail-label">Foto</div>
                            <div class="detail-value">
                                <img src="${escapeHtml(image)}" alt="${escapeHtml(title)}" style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;">
                            </div>
                        </div>`;

                    if (caption) html += `
                        <div class="detail-item">
                            <div class="detail-label">Keterangan</div>
                            <div class="detail-value">${escapeHtml(caption)}</div>
                        </div>`;

                    if (!html.trim()) html = '<p class="text-gray-500 text-sm">Tidak ada detail untuk ditampilkan</p>';

                    content.innerHTML = html;
                    modal.style.display = 'block';
                } catch (err) {
                    console.warn('openModal error', err, data, props);
                }
            }

            function closeModal() {
                const modal = document.getElementById('detail-modal');
                if (!modal) return;
                modal.style.display = 'none';
                const content = document.getElementById('detail-content');
                if (content) content.innerHTML = '';
            }

            window.closeModal = closeModal;
            window.openDetailModal = function(encodedJsonString) { 
                try {
                    const parsed = JSON.parse(decodeURIComponent(encodedJsonString));
                    openModal(parsed.data, parsed.props);
                } catch (err) {
                    console.error('Error parsing modal data:', err);
                }
            };

            // Event listeners
            document.addEventListener('click', function (ev) {
                if (ev.target && ev.target.matches('.modal-close')) closeModal();
                if (ev.target && ev.target.id === 'detail-modal') closeModal();
            });

            document.addEventListener('keydown', function(ev) {
                if (ev.key === 'Escape') {
                    closeModal();
                }
            });

            // Resize handling
            setTimeout(() => map.invalidateSize(), 400);
            window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 150));
        });
        </script>
    @endif

    {{-- Script untuk project map (tetap sama) --}}
    @if(isset($project))
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const locations = @json($locationsForMap ?? []);

            if (locations.length > 0) {
                const map = L.map('proj-map').setView([locations[0].lat, locations[0].lng], 13);

                // === Basemap ===
                const googleMaps = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    attribution: '© Google Maps',
                    maxZoom: 20
                });

                const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    attribution: '© Google Satellite',
                    maxZoom: 20
                }).addTo(map); // default aktif

                const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    attribution: 'Map data: © OSM contributors, SRTM | Map style: © OpenTopoMap',
                    maxZoom: 17
                });

                const baseMaps = {
                    "Google Maps": googleMaps,
                    "Google Satellite": googleSatellite,
                    "OpenTopoMap": topo
                };

                // === Overlay lokasi ===
                const overlayMaps = {};
                const markers = [];

                const grouped = {}; // { user_name: [marker1, marker2, ...] }

                let manualMarker = null;

                // Render semua lokasi awal
                locations.forEach(loc => {
                    let popupContent = `<b class="font-semibold">${loc.nama}</b>`;
                    if (loc.image) {
                        popupContent += `<br><img src="${loc.image}" alt="${loc.nama}" style="width:120px; margin-top:8px; border-radius: 4px;">`;
                    }

                    const marker = L.marker([loc.lat, loc.lng]).bindPopup(popupContent);
                    if (!grouped[loc.user_name]) grouped[loc.user_name] = L.layerGroup();
                    grouped[loc.user_name].addLayer(marker);
                    markers.push(marker);
                });

                // Overlay + layer control
                Object.keys(grouped).forEach(user => {
                    overlayMaps[user] = grouped[user];
                    grouped[user].addTo(map);
                });
                L.control.layers(baseMaps, overlayMaps, { position: 'topright', collapsed: true }).addTo(map);

                // Auto zoom awal
                if (markers.length > 0) {
                    const group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }

                // === Tambahan: Input Listener ===
                const latInput = document.querySelector('input[name="latitude"]');
                const lngInput = document.querySelector('input[name="longitude"]');

                function updateManualMarker() {
                    const lat = parseFloat(latInput?.value);
                    const lng = parseFloat(lngInput?.value);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        // Jika marker sudah ada → pindahkan
                        if (manualMarker) {
                            manualMarker.setLatLng([lat, lng]);
                        } else {
                            manualMarker = L.marker([lat, lng], { draggable: true }).addTo(map);
                            manualMarker.on('dragend', function (e) {
                                const pos = manualMarker.getLatLng();
                                latInput.value = pos.lat.toFixed(6);
                                lngInput.value = pos.lng.toFixed(6);
                                updateManualMarker();
                            });
                        }
                        // Fokuskan peta ke marker
                        map.setView([lat, lng], 15);
                    } else {
                        // Hapus marker jika salah satu kosong
                        if (manualMarker) {
                            map.removeLayer(manualMarker);
                            manualMarker = null;
                        }
                    }
                }

                latInput?.addEventListener('input', updateManualMarker);
                lngInput?.addEventListener('input', updateManualMarker);

            } else {
                const map = L.map('proj-map').setView([-6.9175, 107.6191], 10);

                const googleMaps = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    attribution: '© Google Maps',
                    maxZoom: 20
                });

                const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    attribution: '© Google Satellite',
                    maxZoom: 20
                }).addTo(map);

                const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    attribution: 'Map data: © OSM contributors, SRTM | Map style: © OpenTopoMap',
                    maxZoom: 17
                });

                const baseMaps = {
                    "Google Maps": googleMaps,
                    "Google Satellite": googleSatellite,
                    "OpenTopoMap": topo
                };
                L.control.layers(baseMaps, null, { position: 'topright', collapsed: true }).addTo(map);
            }
        });
        </script>
    @endif
@endsection