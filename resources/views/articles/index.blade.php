@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="px-4 sm:px-6 lg:px-8">
        {{-- Judul Halaman --}}
        <div class="mb-8 border-b border-gray-300">
            <h2 class="text-3xl font-bold inline-block pb-2 border-b-4 border-red-600">Semua Artikel</h2>
        </div>

        {{-- Wadah untuk daftar artikel --}}
        <div class="space-y-8">
            @forelse ($articles as $article)
                {{-- Setiap item artikel --}}
        {{-- 1. Tambahkan tinggi tetap di sini (misal: h-64 atau sekitar 256px) --}}
        <div class="flex flex-col md:flex-row bg-white shadow-md overflow-hidden h-64">
            {{-- Gambar Thumbnail --}}
            @if($article->thumbnail)
            <div class="md:w-1/3 h-full">
                <a href="{{ route('articles.show', $article) }}">
                    {{-- 2. Pastikan gambar mengisi tinggi penuh (h-full) dari wadahnya --}}
                    <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="Thumbnail for {{ $article->title }}" class="w-full h-full object-cover hover:opacity-80 transition-opacity">
                </a>
            </div>
            @endif
            
            {{-- Detail Artikel --}}
            <div class="p-6 flex flex-col justify-between {{ $article->thumbnail ? 'md:w-2/3' : 'w-full' }}">
                <div>
                    <h3 class="font-bold text-2xl mb-2">
                        <a href="{{ route('articles.show', $article) }}" class="hover:text-red-600 transition-colors">
                            {{ $article->title }}
                        </a>
                    </h3>
                    <p class="text-gray-600 text-sm mb-4">
                        {{ Str::limit(strip_tags($article->content), 200) }}
                    </p>
                </div>
                <div class="text-xs text-gray-500 mt-4 flex items-center justify-between">
                    <span>Oleh {{ $article->user->name ?? 'N/A' }} &bull; {{ $article->created_at->format('d M Y') }}</span>
                    <a href="{{ route('articles.show', $article) }}" class="font-semibold text-indigo-600 hover:text-indigo-900">
                        Baca Selengkapnya &rarr;
                    </a>
                </div>
            </div>
        </div>
        {{-- ========================================================= --}}
        {{-- BATAS AKHIR BLOK ARTIKEL --}}
        {{-- ========================================================= --}}
    @empty
        <div class="bg-white shadow-md p-12 text-center text-gray-500">
            <p>Belum ada artikel yang dipublikasikan.</p>
        </div>
    @endforelse
</div>

        {{-- Link Pagination --}}
        <div class="mt-8">
            {{ $articles->links() }}
        </div>

    </div>
</div>
@endsection