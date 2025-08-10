{{-- File ini hanya berisi loop untuk menampilkan artikel --}}
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
        <p class="text-lg">Tidak ada artikel yang cocok dengan pencarian atau filter Anda.</p>
        <p class="text-sm">Coba ubah kata kunci atau filter Anda.</p>
    </div>
@endforelse
