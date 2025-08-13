@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    <div class="mb-6 border-b border-gray-200 pb-4">
        <h1 class="text-3xl font-bold text-gray-900">Buat Artikel Baru</h1>
        <p class="mt-1 text-sm text-gray-600">Isi semua kolom yang diperlukan untuk mempublikasikan artikel.</p>
    </div>

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
            </div>

            {{-- Penulis --}}
            <div class="mb-5">
                <label for="author" class="block text-sm font-medium text-gray-700 mb-1">Nama Penulis</label>
                <input type="text" id="author" name="author" value="{{ old('author') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('author') border-red-500 @enderror"
                       placeholder="Tuliskan nama Anda atau nama pena" required>
            </div>

            {{-- Konten Artikel (CKEditor 5) --}}
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
                <textarea id="editor" name="content">{{ old('content') }}</textarea>
            </div>

            {{-- Input Kategori --}}
            <div class="mb-5">
                <label for="category_input" class="block text-sm font-medium text-gray-700 mb-1">Kategori (Opsional)</label>

                {{-- Daftar Kategori yang Sudah Ada --}}
                @if(isset($categories) && count($categories) > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($categories as $kategori)
                            {{-- Label untuk setiap opsi kategori --}}
                            <label class="category-option inline-flex items-center px-3 py-1 border border-gray-300 rounded-full text-sm cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                {{-- Tombol radio disembunyikan, namanya diubah agar tidak konflik --}}
                                <input type="radio" name="category_radio" value="{{ $kategori }}" class="hidden">
                                <span>{{ $kategori }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm mb-3">Belum ada kategori yang tersedia.</p>
                @endif

                {{-- Input untuk kategori baru atau yang dipilih --}}
                <input type="text" id="category_input" name="category" value="{{ old('category') }}"
                       placeholder="Atau tulis kategori baru di sini..."
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('category')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tags (Hashtag) --}}
            <div class="mb-5">
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tags / Hashtags (Opsional)</label>
                <input type="text" id="tags" name="tags" value="{{ old('tags') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Contoh: teknologi, ai, inovasi (pisahkan dengan koma)">
                <p class="mt-1 text-xs text-gray-500">Pisahkan setiap tag dengan koma.</p>
            </div>

            {{-- Upload Thumbnail --}}
            <div class="mb-6">
                <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-2">Thumbnail (Opsional)</label>
                <input id="thumbnail" name="thumbnail" type="file" 
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
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
{{-- Style untuk kategori yang dipilih --}}
<style>
    .category-selected {
        background-color: #E5E7EB; /* Tailwind's gray-200 */
        border-color: #6B7280; /* Tailwind's gray-500 */
        font-weight: 600;
    }
</style>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi CKEditor
        ClassicEditor
            .create(document.querySelector('#editor'), {
                ckfinder: {
                    uploadUrl: "{{ route('ckeditor.upload').'?_token='.csrf_token() }}"
                }
            })
            .catch(error => {
                console.error('CKEditor Error:', error);
            });

        const categoryRadios = document.querySelectorAll('.category-option input[type="radio"]');
        const categoryInput = document.getElementById('category_input');
        const categoryLabels = document.querySelectorAll('.category-option');

        // Fungsi untuk menangani logika pemilihan kategori
        function handleCategorySelection(selectedValue) {
            // Perbarui input teks dengan nilai yang dipilih
            categoryInput.value = selectedValue;

            // Perbarui tampilan visual untuk setiap label kategori
            categoryLabels.forEach(label => {
                const radio = label.querySelector('input[type="radio"]');
                if (radio.value === selectedValue) {
                    label.classList.add('category-selected');
                } else {
                    label.classList.remove('category-selected');
                }
            });
        }

        // Tambahkan event listener untuk setiap tombol radio kategori
        categoryRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    handleCategorySelection(this.value);
                }
            });
        });

        // Tambahkan event listener untuk input teks
        // Ini akan menghapus seleksi visual jika pengguna mengetik kategori baru
        categoryInput.addEventListener('input', function() {
            const inputValue = this.value.trim();
            let radioIsSelected = false;
            
            categoryRadios.forEach(radio => {
                if (radio.value === inputValue) {
                    radio.checked = true; // Jaga agar radio tetap terpilih jika cocok
                    radioIsSelected = true;
                }
            });

            // Jika nilai input tidak cocok dengan kategori mana pun, hapus gaya visual
            if (!radioIsSelected) {
                 categoryLabels.forEach(label => label.classList.remove('category-selected'));
            } else {
                handleCategorySelection(inputValue);
            }
        });

        // Saat halaman dimuat, periksa apakah ada nilai lama (misalnya, dari validasi error)
        const initialCategory = categoryInput.value;
        if (initialCategory) {
            handleCategorySelection(initialCategory);
            // Juga pastikan radio button yang sesuai dicentang
            categoryRadios.forEach(radio => {
                if(radio.value === initialCategory) {
                    radio.checked = true;
                }
            });
        }
    });
</script>
@endpush
