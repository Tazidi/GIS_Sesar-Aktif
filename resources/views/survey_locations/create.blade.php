@extends('layouts.app')

@section('styles')
{{-- Leaflet CSS diperlukan untuk peta --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>
<style>
    /* Style untuk peta dan area upload gambar */
    #map { 
        height: 350px; 
        border-radius: 0.5rem; 
        z-index: 10;
    }
    .image-upload-container {
        border: 2px dashed #d1d5db; /* gray-300 */
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        background-color: #f9fafb; /* gray-50 */
        transition: background-color 0.2s ease;
        position: relative; /* Menambahkan posisi relatif untuk pratinjau */
    }
    .image-upload-container:hover {
        background-color: #f3f4f6; /* gray-100 */
    }
    #image-preview {
        max-height: 200px;
        max-width: 100%;
        margin: 1rem auto 0;
        border-radius: 0.5rem;
        display: none; /* Sembunyikan secara default */
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
            Isi detail di bawah. Lokasi Anda akan terdeteksi otomatis, atau Anda dapat menggeser penanda pada peta.
        </p>
    </div>

    {{-- Form Container --}}
    <form method="POST" action="{{ route('survey-locations.store') }}" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            {{-- Kolom Kiri: Input Data --}}
            <div class="flex flex-col space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Tempat <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                 <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Gambar Lokasi</label>
                    <div id="image-upload-box" class="image-upload-container">
                        {{-- Wrapper untuk konten petunjuk, agar mudah disembunyikan --}}
                        <div id="upload-instructions">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">
                                <span class="font-semibold text-indigo-600">Klik untuk mengunggah</span> atau seret dan letakkan
                            </p>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF hingga 10MB</p>
                        </div>
                        <input class="sr-only" type="file" id="image" name="image" onchange="previewImage(event);" accept="image/*">
                        <img id="image-preview" src="#" alt="Pratinjau Gambar"/>
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
                        <input type="text" id="lat" name="lat" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" required readonly>
                    </div>
                    <div>
                        <label for="lng" class="block text-xs font-medium text-gray-500">Longitude</label>
                        <input type="text" id="lng" name="lng" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" required readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('survey-locations.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">Batal</a>
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

        const uploadBox = document.getElementById('image-upload-box');
        const imageInput = document.getElementById('image');
        uploadBox.addEventListener('click', () => imageInput.click());
    });

    function previewImage(event) {
        const input = event.target;
        const imgPreview = document.getElementById('image-preview');
        const uploadInstructions = document.getElementById('upload-instructions');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
                uploadInstructions.style.display = 'none'; // <-- PERBAIKAN: Sembunyikan petunjuk
            };
            
            reader.readAsDataURL(input.files[0]);
        } else {
            // Jika tidak ada file dipilih (misal, user klik cancel)
            imgPreview.style.display = 'none';
            imgPreview.src = '#';
            uploadInstructions.style.display = 'block'; // <-- PERBAIKAN: Tampilkan kembali petunjuk
        }
    }
</script>
@endpush