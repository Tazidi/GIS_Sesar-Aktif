@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map { 
        height: 450px; 
        border-radius: 0.75rem; /* rounded halus */
        z-index: 10;
        border: 1px solid #e5e7eb; /* abu-abu soft */
        box-shadow: 0 2px 6px rgba(0,0,0,0.08); /* soft shadow */
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Tambah Lokasi Survey Baru
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            Menambahkan lokasi untuk proyek: <span class="font-semibold">{{ $project->name }}</span>
        </p>
    </div>

    <form method="POST" action="{{ route('projects.survey-locations.store', $project) }}" 
          enctype="multipart/form-data" 
          class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
        @csrf

        {{-- Peta --}}
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
            <h2 class="text-lg font-semibold text-indigo-700 mb-4">Peta Lokasi</h2>
            <div id="map" class="w-full relative rounded-lg overflow-hidden">
                <div id="map-loader" 
                    class="absolute inset-0 bg-gray-500 bg-opacity-30 flex items-center justify-center z-20 rounded-lg">
                    <p class="text-white font-semibold">Mencari lokasi Anda...</p>
                </div>
            </div>
        </div>

        {{-- Lokasi Dinamis --}}
        <div id="locations-wrapper" class="space-y-6">
            <div class="location-item p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition">
                <h3 class="text-lg font-semibold text-indigo-700 mb-4">Lokasi 1</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Tempat <span class="text-red-500">*</span></label>
                        <input type="text" name="locations[0][nama]" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="locations[0][deskripsi]" rows="2" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Utama</label>
                            <input type="file" name="locations[0][image_primary]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Tambahan 1</label>
                            <input type="file" name="locations[0][image_2]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Tambahan 2</label>
                            <input type="file" name="locations[0][image_3]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Latitude</label>
                            <input type="text" name="locations[0][lat]" 
                                class="lat-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Longitude</label>
                            <input type="text" name="locations[0][lng]" 
                                class="lng-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" id="add-location"
            class="mt-4 px-3 py-1 bg-indigo-500 text-white rounded-md text-sm">+ Tambah Lokasi</button>   

        {{-- Tombol Aksi --}}
        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('projects.show', $project) }}" 
               class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" 
                    class="px-6 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700 transition">Simpan Lokasi</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mapLoader = document.getElementById('map-loader');
        const defaultLocation = [-6.9175, 107.6191]; // Bandung
        const map = L.map('map').setView(defaultLocation, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // simpan semua marker
        let markers = [];
        let markerFormMap = {};

        // fungsi bikin marker baru
        function createMarker(lat, lng, index, latInput, lngInput) {
            let marker = L.marker([lat, lng], { draggable: true }).addTo(map);

            // tooltip nomor lokasi
            marker.bindTooltip(`Lokasi ${index + 1}`, { permanent: true, direction: "top" }).openTooltip();

            // drag marker → update input
            marker.on('dragend', function () {
                const pos = marker.getLatLng();
                latInput.value = pos.lat.toFixed(6);
                lngInput.value = pos.lng.toFixed(6);
            });

            // klik marker → scroll ke form
            marker.on('click', function () {
                const formBlock = document.querySelectorAll('.location-item')[index];
                formBlock.scrollIntoView({ behavior: "smooth", block: "center" });
                formBlock.classList.add("ring-2", "ring-indigo-500");
                setTimeout(() => formBlock.classList.remove("ring-2", "ring-indigo-500"), 2000);
            });

            markers[index] = marker;
            markerFormMap[index] = marker;
            return marker;
        }

        // fungsi update marker dari input
        function bindInputEvents(latInput, lngInput, index) {
            function updateFromInput() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);

                if (!isNaN(lat) && !isNaN(lng)) {
                    if (markers[index]) {
                        // update marker yang sudah ada
                        markers[index].setLatLng([lat, lng]);
                    } else {
                        // bikin marker baru kalau belum ada
                        markers[index] = createMarker(lat, lng, index, latInput, lngInput);
                    }
                    map.panTo([lat, lng]);
                }
            }
            latInput.addEventListener('change', updateFromInput);
            lngInput.addEventListener('change', updateFromInput);
        }

        // lokasi pertama default
        const firstLat = document.querySelector('input[name="locations[0][lat]"]');
        const firstLng = document.querySelector('input[name="locations[0][lng]"]');
        bindInputEvents(firstLat, firstLng, 0);

        // geolocation default
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    firstLat.value = pos.coords.latitude.toFixed(6);
                    firstLng.value = pos.coords.longitude.toFixed(6);
                    createMarker(pos.coords.latitude, pos.coords.longitude, 0, firstLat, firstLng);
                    mapLoader.style.display = 'none';
                },
                () => {
                    firstLat.value = defaultLocation[0];
                    firstLng.value = defaultLocation[1];
                    createMarker(defaultLocation[0], defaultLocation[1], 0, firstLat, firstLng);
                    mapLoader.style.display = 'none';
                }
            );
        } else {
            firstLat.value = defaultLocation[0];
            firstLng.value = defaultLocation[1];
            createMarker(defaultLocation[0], defaultLocation[1], 0, firstLat, firstLng);
            mapLoader.style.display = 'none';
        }

        // klik peta → tambah marker baru + blok input baru
        map.on('click', function (e) {
            const wrapper = document.getElementById('locations-wrapper');
            const index = wrapper.querySelectorAll('.location-item').length;

            const html = `
            <div class="location-item p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition mt-4">
                <h3 class="text-lg font-semibold text-indigo-700 mb-4">Lokasi ${index + 1}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Tempat <span class="text-red-500">*</span></label>
                        <input type="text" name="locations[${index}][nama]" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="locations[${index}][deskripsi]" rows="2" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Utama</label>
                            <input type="file" name="locations[${index}][image_primary]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Tambahan 1</label>
                            <input type="file" name="locations[${index}][image_2]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Tambahan 2</label>
                            <input type="file" name="locations[${index}][image_3]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Latitude</label>
                            <input type="text" name="locations[${index}][lat]"
                                class="lat-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Longitude</label>
                            <input type="text" name="locations[${index}][lng]"
                                class="lng-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>

                    <button type="button" class="remove-location bg-red-500 text-white text-xs px-2 py-1 rounded mt-2">
                        Hapus Lokasi
                    </button>
                </div>
            </div>`;
            wrapper.insertAdjacentHTML('beforeend', html);

            const latInput = wrapper.querySelector(`input[name="locations[${index}][lat]"]`);
            const lngInput = wrapper.querySelector(`input[name="locations[${index}][lng]"]`);

            // isi otomatis lat/lng dari klik peta
            latInput.value = e.latlng.lat.toFixed(6);
            lngInput.value = e.latlng.lng.toFixed(6);

            createMarker(e.latlng.lat, e.latlng.lng, index, latInput, lngInput);
            bindInputEvents(latInput, lngInput, index);
        });

        // tombol +Tambah Lokasi manual
        document.getElementById('add-location').addEventListener('click', function() {
            const wrapper = document.getElementById('locations-wrapper');
            const index = wrapper.querySelectorAll('.location-item').length;
            const html = `
            <div class="location-item p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition mt-4">
                <h3 class="text-lg font-semibold text-indigo-700 mb-4">Lokasi ${index + 1}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Tempat <span class="text-red-500">*</span></label>
                        <input type="text" name="locations[${index}][nama]" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="locations[${index}][deskripsi]" rows="2" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Utama</label>
                            <input type="file" name="locations[${index}][image_primary]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Tambahan 1</label>
                            <input type="file" name="locations[${index}][image_2]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Gambar Tambahan 2</label>
                            <input type="file" name="locations[${index}][image_3]" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Latitude</label>
                            <input type="text" name="locations[${index}][lat]"
                                class="lat-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Longitude</label>
                            <input type="text" name="locations[${index}][lng]"
                                class="lng-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>

                    <button type="button" class="remove-location bg-red-500 text-white text-xs px-2 py-1 rounded mt-2">
                        Hapus Lokasi
                    </button>
                </div>
            </div>`;
            wrapper.insertAdjacentHTML('beforeend', html);

            const latInput = wrapper.querySelector(`input[name="locations[${index}][lat]"]`);
            const lngInput = wrapper.querySelector(`input[name="locations[${index}][lng]"]`);

            // isi otomatis lat/lng dari klik peta
            latInput.value = e.latlng.lat.toFixed(6);
            lngInput.value = e.latlng.lng.toFixed(6);

            bindInputEvents(latInput, lngInput, index);
        });
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-location')) {
                const formBlock = e.target.closest('.location-item');
                const index = Array.from(document.querySelectorAll('.location-item')).indexOf(formBlock);

                // hapus marker
                if (markers[index]) {
                    map.removeLayer(markers[index]);
                    markers.splice(index, 1); // buang marker dari array
                }

                // hapus form
                formBlock.remove();

                // re-index semua lokasi
                reIndexLocations();
            }
        });
        function reIndexLocations() {
            const blocks = document.querySelectorAll('.location-item');
            blocks.forEach((block, i) => {
                // Update heading
                let heading = block.querySelector('h3');
                if (!heading) {
                    heading = document.createElement('h3');
                    heading.classList.add('font-bold', 'mb-2');
                    block.prepend(heading);
                }
                heading.textContent = `Lokasi ${i + 1}`;

                // Update semua input name sesuai index baru
                block.querySelectorAll('input, textarea').forEach(input => {
                    input.name = input.name.replace(/locations\[\d+\]/, `locations[${i}]`);
                });

                // Update tooltip marker kalau masih ada
                if (markers[i]) {
                    markers[i].unbindTooltip();
                    markers[i].bindTooltip(`Lokasi ${i + 1}`, { permanent: true, direction: "top" }).openTooltip();
                }
            });
        }
    });
</script>
@endpush
