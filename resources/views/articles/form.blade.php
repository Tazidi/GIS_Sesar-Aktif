<form method="POST" action="{{ isset($article) ? route('articles.update', $article) : route('articles.store') }}" enctype="multipart/form-data">
    @csrf
    @if(isset($article))
        @method('PUT')
    @endif

    <input name="title" value="{{ old('title', $article->title ?? '') }}" placeholder="Judul" class="block mb-2 w-full border px-2 py-1">
    <input name="author" value="{{ old('author', $article->author ?? '') }}" placeholder="Penulis" class="block mb-2 w-full border px-2 py-1">

    <textarea name="content" class="block mb-2 w-full border px-2 py-1" rows="6">{{ old('content', $article->content ?? '') }}</textarea>

    <div class="mb-4">
        <label class="block font-medium">Thumbnail (opsional)</label>
        <input type="file" name="thumbnail" class="w-full border rounded px-2 py-1">

        @if(isset($article) && $article->thumbnail)
            <p class="mt-2 text-sm text-gray-600">Thumbnail saat ini:</p>
            <img src="{{ asset($article->thumbnail) }}" alt="Thumbnail" class="h-24 mt-1 rounded shadow">
        @endif
    </div>

    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
</form>
