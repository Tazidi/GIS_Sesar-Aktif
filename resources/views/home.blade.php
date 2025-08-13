@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="px-4 sm:px-6 lg:px-8">

        {{-- BAGIAN TOP TAGS & LATEST STORY --}}
        <div class="border-b-2 border-gray-200 pb-4 mb-8">
            <h3 class="font-bold text-gray-500 mb-2"># Top Tags</h3>
            <div class="flex items-center space-x-4">
                <span class="bg-red-600 text-white text-sm font-bold py-1 px-3 flex items-center whitespace-nowrap">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                    <span>Latest Story</span>
                </span>
                <div class="ticker-wrap flex-grow overflow-hidden">
                    <div class="ticker-move">
                        <p class="text-sm text-gray-700">{{ $latestPosts->first()->title ?? 'Belum ada berita terbaru.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- STRUKTUR GRID TIGA KOLOM --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            {{-- Kolom Kiri: Latest Post --}}
            <div class="lg:col-span-3 flex flex-col">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Latest Post</h2>
                </div>
                <div class="flex flex-col space-y-6 flex-grow">
                    @forelse($latestPosts as $post)
                        <a href="{{ route('articles.show', $post) }}" class="flex-1 block group relative overflow-hidden shadow-md rounded-md">
                            <img src="{{ asset('thumbnails/' . basename($post->thumbnail)) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-0 p-4 text-white z-10">
                                <span class="bg-red-600 text-white text-xs font-bold py-1 px-2 rounded-md">Latest</span>
                                <h3 class="font-semibold text-lg mt-2">{{ $post->title }}</h3>
                            </div>
                        </a>
                    @empty
                        <div class="bg-white flex-1 rounded-md flex items-center justify-center text-gray-500 shadow-md">
                            <p>Tidak ada post hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            {{-- Kolom Tengah: Main Story --}}
            <div class="lg:col-span-6">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Main Story</h2>
                </div>
                @if($mainStories->isNotEmpty())
                    <div class="grid grid-cols-1 gap-6">
                        <a href="{{ route('articles.show', $mainStories->first()) }}" class="block group relative overflow-hidden shadow-md h-80 rounded-md">
                            <img src="{{ asset('thumbnails/' . basename($mainStories->first()->thumbnail)) }}" alt="{{ $mainStories->first()->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-0 p-6 text-white z-10">
                                <h3 class="font-bold text-3xl">{{ $mainStories->first()->title }}</h3>
                            </div>
                        </a>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($mainStories->skip(1) as $story)
                                <a href="{{ route('articles.show', $story) }}" class="block group relative overflow-hidden shadow-md h-40 rounded-md">
                                     <img src="{{ asset('thumbnails/' . basename($story->thumbnail)) }}" alt="{{ $story->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-60 transition-opacity duration-300"></div>
                                    <div class="absolute inset-0 p-4 flex flex-col justify-end transform translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-in-out">
                                        <h4 class="font-semibold text-white">{{ $story->title }}</h4>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white h-[500px] flex items-center justify-center text-gray-500 shadow-md rounded-md"><p>Tidak ada cerita utama.</p></div>
                @endif
            </div>
            {{-- Kolom Kanan: Today Update --}}
            <div class="lg:col-span-3">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Today Update</h2>
                </div>
                <div class="bg-white p-4 shadow-md rounded-md">
                    <div class="flex border-b mb-4">
                        <button class="flex-1 py-2 text-sm font-bold bg-red-600 text-white rounded-t-md"><i class="fas fa-fire mr-2"></i>Most viewed</button>
                        <button class="flex-1 py-2 text-sm text-gray-500 hover:text-gray-800"><i class="far fa-clock mr-2"></i>Recent</button>
                    </div>
                    <ul>
                        @forelse($popularArticles as $article)
                            <li class="border-b py-3 last:border-b-0">
                                <a href="{{ route('articles.show', $article) }}" class="font-semibold text-gray-800 hover:text-red-600">{{ $article->title }}</a>
                                <p class="text-xs text-gray-500 mt-1">{{ $article->created_at->format('d F Y') }}</p>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada artikel populer saat ini.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- BAGIAN PETA --}}
        <div class="mt-12">
            <div class="mb-4 border-b border-gray-300">
                <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Visualisasi Peta</h2>
            </div>
            <div class="bg-white p-4 shadow-md rounded-md">
                <div id="home-map" style="height: 450px; border-radius: 8px; position: relative;"></div>
            </div>
        </div>
        
        {{-- BAGIAN GALERI --}}
        <div class="mt-12">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-300">
                <div class="flex flex-wrap border-b-0 sm:border-b-0 -mb-px">
                    <button data-category="Sesar Aktif" class="gallery-tab active-tab text-sm font-bold py-2 px-4 border-b-4 border-red-600 text-red-600">Sesar Aktif</button>
                    <button data-category="Peta Geologi" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600">Peta Geologi</button>
                    <button data-category="Mitigasi Bencana" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600">Mitigasi Bencana</button>
                    <button data-category="Studi Lapangan" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600">Studi Lapangan</button>
                    <button data-category="Lainnya" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600">Lainnya</button>
                </div>
                <a href="{{ route('gallery.publik') }}" class="text-sm mt-2 sm:mt-0 font-semibold text-red-600 hover:underline">Lihat Semua Galeri &rarr;</a>
            </div>
            <div id="gallery-grid-home" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="h-48 col-span-full flex items-center justify-center text-gray-500">Memuat galeri...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

