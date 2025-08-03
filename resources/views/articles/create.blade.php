@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Header Form --}}
    <div class="mb-6 border-b border-gray-200 pb-4">
        <h1 class="text-3xl font-bold text-gray-900">Buat Artikel Baru</h1>
        <p class="mt-1 text-sm text-gray-600">Isi semua kolom yang diperlukan untuk mempublikasikan artikel.</p>
    </div>

    {{-- Form Container --}}
    <div class="bg-white p-8 shadow-lg rounded-lg">
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <p class="font-bold text-red-700">Terjadi Kesalahan</p>
                <ul class="list-disc list-inside text-sm text-red-600 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Judul Artikel --}}
            <div class="mb-5">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                       placeholder="Contoh: Perkembangan Teknologi AI Terkini" required>
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Penulis --}}
            <div class="mb-5">
                <label for="author" class="block text-sm font-medium text-gray-700 mb-1">Nama Penulis</label>
                <input type="text" id="author" name="author" value="{{ old('author') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('author') border-red-500 @enderror"
                       placeholder="Tuliskan nama Anda atau nama pena" required>
                @error('author')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konten Artikel (Implementasi Trix Editor) --}}
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
                <input id="content" type="hidden" name="content" value="{{ old('content') }}">
                <trix-editor input="content" class="trix-content"></trix-editor>
                @error('content')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Tag --}}
            <div class="mb-5">
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tag (Opsional)</label>
                <input list="tags-list" name="tags" id="tags" value="{{ old('tags') }}"
                    placeholder="Contoh: Berita, Artikel Ilmiah"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('tags') border-red-500 @enderror">
                <datalist id="tags-list">
                    @if(isset($tags))
                        @foreach($tags as $tag)
                            <option value="{{ $tag }}">
                        @endforeach
                    @endif
                </datalist>
                @error('tags')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Upload Thumbnail --}}
            <div class="mb-6">
                <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-2">Thumbnail (Opsional)</label>
                <input id="thumbnail" name="thumbnail" type="file" 
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                @error('thumbnail')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                <a href="{{ route('articles.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                <button type="submit"
                        class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('trix-initialize', function(event) {
        const toolbar = event.target.toolbarElement;
        const fileTools = toolbar.querySelector('.trix-button-group--file-tools');
        if (fileTools) {
            fileTools.remove();
        }
    });
</script>
@endpush
