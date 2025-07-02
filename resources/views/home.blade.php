{{-- Extend dari layout utama aplikasi --}}
@extends('layouts.app')

{{-- Section untuk menambahkan CSS khusus halaman ini --}}
@section('styles')
    <!-- Import CSS Leaflet dari CDN - library untuk membuat peta interaktif -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        /* CSS untuk mengatur tampilan halaman */
        .container {
            max-width: 1200px;
            /* Batas maksimal lebar container */
            margin: 0 auto;
            /* Center container di halaman */
            padding: 20px;
            /* Kasih jarak dalam container */
        }

        /* Style untuk div peta - ini yang paling penting! */
        #map {
            height: 70vh !important;
            /* Tinggi 70% dari viewport height, !important biar ga ditimpa CSS lain */
            min-height: 400px !important;
            /* Tinggi minimum 400px biar ga terlalu kecil */
            width: 100% !important;
            /* Lebar 100% dari parent container */
            margin: 0 auto;
            /* Center peta */
            border: 1px solid #ddd;
            /* Border tipis abu-abu */
            border-radius: 8px;
            /* Sudut melengkung */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            /* Bayangan halus biar keliatan bagus */
        }

        .map-container {
            width: 100%;
            margin: 20px 0;
            /* Jarak atas bawah 20px */
            padding: 0;
            height: auto;
            /* Biarkan tinggi menyesuaikan konten */
        }

        /* Pastikan leaflet container punya dimensi yang benar - ini fix bug umum leaflet */
        .leaflet-container {
            width: 100% !important;
            height: 100% !important;
        }

        /* Media query untuk tampilan mobile - responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
                /* Kurangi padding di mobile */
            }

            #map {
                height: 60vh !important;
                /* Tinggi peta lebih kecil di mobile */
                min-height: 300px !important;
                /* Minimum height juga dikurangi */
            }
        }

        /* Fix masalah scroll horizontal yang sering muncul */
        body,
        html {
            overflow-x: hidden;
            /* Sembunyikan scroll horizontal */
        }

        .container {
            overflow-x: hidden;
            /* Pastikan container juga ga scroll horizontal */
        }
    </style>
@endsection

{{-- Section untuk konten utama halaman --}}
@section('content')
    <div class="container">
        {{-- Bagian untuk daftar artikel --}}
        <h1>Daftar Artikel</h1>

        {{-- Form pencarian artikel - method GET biar parameter keliatan di URL --}}
        <form method="GET">
            <input type="text" name="search" placeholder="Cari artikel..." value="{{ request('search') }}" />
        </form>

        {{-- Loop untuk menampilkan semua artikel dari controller --}}
        @foreach ($articles as $article)
            <a href="{{ route('article.show', $article->id) }}">
                <h2>{{ $article->title }}</h2>
            </a>
        @endforeach

        <hr> {{-- Garis pemisah --}}

        {{-- Bagian untuk visualisasi peta statis --}}
        <h1>Visualisasi Peta</h1>
        @foreach ($maps as $map)
            <p>{{ $map->title }}</p>
            {{-- Menampilkan peta dalam iframe, file disimpan di storage --}}
            <iframe src="{{ asset('storage/' . $map->file_path) }}" width="100%" height="300"></iframe>
        @endforeach

        {{-- Bagian peta interaktif dengan Leaflet --}}
        <h3>Peta Sesar dan Lokasi Stasiun</h3>
        <div class="map-container">
            {{-- Div ini akan dijadikan peta oleh JavaScript --}}
            <div id="map"></div>
        </div>
    </div>
@endsection

