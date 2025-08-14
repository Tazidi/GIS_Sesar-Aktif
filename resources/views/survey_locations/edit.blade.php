@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>
<style>
    #map { height: 450px; border-radius: 0.5rem; z-index: 10; }
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
            Memperbarui lokasi untuk proyek: <span class="font-semibold">{{ $surveyLocation->project->name }}</span>
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
                    @error('nama') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi', $surveyLocation->deskripsi) }}</textarea>
                </div>

                {{-- Blok untuk menampilkan dan mengubah 3 gambar --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Lokasi</label>
                    <p class="text-xs text-gray-500 mb-4">Kosongkan input file jika tidak ingin mengubah gambar yang sudah ada.</p>

                    @php
                        // Ambil path gambar dari model menggunakan accessor
                        $primaryImage = $surveyLocation->primary_image;
                        $additionalImages = $surveyLocation->additional_images;
                    @endphp

                    {{-- Gambar Utama --}}
                    <div class="mb-6 p-4 border rounded-lg">
                        <label for="image_primary" class="block text-xs font-medium text-gray-600">Gambar Utama (Wajib)</label>
                        @if($primaryImage)
                            <img src="{{ asset('survey/' . $primaryImage) }}" alt="Gambar Utama" class="my-2 h-24 w-auto rounded-md">
                        @endif
                        <input type="file" id="image_primary" name="image_primary" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('image_primary') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Gambar Tambahan 1 --}}
                    <div class="mb-6 p-4 border rounded-lg">
                        <label for="image_2" class="block text-xs font-medium text-gray-600">Gambar Tambahan 1</label>
                        @if(isset($additionalImages[0]))
                            <img src="{{ asset('survey/' . $additionalImages[0]) }}" alt="Gambar Tambahan 1" class="my-2 h-24 w-auto rounded-md">
                        @endif
                        <input type="file" id="image_2" name="image_2" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        @error('image_2') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Gambar Tambahan 2 --}}
                    <div class="p-4 border rounded-lg">
                        <label for="image_3" class="block text-xs font-medium text-gray-600">Gambar Tambahan 2</label>
                        @if(isset($additionalImages[1]))
                            <img src="{{ asset('survey/' . $additionalImages[1]) }}" alt="Gambar Tambahan 2" class="my-2 h-24 w-auto rounded-md">
                        @endif
                        <input type="file" id="image_3" name="image_3" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        @error('image_3') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Peta --}}
            <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi di Peta <span class="text-red-500">*</span></label>
                <div id="map" class="w-full"></div>
                
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="lat" class="block text-xs font-medium text-gray-500">Latitude</label>
                        <input type="text" id="lat" name="lat" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" value="{{ old('lat', $surveyLocation->geometry['lat']) }}" required readonly>
                        @error('lat') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="lng" class="block text-xs font-medium text-gray-500">Longitude</label>
                        <input type="text" id="lng" name="lng" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" value="{{ old('lng', $surveyLocation->geometry['lng']) }}" required readonly>
                        @error('lng') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('projects.show', $surveyLocation->project) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">Batal</a>
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
    });
</script>
@endpush
