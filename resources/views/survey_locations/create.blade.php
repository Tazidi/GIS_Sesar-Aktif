@extends('layouts.app')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
<style>
    #map { height: 400px; border-radius: 0.5rem; }
    #image-preview {
        max-width: 100%;
        max-height: 250px;
        margin-top: 15px;
        border-radius: 0.5rem;
        display: none; /* Sembunyikan secara default */
    }
</style>
@endsection

@section('content')
<div class="container">
    <h2>Tambah Lokasi Survey</h2>
    <p>Lokasi Anda akan terdeteksi secara otomatis. Anda dapat menggeser penanda untuk menyesuaikan lokasi.</p>

    <!-- PENTING: Tambahkan enctype untuk upload file -->
    <form method="POST" action="{{ route('survey-locations.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Tempat</label>
            <input type="text" id="nama" name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Lokasi</label>
            <input class="form-control" type="file" id="image" name="image" onchange="previewImage();">
            <img id="image-preview" src="#" alt="Pratinjau Gambar"/>
        </div>

        <div id="map" class="mb-3"></div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="lat" class="form-label">Latitude</label>
                <input type="text" id="lat" name="lat" class="form-control" required readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label for="lng" class="form-label">Longitude</label>
                <input type="text" id="lng" name="lng" class="form-control" required readonly>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Simpan Lokasi</button>
        <a href="{{ route('survey-locations.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        const defaultLocation = [-6.9175, 107.6191]; // Bandung
        const map = L.map('map').setView(defaultLocation, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker = L.marker(defaultLocation, { draggable: true }).addTo(map);

        function updateMarkerAndInputs(lat, lng) {
            marker.setLatLng([lat, lng]);
            map.panTo([lat, lng]);
            latInput.value = lat;
            lngInput.value = lng;
        }

        marker.on('dragend', function (event) {
            const position = marker.getLatLng();
            latInput.value = position.lat;
            lngInput.value = position.lng;
        });

        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                updateMarkerAndInputs(position.coords.latitude, position.coords.longitude);
            }, function (error) {
                console.warn(`ERROR(${error.code}): ${error.message}`);
                updateMarkerAndInputs(defaultLocation[0], defaultLocation[1]);
            });
        } else {
            console.log('Geolocation tidak tersedia.');
            updateMarkerAndInputs(defaultLocation[0], defaultLocation[1]);
        }
    });

    function previewImage() {
        const image = document.querySelector('#image');
        const imgPreview = document.querySelector('#image-preview');
        imgPreview.style.display = 'block';
        const oFReader = new FileReader();
        oFReader.readAsDataURL(image.files[0]);
        oFReader.onload = function(oFREvent) {
            imgPreview.src = oFREvent.target.result;
        }
    }
</script>
@endpush
