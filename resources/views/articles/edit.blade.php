@extends('layouts.app')

@section('content')
<h1>Edit Artikel</h1>

<form method="POST" action="{{ route('articles.update', $article) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <input name="title" value="{{ old('title', $article->title) }}" placeholder="Judul" class="block mb-2 w-full border px-2 py-1">

    <textarea name="content" class="block mb-2 w-full border px-2 py-1" rows="6">{{ old('content', $article->content) }}</textarea>

    <label class="block mb-1">Thumbnail (opsional)</label>
    <input type="file" name="thumbnail" class="mb-2">

    @if($article->thumbnail)
        <div class="mb-4">
            <p class="text-sm text-gray-600">Thumbnail saat ini:</p>
            <img src="{{ asset($article->thumbnail) }}" alt="Thumbnail" class="h-24 rounded shadow mt-1">
        </div>
    @endif

    <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded">Perbarui</button>
</form>
@endsection
