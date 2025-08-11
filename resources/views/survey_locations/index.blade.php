@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    /* Style minimal untuk peta dan gambar di dalam tabel */
    .map-container-cell {
        height: 120px; /* Ukuran peta diperkecil agar tabel lebih ringkas */
        width: 180px;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb; /* gray-200 */
        overflow: hidden;
        margin: auto;
    }
    .coordinate-info-cell {
        font-size: 0.75rem; /* text-xs */
        color: #6b7280; /* gray-500 */
        margin-top: 0.25rem;
        line-height: 1.2;
    }
    .survey-image-cell {
        width: 96px; /* w-24 */
        height: 64px; /* h-16 */
        object-fit: cover;
        border-radius: 0.375rem; /* rounded-md */
        margin: auto;
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('dashboard') }}" 
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
            Kembali ke Dashboard
        </a>
    </div>

    {{-- Header Halaman --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Data Survey Anda
        </h1>
        <a href="{{ route('survey-locations.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Lokasi
        </a>
    </div>

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
            <p class="font-bold">Sukses</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- Container Tabel --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-center">No</th>
                        @if(auth()->user()->role === 'admin')
                            <th scope="col" class="px-6 py-3">Pengguna</th>
                        @endif
                        <th scope="col" class="px-6 py-3">Nama Tempat</th>
                        <th scope="col" class="px-6 py-3 text-center">Gambar</th>
                        <th scope="col" class="px-6 py-3">Deskripsi</th>
                        <th scope="col" class="px-6 py-3 text-center">Lokasi (Peta)</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    @forelse ($locations as $item)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-center">{{ $loop->iteration }}</td>
                            @if(auth()->user()->role === 'admin')
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item->user->name ?? '-' }}</td>
                            @endif
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->nama }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($item->image)
                                    {{-- BAGIAN INI TIDAK DIUBAH SESUAI PERMINTAAN ANDA --}}
                                    <img src="{{ asset('survey/' . $item->image) }}" alt="Gambar {{ $item->nama }}" class="survey-image-cell">
                                @else
                                    <span class="text-xs text-gray-400">Tidak ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 max-w-xs truncate" title="{{ $item->deskripsi }}">
                                {{ $item->deskripsi ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div id="map-{{ $item->id }}" class="map-container-cell"
                                     data-lat="{{ str_replace(',', '.', $item->geometry['lat']) }}"
                                     data-lng="{{ str_replace(',', '.', $item->geometry['lng']) }}"
                                     data-nama="{{ e($item->nama) }}">
                                </div>
                                <div class="coordinate-info-cell">
                                    {{ $item->geometry['lat'] }}, {{ $item->geometry['lng'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('survey-locations.edit', $item) }}"
                                   class="font-medium text-indigo-600 hover:text-indigo-900">Edit</a>
                                <span class="text-gray-300 mx-1">|</span>
                                <form action="{{ route('survey-locations.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus lokasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="text-center py-16 text-gray-500">
                                <div class="flex flex-col items-center">
                                    
                                    <h3 class="text-lg font-semibold mt-2">Belum Ada Data Survey</h3>
                                    <p class="text-sm mt-1">Silakan klik tombol "Tambah Lokasi" untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.map-container-cell').forEach(mapEl => {
            try {
                const lat = parseFloat(mapEl.dataset.lat);
                const lng = parseFloat(mapEl.dataset.lng);
                if (isNaN(lat) || isNaN(lng)) return;

                const map = L.map(mapEl.id, {
                    // Menonaktifkan interaksi peta karena hanya untuk display
                    zoomControl: false, 
                    scrollWheelZoom: false, 
                    doubleClickZoom: false,
                    dragging: false, 
                    touchZoom: false
                }).setView([lat, lng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                L.marker([lat, lng]).addTo(map).bindPopup(`<b>${mapEl.dataset.nama}</b>`);

            } catch (error) {
                mapEl.innerHTML = `<div class="p-2 text-center text-xs text-red-500">Gagal memuat peta</div>`;
            }
        });
    });
</script>
@endpush