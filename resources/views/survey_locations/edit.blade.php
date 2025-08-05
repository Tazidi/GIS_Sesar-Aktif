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
    }
</style>
@endsection

@section('content')
<div class="container">
    <h2>Edit Lokasi Survey</h2>

    <form method="POST" action="{{ route('survey-locations.update', $surveyLocation) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Tempat</label>
            <input type="text" id="nama" name="nama" class="form-control" value="{{ old('nama', $surveyLocation->nama) }}" required>
        </div>
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" class="form-control">{{ old('deskripsi', $surveyLocation->deskripsi) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Ganti Gambar Lokasi (Opsional)</label>
            <input class="form-control" type="file" id="image" name="image" onchange="previewImage();">
            
            @if ($surveyLocation->image)
                <img id="image-preview" src="{{ asset('survey/' . $surveyLocation->image) }}" alt="Gambar saat ini"/>
            @else
                <img id="image-preview" src="#" alt="Pratinjau Gambar" style="display: none;"/>
            @endif
        </div>

        <div id="map" class="mb-3"></div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="lat" class="form-label">Latitude</label>
                <input type="text" id="lat" name="lat" class="form-control" value="{{ old('lat', $surveyLocation->geometry['lat']) }}" required readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label for="lng" class="form-label">Longitude</label>
                <input type="text" id="lng" name="lng" class="form-control" value="{{ old('lng', $surveyLocation->geometry['lng']) }}" required readonly>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Perbarui Lokasi</button>
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
        const initialLocation = [
            parseFloat(latInput.value.replace(',', '.')), 
            parseFloat(lngInput.value.replace(',', '.'))
        ];
        const map = L.map('map').setView(initialLocation, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker = L.marker(initialLocation, { draggable: true }).addTo(map);

        marker.on('dragend', function (event) {
            const position = marker.getLatLng();
            latInput.value = position.lat;
            lngInput.value = position.lng;
        });
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
