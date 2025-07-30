@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8 border-b border-gray-300">
            <h2 class="text-3xl font-bold inline-block pb-2 border-b-4 border-red-600">Artikel Publik</h2>
        </div>

        {{-- Form Filter dan Sorting --}}
        <form method="GET" class="mb-8 flex flex-col md:flex-row gap-4 md:items-end">
            {{-- Filter Tag --}}
            <div>
                <label for="tag" class="block text-sm font-medium text-gray-700">Filter Tag:</label>
                <select name="tag" id="tag" class="border rounded px-3 py-2 w-48">
                    <option value="">Semua</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag }}" {{ request('tag') == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Sort By --}}
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700">Urutkan Berdasarkan:</label>
                <select name="sort" id="sort" class="border rounded px-3 py-2 w-48">
                    <option value="">Tanggal Terbaru</option>
                    <option value="author" {{ request('sort') == 'author' ? 'selected' : '' }}>Nama Penulis</option>
                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Judul Artikel</option>
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Tanggal Terbit</option>
                </select>
            </div>

            {{-- Order --}}
            <div>
                <label for="order" class="block text-sm font-medium text-gray-700">Urutan:</label>
                <select name="order" id="order" class="border rounded px-3 py-2 w-36">
                    <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>A-Z / Lama</option>
                    <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Z-A / Baru</option>
                </select>
            </div>

            <div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded mt-1 md:mt-0">
                    Terapkan
                </button>
            </div>
        </form>

        {{-- Daftar Artikel --}}
        <div class="space-y-8">
            @forelse ($articles as $article)
                <div class="flex flex-col md:flex-row bg-white shadow-md overflow-hidden h-64">
                    @if($article->thumbnail)
                        <div class="md:w-1/3 h-full">
                            <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}"
                                 alt="Thumbnail"
                                 class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="p-6 flex flex-col justify-between {{ $article->thumbnail ? 'md:w-2/3' : 'w-full' }}">
                        <div>
                            <h3 class="font-bold text-2xl mb-2">{{ $article->title }}</h3>
                            <p class="text-gray-600 text-sm mb-4">
                                {{ Str::limit(strip_tags($article->content), 200) }}
                            </p>
                        </div>

                        <div class="text-xs text-gray-500 mt-4 flex justify-between items-center">
                            <span>
                                Oleh {{ $article->author ?? 'N/A' }} | 
                                {{ $article->created_at->format('d M Y') }} 
                                @if($article->tags)
                                    | <span class="bg-gray-200 px-2 py-0.5 rounded text-xs text-gray-800">{{ $article->tags }}</span>
                                @endif
                            </span>

                            <a href="{{ route('articles.show', $article) }}"
                               class="font-semibold text-indigo-600 hover:text-indigo-900 text-sm">
                                Baca Selengkapnya →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow p-8 text-center text-gray-500">Belum ada artikel.</div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
