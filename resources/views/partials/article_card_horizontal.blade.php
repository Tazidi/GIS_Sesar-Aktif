{{-- resources/views/partials/article_card_horizontal.blade.php --}}
<div class="flex flex-col sm:flex-row items-center bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
    @if($article->thumbnail)
    <a href="{{ route('articles.show', $article) }}" class="block w-full sm:w-1/3 flex-shrink-0">
        <img src="{{ asset(strpos($article->thumbnail, 'thumbnails/') === 0 ? $article->thumbnail : 'thumbnails/' . basename($article->thumbnail)) }}" 
             alt="Thumbnail untuk {{ $article->title }}" 
             class="w-full h-48 sm:h-40 object-cover">
    </a>
    @endif
    <div class="p-5 flex flex-col justify-between self-stretch">
        <div>
            @if($article->category)
              <span class="inline-block bg-indigo-100 text-indigo-800 text-xs font-medium mb-2 px-2.5 py-0.5 rounded-full">{{ $article->category }}</span>
            @endif
            <h3 class="font-bold text-xl mb-2">
                <a href="{{ route('articles.show', $article) }}" class="hover:text-indigo-600 transition-colors">
                    {{ $article->title }}
                </a>
            </h3>
            <p class="text-gray-600 text-sm">
                {{ Str::limit(strip_tags($article->content), 120) }}
            </p>
        </div>
        <div class="text-xs text-gray-500 mt-4">
            <span>Oleh {{ $article->author ?? 'N/A' }} • {{ $article->created_at->format('d M Y') }}</span>
        </div>
    </div>
</div>