@extends('layouts.app')

@section('styles')
{{-- Leaflet CSS diperlukan untuk peta --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>
<style>
    /* Style untuk peta */
    #map { 
        height: 450px; /* Disesuaikan agar lebih tinggi */
        border-radius: 0.5rem; 
        z-index: 10;
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header Halaman --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Tambah Lokasi Survey Baru
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            Menambahkan lokasi untuk proyek: <span class="font-semibold">{{ $project->name }}</span>
        </p>
    </div>

    {{-- Form Container --}}
    <form method="POST" action="{{ route('projects.survey-locations.store', $project) }}" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            {{-- Kolom Kiri: Input Data --}}
            <div class="flex flex-col space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Tempat <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('nama') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi') }}</textarea>
                </div>
                
                {{-- Blok untuk upload 3 gambar --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Lokasi</label>
                    
                    {{-- Gambar Utama --}}
                    <div class="mb-4">
                        <label for="image_primary" class="block text-xs font-medium text-gray-600">Gambar Utama (Wajib) <span class="text-red-500">*</span></label>
                        <input type="file" id="image_primary" name="image_primary" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        @error('image_primary') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Gambar Tambahan 1 --}}
                    <div class="mb-4">
                        <label for="image_2" class="block text-xs font-medium text-gray-600">Gambar Tambahan 1</label>
                        <input type="file" id="image_2" name="image_2" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        @error('image_2') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Gambar Tambahan 2 --}}
                    <div>
                        <label for="image_3" class="block text-xs font-medium text-gray-600">Gambar Tambahan 2</label>
                        <input type="file" id="image_3" name="image_3" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        @error('image_3') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Peta --}}
            <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi di Peta <span class="text-red-500">*</span></label>
                <div id="map" class="w-full relative">
                    <div id="map-loader" class="absolute inset-0 bg-gray-500 bg-opacity-30 flex items-center justify-center z-20 rounded-md">
                        <p class="text-white font-semibold">Mencari lokasi Anda...</p>
                    </div>
                </div>
                {{-- Input Koordinat (Readonly) --}}
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="lat" class="block text-xs font-medium text-gray-500">Latitude</label>
                        <input type="text" id="lat" name="lat" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" value="{{ old('lat') }}" required readonly>
                        @error('lat') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="lng" class="block text-xs font-medium text-gray-500">Longitude</label>
                        <input type="text" id="lng" name="lng" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" value="{{ old('lng') }}" required readonly>
                        @error('lng') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="px-6 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700 transition">Simpan Lokasi</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- Leaflet JS diperlukan untuk peta --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        const mapLoader = document.getElementById('map-loader');
        const defaultLocation = [-6.9175, 107.6191]; // Default: Bandung
        const map = L.map('map').setView(defaultLocation, 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker = L.marker(defaultLocation, { draggable: true }).addTo(map);

        function updateMarkerPosition(lat, lng, pan = true) {
            marker.setLatLng([lat, lng]);
            if (pan) {
                map.panTo([lat, lng]);
            }
            latInput.value = lat.toFixed(6);
            lngInput.value = lng.toFixed(6);
            mapLoader.style.display = 'none'; // Sembunyikan loader setelah lokasi ditemukan
        }

        marker.on('dragend', function (event) {
            const position = marker.getLatLng();
            updateMarkerPosition(position.lat, position.lng, false);
        });

        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    updateMarkerPosition(position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    console.warn(`Geolocation error (${error.code}): ${error.message}`);
                    updateMarkerPosition(defaultLocation[0], defaultLocation[1]); 
                }
            );
        } else {
            console.log('Geolocation tidak tersedia.');
            updateMarkerPosition(defaultLocation[0], defaultLocation[1]);
        }
    });
</script>
@endpush
