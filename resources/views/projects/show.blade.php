@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 450px; border-radius: 0.5rem; z-index: 10; }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Tombol Kembali ke Daftar Proyek --}}
    <div class="mb-5">
        <a href="{{ route('projects.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
            Kembali ke Daftar Proyek
        </a>
    </div>

    {{-- Header Proyek --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $project->name }}</h1>
            <p class="mt-1 text-gray-600">{{ $project->description }}</p>
        </div>
        @can('create', [App\Models\SurveyLocation::class, $project])
            <a href="{{ route('projects.survey-locations.create', $project) }}" 
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-xs uppercase">
                + Tambah Lokasi
            </a>
        @endcan
    </div>

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
            <p class="font-bold">Sukses</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- Peta Leaflet --}}
    <div class="bg-white shadow-lg rounded-lg p-4 mb-8">
        <div id="map"></div>
    </div>

    {{-- Daftar Lokasi dalam Proyek --}}
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Lokasi</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($project->surveyLocations as $location)
            <div class="bg-white shadow-md rounded-lg overflow-hidden flex flex-col">
                @if($location->primary_image)
                    <img src="{{ asset('survey/' . $location->primary_image) }}" alt="{{ $location->nama }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500">Tidak ada gambar</span>
                    </div>
                @endif
                <div class="p-4 flex flex-col flex-grow">
                    <h3 class="font-bold text-lg">{{ $location->nama }}</h3>
                    <p class="text-sm text-gray-600 mt-1 flex-grow">{{ $location->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                    <p class="text-xs text-gray-500 mt-2">Lat: {{ $location->geometry['lat'] ?? 'N/A' }}, Lng: {{ $location->geometry['lng'] ?? 'N/A' }}</p>
                    <div class="mt-4 flex justify-end space-x-3 border-t pt-3">
                         {{-- Edit/Hapus lokasi: hanya pemilik lokasi atau admin --}}
                        @if(Auth::user()->id === $location->user_id || Auth::user()->role === 'admin')
                            <a href="{{ route('survey-locations.edit', $location) }}" class="font-medium text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                            <form action="{{ route('survey-locations.destroy', $location) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus lokasi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-900 text-sm">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white p-8 rounded-lg shadow-md text-center">
                <p class="text-gray-500">Belum ada lokasi yang ditambahkan ke proyek ini.</p>
                <a href="{{ route('projects.survey-locations.create', $project) }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-xs uppercase">
                    + Tambah Lokasi Pertama
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection

{{-- ✅ FIX: Memindahkan logika PHP ke luar dari tag <script> --}}
@php
    $locationsForMap = $project->surveyLocations->map(function($loc) {
        return [
            'lat' => $loc->geometry['lat'] ?? 0,
            'lng' => $loc->geometry['lng'] ?? 0,
            'nama' => $loc->nama,
            'image' => $loc->primary_image ? asset('survey/' . $loc->primary_image) : null
        ];
    });
@endphp

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Menggunakan variabel yang sudah disiapkan di atas
        const locations = @json($locationsForMap);

        if (locations.length > 0) {
            const map = L.map('map').setView([locations[0].lat, locations[0].lng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            locations.forEach(loc => {
                let popupContent = `<b class="font-semibold">${loc.nama}</b>`;
                if (loc.image) {
                    popupContent += `<br><img src="${loc.image}" alt="${loc.nama}" style="width:120px; margin-top:8px; border-radius: 4px;">`;
                }
                L.marker([loc.lat, loc.lng])
                    .addTo(map)
                    .bindPopup(popupContent);
            });

            if (locations.length > 1) {
                const bounds = L.latLngBounds(locations.map(loc => [loc.lat, loc.lng]));
                map.fitBounds(bounds.pad(0.1));
            }
        } else {
            const map = L.map('map').setView([-6.9175, 107.6191], 10); // Default Bandung
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
            const mapContainer = document.getElementById('map');
            const loader = document.createElement('div');
            loader.className = 'absolute inset-0 bg-gray-500 bg-opacity-30 flex items-center justify-center z-20 rounded-md';
            loader.innerHTML = '<p class="text-white font-semibold">Belum ada lokasi untuk ditampilkan.</p>';
            mapContainer.appendChild(loader);
        }
    });
</script>
@endpush
