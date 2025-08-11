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

            {{-- Konten Artikel (CKEditor 5) --}}
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
                <textarea id="editor" name="content">{{ old('content') }}</textarea>
                @error('content')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Tag --}}
            <div class="mb-5">
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tag (Opsional)</label>

                {{-- Daftar Tag yang Sudah Ada --}}
                @if(isset($tags) && count($tags) > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($tags as $tag)
                            <label class="tag-option inline-flex items-center px-3 py-1 border border-gray-300 rounded-full text-sm cursor-pointer hover:bg-gray-200">
                                <input type="radio" name="tags" value="{{ $tag }}" class="hidden">
                                <span>{{ $tag }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm mb-3">Belum ada tag yang tersedia.</p>
                @endif

                {{-- Input Tag Baru --}}
                <input type="text" id="tags_input" name="tags" value="{{ old('tags') }}"
                    placeholder="Atau tulis tag baru di sini..."
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('tags') border-red-500 @enderror">
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

{{-- Script Tag Pilihan --}}
<script>
    const tagOptions = document.querySelectorAll('.tag-option');
    tagOptions.forEach(option => {
        option.addEventListener('click', function() {
            tagOptions.forEach(opt => {
                opt.classList.remove('bg-gray-500/50');
                opt.classList.add('border-gray-300');
            });
            this.classList.add('bg-gray-500/50');
            this.classList.remove('border-gray-300');

            const inputVal = this.querySelector('input').value;
            document.getElementById('tags_input').value = inputVal;
        });
    });
</script>
@endpush
