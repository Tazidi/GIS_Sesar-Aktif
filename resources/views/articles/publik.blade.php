@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50" x-data="{ filterOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header & Filter --}}
        <div class="mb-8 pb-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Artikel Terbaru</h2>
                    <p class="text-sm text-gray-500 mt-1">Jelajahi semua artikel yang dipublikasikan.</p>
                </div>
                
                <form action="{{ route('artikel.publik') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
                    <div class="relative flex-grow">
                        <input type="search" id="search-input" name="search" placeholder="Cari artikel, hashtag..." value="{{ request('search') }}"
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>

                    <div class="relative">
                        <button type="button" @click="filterOpen = !filterOpen" class="flex items-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V15a1 1 0 01-.293.707l-2 2A1 1 0 019 17v-6.586L4.293 6.707A1 1 0 014 6V3z" clip-rule="evenodd" /></svg>
                            <span>Filter & Urutkan</span>
                        </button>
                        
                        {{-- Dropdown Filter --}}
                        <div x-show="filterOpen" @click.away="filterOpen = false" x-transition class="absolute z-20 top-full right-0 mt-2 w-screen max-w-xs sm:max-w-sm bg-white shadow-xl rounded-lg p-6" style="display: none;">
                            <h3 class="text-xl font-semibold mb-4 pb-3 border-b border-gray-200 text-gray-800">Filter & Urutkan</h3>
                            <div class="space-y-6">
                                <div>
                                    <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-1">Filter Kategori</label>
                                    <select name="category" id="category-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Semua Kategori</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="sort-filter" class="block text-sm font-medium text-gray-700 mb-1">Urutkan</label>
                                    <select name="sort" id="sort-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Tanggal Terbit</option>
                                        <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Judul Artikel</option>
                                        <option value="author" {{ request('sort') == 'author' ? 'selected' : '' }}>Nama Penulis</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="order-filter" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                                    <select name="order" id="order-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="desc" {{ request('order', 'desc') == 'desc' ? 'selected' : '' }}>Terbaru / Z-A</option>
                                        <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Terlama / A-Z</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">Terapkan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

{{-- Layout Konten Artikel --}}
        @if ($articles->isNotEmpty())

            {{-- Logika Halaman Pertama --}}
            @if ($articles->currentPage() == 1)
                
                {{-- Grid Atas: 3 Artikel pertama dalam bentuk card vertikal --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($articles->take(3) as $article)
                        @include('partials.article_card_vertical', ['article' => $article])
                    @endforeach
                </div>

                {{-- Pemisah dan judul untuk artikel lainnya (jika ada) --}}
                @if($articles->count() > 3)
                    <hr class="my-12 border-gray-300">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Artikel Lainnya</h3>
                
                    {{-- Grid Bawah: Menampilkan sisa artikel --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach ($articles->skip(3) as $article)
                            @include('partials.article_card_vertical', ['article' => $article])
                        @endforeach
                    </div>
                @endif

            @else 
                {{-- Logika untuk Halaman 2 dan seterusnya (tetap sama) --}}
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Artikel Lainnya</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($articles as $article)
                        @include('partials.article_card_vertical', ['article' => $article])
                    @endforeach
                </div>
            @endif

        @else
            <div class="bg-white shadow-md p-12 text-center text-gray-500 rounded-lg">
                <p>Artikel tidak ditemukan.</p>
            </div>
        @endif
        
        {{-- Pagination --}}
            <div id="pagination-container" class="mt-12">
                <div class="flex justify-center">
                    {{ $articles->links('vendor.pagination.modern') }}
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
@endsection

{{-- 
@push('scripts')
<script>
// SCRIPT AJAX DINONAKTIFKAN
// Perubahan layout yang kompleks ini lebih baik menggunakan full-page reload
// untuk memastikan semua elemen (artikel random, dll) diperbarui dengan benar saat filtering.
// Jika fungsionalitas AJAX ingin dipertahankan, logic fetchArticles() perlu dirombak total
// untuk membangun kembali seluruh struktur DOM, bukan hanya mengganti satu container.
</script>
@endpush 
--}}