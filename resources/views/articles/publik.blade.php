@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50" x-data="{ filterOpen: false }">
    {{-- PERUBAHAN: Menghapus 'max-w-7xl mx-auto' agar lebar mengikuti layout utama --}}
    <div class="px-4 sm:px-6 lg:px-8">

        {{-- Header Halaman dan Tombol Filter --}}
        <div class="flex justify-between items-center mb-8 border-b border-gray-200 pb-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Artikel Publik</h2>
                <p class="text-sm text-gray-500 mt-1">Jelajahi semua artikel yang telah dipublikasikan.</p>
            </div>
            
            {{-- Tombol untuk membuka filter --}}
            <div class="relative">
                <button @click="filterOpen = !filterOpen" class="flex items-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V15a1 1 0 01-.293.707l-2 2A1 1 0 019 17v-6.586L4.293 6.707A1 1 0 014 6V3z" clip-rule="evenodd" />
                    </svg>
                    <span>Filter & Urutkan</span>
                </button>

                {{-- Kontainer Filter yang Melayang --}}
                <div x-show="filterOpen"
                     @click.away="filterOpen = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute z-20 top-full right-0 mt-2 w-screen max-w-xs sm:max-w-sm bg-white shadow-xl rounded-lg p-6"
                     style="display: none;">
                    
                    <h3 class="text-xl font-semibold mb-4 pb-3 border-b border-gray-200 text-gray-800">Filter & Urutkan</h3>
                    <form method="GET" action="{{ request()->url() }}" class="space-y-6">
                        
                        {{-- Filter Tag --}}
                        <div>
                            <label for="tag" class="block text-sm font-medium text-gray-700 mb-1">Filter berdasarkan Tag</label>
                            <select name="tag" id="tag" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Tag</option>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag }}" {{ request('tag') == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Urutkan berdasarkan</label>
                            <select name="sort" id="sort" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Tanggal Terbit</option>
                                <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Judul Artikel</option>
                                <option value="author" {{ request('sort') == 'author' ? 'selected' : '' }}>Nama Penulis</option>
                            </select>
                        </div>

                        <div>
                            <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                            <select name="order" id="order" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="desc" {{ request('order', 'desc') == 'desc' ? 'selected' : '' }}>Terbaru / Z-A</option>
                                <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Terlama / A-Z</option>
                            </select>
                        </div>

                        <div>
                            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                Terapkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kolom Utama: Daftar Artikel --}}
        <div>
            <div class="space-y-8">
                @forelse ($articles as $article)
                    <div class="flex flex-col md:flex-row bg-white shadow-lg rounded-xl overflow-hidden transition-transform transform hover:-translate-y-1 h-64">
                        @if($article->thumbnail)
                            <div class="md:w-1/3 h-full">
                                <a href="{{ route('articles.show', $article) }}">
                                    <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}"
                                            alt="Thumbnail untuk {{ $article->title }}"
                                            class="w-full h-full object-cover">
                                </a>
                            </div>
                        @endif

                        <div class="p-6 flex flex-col justify-between {{ $article->thumbnail ? 'md:w-2/3' : 'w-full' }}">
                            <div>
                                @if($article->tags)
                                    <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold mb-2">{{ $article->tags }}</span>
                                @endif
                                <h3 class="font-bold text-2xl mb-2">
                                    <a href="{{ route('articles.show', $article) }}" class="text-gray-900 hover:text-red-600 transition-colors">
                                        {{ $article->title }}
                                    </a>
                                </h3>
                                <p class="text-gray-600 text-sm mb-4">
                                    {{ Str::limit(strip_tags($article->content), 180) }}
                                </p>
                            </div>

                            <div class="text-xs text-gray-500 mt-4 flex justify-between items-center">
                                <span>
                                    Oleh <strong>{{ $article->author ?? 'N/A' }}</strong> • {{ $article->created_at->format('d M Y') }}
                                </span>
                                <a href="{{ route('articles.show', $article) }}"
                                    class="font-semibold text-indigo-600 hover:text-indigo-800 text-sm">
                                    Baca Selengkapnya →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow-md p-12 text-center text-gray-500 rounded-lg">
                        <p class="text-lg">Belum ada artikel yang ditemukan.</p>
                        <p class="text-sm">Coba ubah filter Anda atau periksa kembali nanti.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-10">
                {{ $articles->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
@endsection
