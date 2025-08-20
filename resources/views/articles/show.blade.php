@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
  <div class="mb-6 pb-4 border-b border-gray-200">
    @if($article->category)
      <span class="inline-block bg-indigo-100 text-indigo-800 text-sm font-medium mb-2 px-3 py-1 rounded-full">{{ $article->category }}</span>
    @endif
    <h1 class="text-3xl font-bold text-gray-900">{{ $article->title }}</h1>
    
    <!-- Modified Section for Author, Date, View Count, and Share -->
    <div class="flex justify-between items-center mt-2 text-sm text-gray-600">
      <p>Oleh <span class="font-semibold">{{ $article->author }}</span> • {{ $article->created_at->format('d M Y') }}</p>
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-1.5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>{{ $article->visit_count ?? 0 }} dilihat</span>

        <!-- Share Button Section -->
        <div class="relative ml-4">
          <button id="shareButton" class="flex items-center text-gray-600 hover:text-gray-900 transition-colors" title="Bagikan artikel">
            <svg class="w-5 h-5 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            <span>Bagikan</span>
          </button>
          <div id="copyFeedback" class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 w-max bg-gray-800 text-white text-xs font-bold px-3 py-1 rounded-md opacity-0 transition-opacity duration-300 pointer-events-none">
            Link disalin!
          </div>
        </div>
      </div>
    </div>
    <!-- End of Modified Section -->

  </div>

  <div class="bg-white p-8 shadow-lg rounded-lg">
    @if ($article->thumbnail)
      <img src="{{ asset(strpos($article->thumbnail, 'thumbnails/') === 0 ? $article->thumbnail : 'thumbnails/' . basename($article->thumbnail)) }}" class="w-full max-h-[400px] object-cover rounded-md mb-6" alt="Article Thumbnail">
    @endif

    <div class="prose prose-lg max-w-none ck-content">
      {!! $article->content !!}
    </div>

    @if ($article->tags)
      <div class="mt-8 pt-6 border-t border-gray-200">
          <p class="text-sm font-semibold text-gray-600 mb-3">Tags:</p>
          <div class="flex flex-wrap gap-2">
              @foreach ($article->tags_as_array as $tag)
                  <a href="{{ route('artikel.publik', ['search' => $tag]) }}" class="bg-gray-200 text-gray-800 text-xs font-semibold px-3 py-1 rounded-full hover:bg-gray-300 transition-colors">
                      #{{ $tag }}
                  </a>
              @endforeach
          </div>
      </div>
    @endif
    
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const shareButton = document.getElementById('shareButton');
  const copyFeedback = document.getElementById('copyFeedback');

  shareButton.addEventListener('click', async () => {
    const articleUrl = window.location.href;
    const articleTitle = document.title;

    // Check if the Web Share API is supported
    if (navigator.share) {
      try {
        await navigator.share({
          title: articleTitle,
          url: articleUrl,
        });
        console.log('Artikel berhasil dibagikan');
      } catch (error) {
        console.error('Gagal membagikan:', error);
      }
    } else {
      // Fallback for desktop: copy to clipboard
      try {
        await navigator.clipboard.writeText(articleUrl);
        
        // Show feedback message
        copyFeedback.classList.remove('opacity-0');
        copyFeedback.classList.add('opacity-100');

        setTimeout(() => {
          copyFeedback.classList.remove('opacity-100');
          copyFeedback.classList.add('opacity-0');
        }, 2000); // Hide after 2 seconds

      } catch (err) {
        console.error('Gagal menyalin link:', err);
        // You could add a fallback here for older browsers, like a prompt
      }
    }
  });
});
</script>
@endpush
