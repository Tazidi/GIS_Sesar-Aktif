@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    {{-- Tombol Kembali --}}
    <div class="mb-6">
        <a href="{{ route('gallery_maps.projects.show', $project) }}"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Kembali ke Proyek
        </a>
    </div>

    {{-- Card Detail Lokasi --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-20">
        @if ($location->primary_image)
            <img src="{{ asset('survey/' . $location->primary_image) }}"
                 alt="{{ $location->nama }}"
                 class="w-full h-64 md:h-96 object-cover">
        @endif
        
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">
                {{ $location->nama }}
            </h1>

            @if($location->deskripsi)
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-2">Deskripsi</h2>
                    <p class="text-gray-600 leading-relaxed">
                        {{ $location->deskripsi }}
                    </p>
                </div>
            @endif

            <div class="bg-gray-50 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-700 mb-3">Koordinat Lokasi</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <span class="font-medium text-gray-600 w-20">Latitude:</span>
                        <span class="text-gray-800 font-mono">{{ $location->geometry['lat'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="font-medium text-gray-600 w-20">Longitude:</span>
                        <span class="text-gray-800 font-mono">{{ $location->geometry['lng'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
