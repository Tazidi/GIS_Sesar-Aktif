@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>
<style>
    #map { height: 350px; border-radius: 0.5rem; z-index: 10; }
    #image-preview-container {
        width: 100%;
        min-height: 200px;
        border: 2px dashed #d1d5db; /* gray-300 */
        border-radius: 0.5rem;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #f9fafb; /* gray-50 */
    }
    #image-preview {
        max-height: 180px;
        max-width: 100%;
        border-radius: 0.5rem;
        object-fit: cover;
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header Halaman --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Edit Lokasi Survey
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            Perbarui detail lokasi di bawah ini. Anda dapat menggeser penanda pada peta untuk menyesuaikan koordinat.
        </p>
    </div>

    {{-- Form Container --}}
    <form method="POST" action="{{ route('survey-locations.update', $surveyLocation) }}" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            {{-- Kolom Kiri: Input Data & Gambar --}}
            <div class="flex flex-col space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Tempat <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('nama', $surveyLocation->nama) }}" required>
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi', $surveyLocation->deskripsi) }}</textarea>
                </div>

                {{-- ✅ PERBAIKAN: Bagian Gambar dipindahkan ke sini, di bawah Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Lokasi</label>
                    <div id="image-preview-container">
                        <img id="image-preview" src="{{ $surveyLocation->image ? asset('survey/' . $surveyLocation->image) : '#' }}" alt="Pratinjau Gambar" class="{{ $surveyLocation->image ? '' : 'hidden' }}"/>
                        <div id="placeholder" class="{{ $surveyLocation->image ? 'hidden' : '' }} text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            <p class="mt-2 text-sm">Belum ada gambar.</p>
                        </div>
                        <button type="button" id="change-image-button" class="mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-500 focus:outline-none">
                            {{ $surveyLocation->image ? 'Ganti Gambar' : 'Unggah Gambar' }}
                        </button>
                        <input type="file" id="image" name="image" class="hidden" onchange="previewImage(event);" accept="image/*">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah.</p>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Peta --}}
            <div class="flex flex-col space-y-6">
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi di Peta <span class="text-red-500">*</span></label>
                    <div id="map" class="w-full"></div>
                    
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="lat" class="block text-xs font-medium text-gray-500">Latitude</label>
                            <input type="text" id="lat" name="lat" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" value="{{ old('lat', $surveyLocation->geometry['lat']) }}" required readonly>
                        </div>
                        <div>
                            <label for="lng" class="block text-xs font-medium text-gray-500">Longitude</label>
                            <input type="text" id="lng" name="lng" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" value="{{ old('lng', $surveyLocation->geometry['lng']) }}" required readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('survey-locations.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700 transition">Perbarui Lokasi</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        const initialLocation = [
            parseFloat(latInput.value.replace(',', '.')), 
            parseFloat(lngInput.value.replace(',', '.'))
        ];
        const map = L.map('map').setView(initialLocation, 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker = L.marker(initialLocation, { draggable: true }).addTo(map);

        marker.on('dragend', function (event) {
            const position = marker.getLatLng();
            latInput.value = position.lat.toFixed(6);
            lngInput.value = position.lng.toFixed(6);
        });

        const changeImageBtn = document.getElementById('change-image-button');
        const imageInput = document.getElementById('image');
        changeImageBtn.addEventListener('click', (e) => {
            e.preventDefault(); 
            imageInput.click();
        });
    });

    function previewImage(event) {
        const input = event.target;
        const imgPreview = document.getElementById('image-preview');
        const placeholder = document.getElementById('placeholder');
        const changeImageBtn = document.getElementById('change-image-button');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                changeImageBtn.textContent = 'Ganti Gambar';
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush