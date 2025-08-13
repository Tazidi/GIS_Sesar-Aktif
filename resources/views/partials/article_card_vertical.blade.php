{{-- resources/views/partials/article_card_vertical.blade.php --}}
<div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col h-full hover:shadow-xl transition-shadow duration-300">
    @if($article->thumbnail)
        <a href="{{ route('articles.show', $article) }}">
            <img src="{{ asset(strpos($article->thumbnail, 'thumbnails/') === 0 ? $article->thumbnail : 'thumbnails/' . basename($article->thumbnail)) }}" alt="{{ $article->title }}" class="w-full h-48 object-cover">
        </a>
    @endif
    <div class="p-5 flex flex-col flex-grow">
        <div class="flex-grow">
            @if($article->category)
                <span class="inline-block bg-indigo-100 text-indigo-800 text-xs font-medium mb-2 px-2.5 py-0.5 rounded-full">{{ $article->category }}</span>
            @endif
            <h3 class="font-bold text-lg mb-2">
                <a href="{{ route('articles.show', $article) }}" class="hover:text-indigo-600 transition-colors">
                    {{ $article->title }}
                </a>
            </h3>
        </div>
        <div class="mt-4">
            @if(!empty($article->tags_as_array))
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach ($article->tags_as_array as $tag)
                        <a href="{{ route('artikel.publik', ['search' => $tag]) }}" class="text-xs bg-gray-200 text-gray-800 px-2 py-1 rounded-full hover:bg-gray-300">#{{ $tag }}</a>
                    @endforeach
                </div>
            @endif
            <div class="text-xs text-gray-500">
                <span>Oleh {{ $article->author ?? 'N/A' }} • {{ $article->created_at->format('d M Y') }}</span>
            </div>
        </div>
    </div>
</div>