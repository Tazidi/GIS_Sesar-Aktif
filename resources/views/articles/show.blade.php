@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
  <div class="mb-6 pb-4 border-b border-gray-200">
    <h1 class="text-3xl font-bold text-gray-900">{{ $article->title }}</h1>
    <p class="mt-1 text-sm text-gray-600">Oleh <span class="font-semibold">{{ $article->author }}</span> • {{ $article->created_at->format('d M Y') }}</p>
  </div>

  <div class="bg-white p-8 shadow-lg rounded-lg">
    @if ($article->thumbnail)
      <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}" class="w-full max-h-[400px] object-cover rounded-md mb-6" alt="...">
    @endif

    <!-- PENTING: .ck-content supaya aturan content styles diterapkan -->
    <div class="ck-content">
      {!! $article->content !!}
    </div>
  </div>
</div>
@endsection
