@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Daftar Artikel</h1>
    <form method="GET">
        <input type="text" name="search" placeholder="Cari artikel..." value="{{ request('search') }}" />
    </form>

    @foreach($articles as $article)
        <a href="{{ route('article.show', $article->id) }}">
            <h2>{{ $article->title }}</h2>
        </a>
    @endforeach

    <hr>
    <h1>Visualisasi Peta</h1>
    @foreach($maps as $map)
        <p>{{ $map->title }}</p>
        <iframe src="{{ asset('storage/' . $map->file_path) }}" width="100%" height="300"></iframe>
    @endforeach

    <hr>
    <h2>Peta Interaktif</h2>
    <div id="map" style="height: 400px;"></div>
</div>
@endsection

@section('scripts')
<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

<script>
    var map = L.map('map').setView([-6.2, 106.8], 10);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Contoh pemanggilan GeoJSON statis
    fetch('/storage/maps/sample.json')
        .then(res => res.json())
        .then(data => L.geoJSON(data).addTo(map));
</script>
@endsection
