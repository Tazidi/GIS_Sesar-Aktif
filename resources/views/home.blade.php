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
                <div class="ticker-wrap flex-grow">
                    <div class="ticker-move">
                        <p class="text-sm text-gray-700">{{ $latestPosts->first()->title ?? 'Belum ada berita terbaru.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- STRUKTUR GRID TIGA KOLOM --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

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
                            <p class="text-sm text-gray-500">Tidak ada artikel yang paling banyak dilihat.</p>
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
                <div id="home-map" style="height: 450px; border-radius: 8px;"></div>
            </div>
        </div>

    {{-- BAGIAN PETA SISIRAJA --}}
        <div class="mt-12">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-300">
                <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Peta SISIRAJA</h2>
                <div class="flex gap-4">
                    <a href="{{ route('gallery_maps.index') }}" class="text-sm font-semibold text-red-600 hover:underline">
                        Lihat Semua Peta &rarr;
                    </a>
                </div>
            </div>

            <div class="rounded-md overflow-hidden shadow ring-1 ring-black/5">
                <iframe
                    src="{{ route('visualisasi.index', ['embed' => 1]) }}"
                    title="Peta SISIRAJA"
                    class="w-full"
                    style="height: 560px; border: 0;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
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

{{-- ▼▼▼ PINDAHKAN SEMUA SKRIP KE DALAM BLOK INI ▼▼▼ --}}
@push('scripts')

{{-- 1. Pustaka Leaflet (selalu dimuat) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // ===================================
    // LOGIKA UNTUK GALERI (TIDAK DIUBAH)
    // ===================================
    // ... (biarkan semua kode galeri Anda di sini)
    const tabs = document.querySelectorAll('.gallery-tab');
    const gridContainer = document.getElementById('gallery-grid-home');
    const galleryPageUrl = "{{ route('gallery.publik') }}";
    const assetBaseUrl = "{{ asset('gallery/') }}";
    const apiBaseUrl = "{{ url('/gallery/category') }}";

    async function fetchHomepageGallery(category) { /* ... isi fungsi galeri ... */ }
    tabs.forEach(tab => { /* ... event listener galeri ... */ });
    const initialActiveTab = document.querySelector('.gallery-tab.active-tab');
    if (initialActiveTab) { fetchHomepageGallery(initialActiveTab.dataset.category); }
    
    // ===================================================
    // BAGIAN LOGIKA PETA YANG DIPERBARUI
    // ===================================================
    const mapContainer = document.getElementById('home-map');
    
    if (mapContainer) {
        // Definisikan koordinat dan zoom awal untuk reset
        const initialView = {
            coords: [-2.5489, 118.0149],
            zoom: 5
        };

        // Langkah A: Definisikan semua pilihan Base Map
        const googleStreet = L.tileLayer('http://{s}.google.com/vt?lyrs=m&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3'],
            attribution: '© Google Maps'
        });

        const googleSat = L.tileLayer('http://{s}.google.com/vt?lyrs=s,h&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3'],
            attribution: '© Google Satellite'
        });
        
        const openTopoMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: 'Map data: © OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)'
        });

        const baseMaps = {
            "Google Maps": googleStreet,
            "Google Satellite": googleSat,
            "OpenTopoMap": openTopoMap
        };

        // Langkah B: Inisialisasi Peta
        // Kita set 'googleSat' sebagai tampilan default
        const map = L.map('home-map', {
            layers: [googleSat], // Layer default yang aktif
            scrollWheelZoom: false
        }).setView(initialView.coords, initialView.zoom);

        // Langkah C: Siapkan grup untuk layer overlay (data GeoJSON)
        let overlays = {}; // Objek kosong untuk menampung layer data
        
        // Cek jika ada data dari controller
        @if($mapForHome)
            const geoJsonUrl = "{{ route('maps.geojson', $mapForHome->id) }}";

            fetch(geoJsonUrl)
                .then(response => response.json())
                .then(data => {
                    // Buat layer GeoJSON
                    const dataLayer = L.geoJSON(data, {
                        onEachFeature: function (feature, layer) {
                            if (feature.properties && feature.properties.name) {
                                layer.bindPopup(feature.properties.name);
                            }
                        }
                    });
                    
                    // Masukkan layer ke dalam objek overlays
                    // Nama '{{ $mapForHome->name }}' akan muncul di kotak pilihan layer
                    overlays['{{ $mapForHome->name }}'] = dataLayer;
                    
                    // Tambahkan layer ini ke peta secara default
                    dataLayer.addTo(map);

                    // PENTING: Buat dan tambahkan Layer Control SETELAH fetch selesai
                    L.control.layers(baseMaps, overlays).addTo(map);
                })
                .catch(error => {
                    console.error('Gagal memuat data GeoJSON:', error);
                    // Jika fetch gagal, tetap tampilkan kontrol layer hanya dengan base map
                    L.control.layers(baseMaps).addTo(map);
                });
        @else
            // Jika tidak ada data peta sama sekali, tampilkan kontrol layer hanya dengan base map
            L.control.layers(baseMaps).addTo(map);
        @endif

        // Langkah D: Tambahkan Tombol Reset View
        L.Control.resetView = L.Control.extend({
            onAdd: function(map) {
                var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                container.innerHTML = '<button title="Reset View" style="width: 30px; height: 30px; font-size: 1.2rem; line-height: 30px;"><i class="fas fa-sync-alt"></i></button>';
                container.onclick = function(){
                    map.setView(initialView.coords, initialView.zoom);
                }
                return container;
            },
            onRemove: function(map) {}
        });
        new L.Control.resetView({ position: 'topright' }).addTo(map);


        // Langkah E: Tambahkan Legenda
        L.Control.Legend = L.Control.extend({
            onAdd: function(map) {
                var div = L.DomUtil.create('div', 'info legend');
                div.innerHTML = '<h4>Keterangan Peta</h4>';
                // Anda bisa tambahkan loop di sini untuk membuat legenda dinamis
                // Contoh statis:
                div.innerHTML += '<i style="background: #0000FF"></i> Sekolah<br>';
                return div;
            },
        });
        new L.Control.Legend({ position: 'bottomleft' }).addTo(map);

        // Anda mungkin perlu menambahkan sedikit CSS untuk legenda
        const legendStyle = document.createElement('style');
        legendStyle.innerHTML = `
            .info.legend { padding: 6px 8px; font: 14px/16px Arial, Helvetica, sans-serif; background: white; background: rgba(255,255,255,0.8); box-shadow: 0 0 15px rgba(0,0,0,0.2); border-radius: 5px; }
            .info.legend h4 { margin: 0 0 5px; color: #777; }
            .info.legend i { width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.7; border-radius: 50%; }
        `;
        document.head.appendChild(legendStyle);
    }
});
</script>

@endpush