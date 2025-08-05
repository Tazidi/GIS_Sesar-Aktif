@extends('layouts.app')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .map-container {
        height: 200px;
        border-radius: 0.5rem;
        width: 100%;
        min-width: 250px;
    }

    .survey-image {
        max-width: 150px;
        height: auto;
        border-radius: 0.375rem;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Data Survey Anda</h2>
        <a href="{{ route('survey-locations.create') }}" class="btn btn-primary">Tambah Lokasi</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">No</th>
                    @if(auth()->user()->role === 'admin')
                        <th scope="col">Pengguna</th>
                    @endif
                    <th scope="col">Nama Tempat</th>
                    <th scope="col">Gambar</th>
                    <th scope="col">Deskripsi</th>
                    <th scope="col">Lokasi (Peta)</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($locations as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        @if(auth()->user()->role === 'admin')
                            <td>{{ $item->user->name ?? '-' }}</td>
                        @endif
                        <td>{{ $item->nama }}</td>
                        <td>
                            @if($item->image)
                                <img src="{{ asset('survey/' . $item->image) }}" alt="Gambar {{ $item->nama }}" class="survey-image">
                            @else
                                <small class="text-muted">Tidak ada</small>
                            @endif
                        </td>
                        <td>{{ $item->deskripsi }}</td>
                        <td>
                            <div id="map-{{ $item->id }}" class="map-container"
                                 data-lat="{{ str_replace(',', '.', $item->geometry['lat']) }}"
                                 data-lng="{{ str_replace(',', '.', $item->geometry['lng']) }}"
                                 data-nama="{{ e($item->nama) }}"
                                 data-deskripsi="{{ e($item->deskripsi) }}">
                            </div>
                            <small class="text-muted d-block mt-1">
                                Lat: {{ $item->geometry['lat'] }}, Lng: {{ $item->geometry['lng'] }}
                            </small>
                        </td>
                        <td>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a href="{{ route('survey-locations.edit', $item) }}" class="btn btn-sm btn-warning w-100">Edit</a>
                                <form action="{{ route('survey-locations.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');" class="w-100">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger w-100">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="text-center">
                            <div class="alert alert-info mb-0">
                                Anda belum memiliki data. Silakan <a href="{{ route('survey-locations.create') }}">tambahkan satu</a>.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.map-container').forEach(mapEl => {
            try {
                const lat = parseFloat(mapEl.dataset.lat);
                const lng = parseFloat(mapEl.dataset.lng);
                if (isNaN(lat) || isNaN(lng)) throw new Error('Koordinat tidak valid');

                const map = L.map(mapEl.id).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                L.marker([lat, lng]).addTo(map).bindPopup(`<b>${mapEl.dataset.nama}</b>`).openPopup();
            } catch (error) {
                mapEl.innerHTML = `<div class="alert alert-danger p-2">Gagal memuat peta: ${error.message}</div>`;
            }
        });
    });
</script>
@endpush
