<form method="POST" action="{{ isset($article) ? route('articles.update', $article) : route('articles.store') }}" enctype="multipart/form-data">
    @csrf
    @if(isset($article))
        @method('PUT')
    @endif

    <div class="mb-4">
        <label for="title" class="block font-medium">Judul</label>
        <input id="title" name="title" value="{{ old('title', $article->title ?? '') }}" placeholder="Judul" class="block w-full border px-2 py-1 rounded">
    </div>

    <div class="mb-4">
        <label for="author" class="block font-medium">Penulis</label>
        <input id="author" name="author" value="{{ old('author', $article->author ?? '') }}" placeholder="Penulis" class="block w-full border px-2 py-1 rounded">
    </div>

    <div class="mb-4">
        <label for="content" class="block font-medium">Konten</label>
        <textarea id="content" name="content" class="block w-full border px-2 py-1 rounded" rows="6">{{ old('content', $article->content ?? '') }}</textarea>
    </div>

    {{-- Dropdown untuk Tag --}}
    {{-- Input Tag --}}
    <div class="mb-5">
        <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tag (Opsional)</label>
        <input type="text" id="tags" name="tags" value="{{ old('tags') }}"
            placeholder="Misal: Berita, Artikel Ilmiah, Opini"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('tags') border-red-500 @enderror">
        @error('tags')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>


    <div class="mb-4">
        <label class="block font-medium">Thumbnail (opsional)</label>
        <input type="file" name="thumbnail" class="w-full border rounded px-2 py-1">

        @if(isset($article) && $article->thumbnail)
            <p class="mt-2 text-sm text-gray-600">Thumbnail saat ini:</p>
            <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}" alt="Thumbnail" class="h-24 mt-1 rounded shadow">
        @endif
    </div>

    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
        {{ isset($article) ? 'Perbarui' : 'Simpan' }}
    </button>
</form>