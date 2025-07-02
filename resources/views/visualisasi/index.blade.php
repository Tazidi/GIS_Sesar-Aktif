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

@section('content')
<div class="container">
    <h1>Halaman Visualisasi Peta</h1>
    <a href="{{ route('home') }}">← Kembali ke Beranda</a>
    
    <div class="map-container">
        <div id="map"></div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- Import JavaScript Leaflet dari CDN -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @php
        $colors = ['blue', 'green', 'red', 'orange', 'purple', 'brown', 'magenta', 'darkcyan', 'gold', 'darkslategray'];
    @endphp 
       
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var map = L.map('map', {
                    preferCanvas: true,
                    zoomControl: true
                }).setView([-7.5, 107.5], 7);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 18
                }).addTo(map);

                setTimeout(() => map.invalidateSize(), 500);
                window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 100));

                // Reset View Button
                L.Control.ResetView = L.Control.extend({
                    onAdd: function(map) {
                        var btn = L.DomUtil.create('button', 'leaflet-bar leaflet-control leaflet-control-custom');
                        btn.innerHTML = '🔄 Reset View';
                        btn.style.backgroundColor = 'white';
                        btn.style.padding = '5px 10px';
                        btn.style.cursor = 'pointer';
                        btn.onclick = () => fitAllBounds();
                        return btn;
                    }
                });
                L.control.resetView = opts => new L.Control.ResetView(opts);
                L.control.resetView({ position: 'topright' }).addTo(map);

                const allBounds = [];
                let loaded = 0;
                const total = {{ count($maps) }};

                function fitAllBounds() {
                    if (allBounds.length > 0) {
                        const group = L.featureGroup();
                        allBounds.forEach(bounds => group.addLayer(L.rectangle(bounds)));
                        map.fitBounds(group.getBounds(), { padding: [30, 30], maxZoom: 10 });
                    } else {
                        map.fitBounds([[-8.8, 106.0], [-5.8, 109.0]], { padding: [30, 30] });
                    }
                }

                function loadGeoJSON(url, style, name) {
                    fetch(url)
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP ${res.status}`);
                            return res.json();
                        })
                        .then(data => {
                            const layer = L.geoJSON(data, {
                                style: style,
                                onEachFeature: function (feature, layer) {
                                    let props = feature.properties || {};
                                    let content = Object.entries(props).map(([k,v]) => `<b>${k}:</b> ${v}`).join('<br>');
                                    layer.bindPopup(content);
                                },
                                pointToLayer: function(feature, latlng) {
                                    return L.circleMarker(latlng, {
                                        radius: 6,
                                        fillColor: "red",
                                        color: "#000",
                                        weight: 1,
                                        opacity: 1,
                                        fillOpacity: 0.8
                                    });
                                }
                            });
                            layer.addTo(map);
                            if (layer.getBounds().isValid()) {
                                allBounds.push(layer.getBounds());
                            }
                        })
                        .catch(err => console.error(`Gagal memuat ${name}:`, err))
                        .finally(() => {
                            loaded++;
                            if (loaded === total) {
                                setTimeout(() => fitAllBounds(), 300);
                            }
                        });
                }

                // Load semua data dari database
                @foreach ($maps as $index => $map)
                    loadGeoJSON("{{ route('maps.geojson', $map->id) }}", {
                        color: '{{ $colors[$index % count($colors)] }}',
                        weight: 2
                    }, "{{ $map->title }}");
                @endforeach

            }, 300);
        });
    </script>
@endsection