{{-- Section untuk JavaScript - diletakkan di bagian bawah biar DOM sudah ready --}}
@section('scripts')
    <!-- Import JavaScript Leaflet dari CDN -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Event listener tunggu sampai DOM selesai dimuat - ini penting banget!
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM sudah dimuat, memulai inisialisasi peta...');

            // Delay sebentar biar container udah ter-render dengan baik
            // Ini trick yang sering dipake buat fix masalah peta ga muncul
            setTimeout(function() {
                // Ambil container peta dari DOM
                var mapContainer = document.getElementById('map');

                // Debug: cek dimensi container - berguna buat troubleshooting
                console.log('Dimensi container peta:', mapContainer.offsetWidth, 'x', mapContainer
                    .offsetHeight);

                // Inisialisasi peta Leaflet
                // preferCanvas: true -> pake canvas renderer buat performa lebih baik
                // zoomControl: true -> tampilkan tombol zoom +/-
                // setView -> set posisi awal peta (lat, lng, zoom level)
                var map = L.map('map', {
                    preferCanvas: true,
                    zoomControl: true
                }).setView([-7.5, 107.5], 7); // Koordinat Jawa Barat, zoom level 7

                // Tambahkan base layer (tile layer) - ini background peta
                // Pake OpenStreetMap karena gratis dan bagus
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 18 // Batas maksimal zoom
                }).addTo(map);

                console.log('Peta dasar sudah dimuat');

                // Fix ukuran peta - ini sering jadi masalah di Leaflet
                // invalidateSize() memberitahu Leaflet untuk recalculate ukuran container
                setTimeout(function() {
                    map.invalidateSize();
                    console.log('Ukuran peta sudah diperbarui');
                }, 500);

                // Event listener untuk window resize - penting buat responsive
                window.addEventListener('resize', function() {
                    setTimeout(function() {
                        map.invalidateSize(); // Update ukuran peta saat window diresize
                    }, 100);
                });

                // Bikin custom control untuk reset view peta
                L.Control.ResetView = L.Control.extend({
                    onAdd: function(map) {
                        // Bikin tombol HTML
                        var btn = L.DomUtil.create('button',
                            'leaflet-bar leaflet-control leaflet-control-custom');
                        btn.innerHTML = '🔄 Reset View';
                        btn.style.backgroundColor = 'white';
                        btn.style.padding = '5px 10px';
                        btn.style.cursor = 'pointer';

                        // Event handler saat tombol diklik
                        btn.onclick = function() {
                            fitAllBounds(); // Panggil fungsi untuk reset view
                        };
                        return btn;
                    },
                    onRemove: function(map) {
                        // Ga ada yang perlu dilakukan saat control dihapus
                    }
                });

                // Daftarkan control baru
                L.control.resetView = function(opts) {
                    return new L.Control.ResetView(opts);
                }

                // Tambahkan control ke peta di pojok kanan atas
                L.control.resetView({
                    position: 'topright'
                }).addTo(map);

                // Style untuk berbagai layer - biar beda warna dan mudah dibedakan
                var styleSesar1 = {
                    color: 'blue', // Warna biru untuk sesar tipe 1
                    weight: 2 // Ketebalan garis
                };

                var styleSesar2 = {
                    color: 'green', // Warna hijau untuk sesar tipe 2
                    weight: 2
                };

                // Layer untuk sesar 1 (Java Fault Model)
                var layerSesar1 = L.geoJSON(null, {
                    style: styleSesar1, // Pake style yang udah didefinisikan
                    onEachFeature: function(feature, layer) {
                        // Fungsi ini dipanggil untuk setiap feature di GeoJSON
                        var props = feature.properties; // Ambil properties dari GeoJSON

                        // Bikin konten popup dengan data dari properties
                        var popupContent = "<b>Nama:</b> " + (props.Name || '-') +
                            "<br><b>Segment:</b> " + (props.Segment || '-') +
                            "<br><b>Type:</b> " + (props.Type || '-') +
                            "<br><b>Mmax:</b> " + (props.Mmax_d || '-');

                        layer.bindPopup(popupContent); // Bind popup ke layer
                    }
                });

                // Layer untuk sesar 2 (Sesar Jawa Barat)
                var layerSesar2 = L.geoJSON(null, {
                    style: styleSesar2,
                    onEachFeature: function(feature, layer) {
                        var props = feature.properties;

                        // Popup dengan struktur data yang berbeda
                        var popupContent = "<b>Name:</b> " + (props.Name || '-') +
                            "<br><b>Popup Info:</b> " + (props.PopupInfo || '-') +
                            "<br><b>Shape Length:</b> " + (props.Shape_Leng || '-');

                        layer.bindPopup(popupContent);
                    }
                });

                // Layer untuk stasiun OSL - ini pake point marker
                var layerOSL = L.geoJSON(null, {
                    // pointToLayer: fungsi untuk convert point GeoJSON jadi marker
                    pointToLayer: function(feature, latlng) {
                        // Pake circleMarker biar konsisten dan bisa dikustomisasi
                        return L.circleMarker(latlng, {
                            radius: 6, // Ukuran marker
                            fillColor: "red", // Warna isi merah
                            color: "#000", // Warna border hitam
                            weight: 1, // Ketebalan border
                            opacity: 1, // Opacity border
                            fillOpacity: 0.8 // Opacity isi
                        });
                    },
                    onEachFeature: function(feature, layer) {
                        var props = feature.properties;
                        var popupContent = "<b>Stasiun:</b> " + (props.Name || '-');
                        layer.bindPopup(popupContent);
                    }
                });

                // Variable untuk tracking bounds dan loading status
                var allBounds = []; // Array untuk simpan bounds dari semua layer
                var loadedLayers = 0; // Counter layer yang udah dimuat
                var totalLayers = 3; // Total layer yang mau dimuat

                // Fungsi untuk load GeoJSON dengan error handling
                function loadGeoJSON(url, layer, layerName) {
                    console.log('Memuat ' + layerName + ' dari: ' + url);

                    // Pake fetch API untuk ambil data GeoJSON
                    fetch(url)
                        .then(response => {
                            // Cek apakah response OK
                            if (!response.ok) {
                                throw new Error('HTTP error! status: ' + response.status);
                            }
                            return response.json(); // Parse JSON
                        })
                        .then(data => {
                            // Berhasil load data
                            console.log('Data ' + layerName + ' berhasil dimuat:', data);

                            layer.addData(data); // Tambahkan data ke layer
                            layer.addTo(map); // Tambahkan layer ke peta

                            // Simpan bounds dari layer ini
                            if (layer.getBounds().isValid()) {
                                allBounds.push(layer.getBounds());
                            }

                            loadedLayers++; // Increment counter

                            // Kalau semua layer udah dimuat, fit bounds
                            if (loadedLayers === totalLayers) {
                                setTimeout(function() {
                                    fitAllBounds();
                                }, 300);
                            }
                        })
                        .catch(error => {
                            // Handle error
                            console.error('Error saat memuat ' + layerName + ':', error);
                            console.log('Pastikan file ada di: ' + url);

                            loadedLayers++; // Tetap increment meskipun error

                            // Tetap cek apakah semua layer sudah dicoba dimuat
                            if (loadedLayers === totalLayers) {
                                setTimeout(function() {
                                    fitAllBounds();
                                }, 300);
                            }
                        });
                }

                // Fungsi untuk menyesuaikan view peta dengan semua data
                function fitAllBounds() {
                    // Update ukuran peta dulu
                    map.invalidateSize();

                    if (allBounds.length > 0) {
                        // Kalau ada bounds dari layer yang dimuat
                        var group = new L.featureGroup(); // Bikin feature group

                        // Tambahkan semua bounds ke group
                        allBounds.forEach(function(bounds) {
                            var rect = L.rectangle(bounds); // Bikin rectangle dari bounds
                            group.addLayer(rect);
                        });

                        // Fit peta ke bounds gabungan
                        setTimeout(function() {
                            map.fitBounds(group.getBounds(), {
                                padding: [30, 30], // Padding 30px dari semua sisi
                                maxZoom: 10 // Batasi zoom maksimum biar ga terlalu deket
                            });
                            console.log('Peta telah disesuaikan dengan semua bounds');
                        }, 100);

                    } else {
                        // Fallback kalau ga ada data yang berhasil dimuat
                        setTimeout(function() {
                            map.fitBounds([
                                [-8.8, 106.0], // Sudut barat daya Jawa Barat
                                [-5.8, 109.0] // Sudut timur laut Jawa Barat
                            ], {
                                padding: [30, 30]
                            });
                            console.log('Menggunakan bounds fallback untuk Jawa Barat');
                        }, 100);
                    }
                }

                // Load semua file GeoJSON - pastikan path file sesuai dengan struktur folder project
                loadGeoJSON('/geojson/java_faultmodel.geojson', layerSesar1, 'Java Fault Model');
                loadGeoJSON('/geojson/sesar_jawabbarat.geojson', layerSesar2, 'Sesar Jawa Barat');
                loadGeoJSON('/geojson/osl_brin.geojson', layerOSL, 'OSL BRIN Stations');

                console.log('Inisialisasi peta selesai');
            }, 300); // Delay 300ms biar DOM benar-benar siap
        });
    </script>
@endsection
