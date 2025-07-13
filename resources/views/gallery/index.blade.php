@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="px-4 sm:px-6 lg:px-8">
        {{-- Judul Halaman --}}
        <div class="mb-8 border-b border-gray-300">
            <h2 class="text-3xl font-bold inline-block pb-2 border-b-4 border-red-600">Galeri Foto</h2>
        </div>

        @if($images->isNotEmpty())
            {{-- Menggunakan 'columns' untuk membuat layout masonry yang responsif --}}
            <div class="columns-2 md:columns-3 lg:columns-4 gap-4">
                @foreach ($images as $image)
                    <div class="mb-4 break-inside-avoid">
                        {{-- 1. Link ini memicu Fancybox untuk membuka konten dari #id --}}
                        <a href="#gallery-item-{{ $image->id }}" data-fancybox
                           class="block group relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow">
                            <img class="h-auto w-full max-w-full rounded-lg" src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->title }}">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 p-4 text-center">
                                <i class="fas fa-search-plus text-white text-3xl"></i>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- KONTEN POPUP YANG TERSEMBUNYI --}}
            <div style="display: none;">
                @foreach ($images as $image)
                    {{-- 2. Ini adalah layout 2 kolom yang akan muncul di popup. ID-nya cocok dengan href di atas. --}}
                    <div id="gallery-item-{{ $image->id }}" class="flex flex-col md:flex-row max-w-4xl w-full h-auto">
                        <div class="w-full md:w-2/3 flex-shrink-0">
                            <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-full object-contain">
                        </div>
                        <div class="w-full md:w-1/3 p-6 flex flex-col bg-white">
                            <h3 class="text-2xl font-bold mb-4">{{ $image->title }}</h3>
                            <div class="text-gray-600 prose max-w-none flex-grow">
                                <p>{{ $image->description ?? 'Tidak ada deskripsi.' }}</p>
                            </div>
                            <p class="text-xs text-gray-400 mt-4">Diunggah oleh: {{ $image->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            
        @else
            <div class="bg-white shadow-md p-12 text-center text-gray-500 rounded-lg">
                <p>Belum ada gambar di galeri.</p>
            </div>
        @endif

        {{-- Link Pagination --}}
        <div class="mt-8">
            {{ $images->links() }}
        </div>
    </div>
</div>
@endsection

{{-- ========================================================= --}}
{{-- KUNCI PERBAIKAN: SCRIPT INISIALISASI FANCYBOX --}}
{{-- ========================================================= --}}
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Script ini secara eksplisit memberitahu Fancybox untuk aktif
        Fancybox.bind("[data-fancybox]", {
            // Opsi kustom bisa ditambahkan di sini jika perlu
        });
    });
</script>
@endsection