@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    <div class="mb-6 pb-4 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900">Edit Artikel</h1>
        <p class="mt-1 text-sm text-gray-600">Lakukan perubahan pada artikel dan simpan untuk memperbarui.</p>
    </div>

    <div class="bg-white p-8 shadow-lg rounded-lg">
        <form method="POST" action="{{ route('articles.update', $article) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Judul dan Penulis --}}
            <div class="mb-5">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" id="title" name="title" value="{{ old('title', $article->title) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Judul artikel">
            </div>
            <div class="mb-5">
                <label for="author" class="block text-sm font-medium text-gray-700 mb-1">Penulis</label>
                <input type="text" id="author" name="author" value="{{ old('author', $article->author) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama penulis">
            </div>

            {{-- Konten --}}
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
                <textarea id="editor" name="content" rows="12">{{ old('content', $article->content) }}</textarea>
            </div>
            
            {{-- Kategori (Dropdown + Datalist) --}}
            <div class="mb-5">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori (Opsional)</label>
                <select id="category" name="category"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $article->category) == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tags (Hashtag) --}}
            <div class="mb-5">
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tags / Hashtags (Opsional)</label>
                <input type="text" id="tags" name="tags" value="{{ old('tags', $article->tags) }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Contoh: teknologi, ai, inovasi (pisahkan dengan koma)">
                <p class="mt-1 text-xs text-gray-500">Pisahkan setiap tag dengan koma.</p>
            </div>

            {{-- Thumbnail --}}
            <div class="mb-6">
                <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-2">Ganti Thumbnail (Opsional)</label>
                <input type="file" id="thumbnail" name="thumbnail" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>

            @if ($article->thumbnail)
                <div class="mb-6">
                    <p class="block text-sm font-medium text-gray-700 mb-2">Thumbnail Saat Ini</p>
                    <img src="{{ asset(strpos($article->thumbnail, 'thumbnails/') === 0 ? $article->thumbnail : 'thumbnails/' . basename($article->thumbnail)) }}" alt="Thumbnail saat ini" class="h-32 w-auto rounded-md shadow-md object-cover">
                </div>
            @endif

            <div class="flex items-center justify-end pt-6 border-t border-gray-200">
                <a href="{{ route('articles.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600">Perbarui Artikel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            ckfinder: {
                uploadUrl: "{{ route('ckeditor.upload').'?_token='.csrf_token() }}"
            }
        })
        .catch(error => {
            console.error(error);
        });
</script>
@endpush