{{-- Pustaka Leaflet (selalu dimuat) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
window.addEventListener('load', function () {
    
    const tabs = document.querySelectorAll('.gallery-tab');
    const gridContainer = document.getElementById('gallery-grid-home');
    if (tabs.length > 0 && gridContainer) {
        const galleryPageUrl = "{{ route('gallery.publik') }}";
        const assetBaseUrl = "{{ asset('gallery/') }}";
        const apiBaseUrl = "{{ url('/gallery/category') }}";
        async function fetchHomepageGallery(category) {
            gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-gray-500">Memuat galeri...</div>`;
            try {
                const encodedCategory = encodeURIComponent(category);
                const response = await fetch(`${apiBaseUrl}/${encodedCategory}/home`);
                if (!response.ok) throw new Error('Network response was not ok.');
                const images = await response.json();
                gridContainer.innerHTML = '';
                if (images.length > 0) {
                    images.forEach(image => {
                        const linkUrl = `${galleryPageUrl}?category=${encodeURIComponent(image.category)}`;
                        const imageUrl = `${assetBaseUrl}/${image.image_path}`;
                        const galleryItem = `
                            <a href="${linkUrl}" class="relative block aspect-square group overflow-hidden rounded-md shadow-md">
                                <img src="${imageUrl}" alt="${image.title}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex flex-col justify-end p-3">
                                    <h3 class="font-bold text-white text-sm">${image.title}</h3>
                                </div>
                            </a>
                        `;
                        gridContainer.insertAdjacentHTML('beforeend', galleryItem);
                    });
                } else {
                    gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-gray-400">Tidak ada gambar dalam kategori ini.</div>`;
                }
            } catch (error) {
                console.error('Gagal memuat galeri:', error);
                gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-red-500">Gagal memuat galeri.</div>`;
            }
        }
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => {
                    t.classList.remove('active-tab', 'border-red-600', 'text-red-600');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.add('active-tab', 'border-red-600', 'text-red-600');
                this.classList.remove('border-transparent', 'text-gray-500');
                const category = this.dataset.category;
                fetchHomepageGallery(category);
            });
        });
        const initialActiveTab = document.querySelector('.gallery-tab.active-tab');
        if (initialActiveTab) {
            fetchHomepageGallery(initialActiveTab.dataset.category);
        }
    }
    
    const mapContainer = document.getElementById('home-map');
    
    if (mapContainer) {
        const initialView = { coords: [-2.5489, 118.0149], zoom: 5 };
        const googleSat = L.tileLayer('http://{s}.google.com/vt?lyrs=s,h&x={x}&y={y}&z={z}',{ maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3'], attribution: 'Â© Google Satellite' });
        const baseMaps = { "Google Satellite": googleSat };
        const map = L.map('home-map', { layers: [googleSat], scrollWheelZoom: false }).setView(initialView.coords, initialView.zoom);
        let overlays = {};
        const layerControl = L.control.layers(baseMaps, overlays, { position: 'bottomright' }).addTo(map);

        @if(isset($mapForHome) && $mapForHome)
            const geoJsonUrl = "{{ route('maps.geojson', $mapForHome->id) }}";
            fetch(geoJsonUrl)
                .then(response => response.json())
                .then(data => {
                    const dataLayer = L.geoJSON(data, {
                        onEachFeature: function (feature, layer) {
                            if (feature.properties && feature.properties.name) {
                                layer.bindPopup(feature.properties.name);
                            }
                        }
                    });
                    dataLayer.addTo(map);
                    layerControl.addOverlay(dataLayer, '{{ $mapForHome->name }}');
                })
                .catch(error => console.error('Gagal memuat data GeoJSON:', error));
        @endif

        L.Control.resetView = L.Control.extend({
            onAdd: function(map) {
                var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                container.innerHTML = '<a href="#" title="Reset View" role="button" style="width: 34px; height: 34px; line-height: 34px; text-align: center; font-size: 1.2rem;"><i class="fas fa-sync-alt"></i></a>';
                container.onclick = function(e){ e.preventDefault(); map.setView(initialView.coords, initialView.zoom); }
                return container;
            },
        });
        new L.Control.resetView({ position: 'bottomright' }).addTo(map);

        L.Control.Legend = L.Control.extend({
            onAdd: function(map) {
                var div = L.DomUtil.create('div', 'info legend');
                div.innerHTML = '<h4>Keterangan Peta</h4>';
                @if(isset($mapForHome) && $mapForHome)
                    div.innerHTML += '<i style="background: #0000FF; border-radius: 50%;"></i> {{ $mapForHome->name }}<br>';
                @endif
                return div;
            },
        });
        new L.Control.Legend({ position: 'bottomleft' }).addTo(map);
        
        const actionButtonContainer = document.createElement('div');
        actionButtonContainer.classList.add('manual-action-buttons');

        actionButtonContainer.innerHTML = `
            <a href="{{ route('visualisasi.index') }}" class="action-button primary">
                <i class="fas fa-map-marked-alt mr-2"></i> Lihat Peta Lengkap
            </a>
            <a href="{{ route('gallery_maps.peta') }}" class="action-button secondary">
                <i class="fas fa-images mr-2"></i> Lihat Galeri Peta
            </a>
        `;

        mapContainer.appendChild(actionButtonContainer);
        L.DomEvent.disableClickPropagation(actionButtonContainer);

        // CSS dengan layout yang diperbaiki
        const customStyles = document.createElement('style');
        customStyles.innerHTML = `
            .info.legend { 
                padding: 6px 8px; 
                font: 14px/16px Arial, Helvetica, sans-serif; 
                background: white; 
                background: rgba(255,255,255,0.8); 
                box-shadow: 0 0 15px rgba(0,0,0,0.2); 
                border-radius: 5px; 
                margin-bottom: 10px;
            }
            .info.legend h4 { margin: 0 0 5px; color: #777; }
            .info.legend i { width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.9; }

            /* CSS untuk tombol aksi di top right */
            .manual-action-buttons {
                position: absolute;
                top: 10px;
                right: 10px;
                z-index: 1000;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .action-button {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 8px 12px;
                font-size: 0.9rem;
                font-weight: bold;
                text-decoration: none;
                transition: all 0.2s ease-in-out;
                white-space: nowrap;
                border-radius: 5px;
                box-shadow: 0 1px 5px rgba(0,0,0,0.4);
            }
            .action-button.primary {
                background-color: #DC2626;
                color: white;
            }
            .action-button.primary:hover {
                background-color: #B91C1C;
            }
            .action-button.secondary {
                background-color: #ffffff;
                color: #374151;
            }
            .action-button.secondary:hover {
                 background-color: #f3f4f6;
            }

            /* Styling untuk kontrol di bottomright agar tidak bertabrakan */
            .leaflet-bottom.leaflet-right {
                margin-bottom: 15px;
                margin-right: 10px;
            }
            
            .leaflet-control-layers {
                margin-bottom: 10px;
            }
            
            .leaflet-bar .leaflet-control {
                margin-bottom: 5px;
            }
        `;
        document.head.appendChild(customStyles);
    }
});
</script>

@endpush