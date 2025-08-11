@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Header Form --}}
    <div class="mb-6 pb-4 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-900">Edit Artikel</h1>
        <p class="mt-1 text-sm text-gray-600">Lakukan perubahan pada artikel dan simpan untuk memperbarui.</p>
    </div>

    {{-- Form Container --}}
    <div class="bg-white p-8 shadow-lg rounded-lg">
        <form method="POST" action="{{ route('articles.update', $article) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Judul Artikel --}}
            <div class="mb-5">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" id="title" name="title" value="{{ old('title', $article->title) }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                       placeholder="Judul artikel">
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Penulis --}}
            <div class="mb-5">
                <label for="author" class="block text-sm font-medium text-gray-700 mb-1">Penulis</label>
                <input type="text" id="author" name="author" value="{{ old('author', $article->author) }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('author') border-red-500 @enderror"
                       placeholder="Nama penulis">
                @error('author')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konten Artikel (CKEditor) --}}
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
                <textarea id="editor" name="content" rows="12"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('content') border-red-500 @enderror">{{ old('content', $article->content) }}</textarea>
                @error('content')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Dropdown untuk Tag --}}
            <div class="mb-5">
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tag (Opsional)</label>
                <select name="tags" id="tags" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('tags') border-red-500 @enderror">
                    <option value="">-- Tidak Ada Tag --</option>
                    @if(isset($tags) && $tags->count() > 0)
                        @foreach($tags as $tag)
                            <option value="{{ $tag }}" {{ old('tags', $article->tags) == $tag ? 'selected' : '' }}>
                                {{ $tag }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('tags')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Thumbnail --}}
            <div class="mb-6">
                <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-2">Ganti Thumbnail (Opsional)</label>
                <input type="file" id="thumbnail" name="thumbnail"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('thumbnail')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pratinjau Thumbnail Saat Ini --}}
            @if ($article->thumbnail)
                <div class="mb-6">
                    <p class="block text-sm font-medium text-gray-700 mb-2">Thumbnail Saat Ini</p>
                    <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}" alt="Thumbnail saat ini" 
                         class="h-32 w-auto rounded-md shadow-md object-cover">
                </div>
            @endif

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end pt-6 border-t border-gray-200">
                <a href="{{ route('articles.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition">
                    Perbarui Artikel
                </button>
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
