@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 bg-white shadow-md rounded-lg p-8">

        {{-- Judul --}}
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $article->title }}</h1>

        {{-- Informasi Penulis dan Tanggal --}}
        <div class="text-sm text-gray-500 mb-6">
            Oleh <span class="font-semibold">{{ $article->author ?? 'Tidak diketahui' }}</span> •
            {{ $article->created_at->format('d M Y') }}
        </div>

        {{-- Thumbnail --}}
        @if ($article->thumbnail)
            <div class="mb-6">
                <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}"
                     alt="Thumbnail"
                     class="w-full max-h-[400px] object-cover rounded-md">
            </div>
        @endif

        {{-- Konten Artikel --}}
        <div class="prose max-w-none text-gray-800">
            {!! nl2br(e($article->content)) !!}
        </div>

    </div>
</div>
@endsection
