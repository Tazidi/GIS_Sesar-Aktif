@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="mb-8 border-b border-gray-300">
            <h2 class="text-3xl font-bold inline-block pb-2 border-b-4 border-red-600">Artikel Publik</h2>
        </div>

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
                            <span>Oleh {{ $article->author ?? 'N/A' }} | {{ $article->created_at->format('d M Y') }}</span>
                            
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

        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
