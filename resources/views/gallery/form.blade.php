@csrf

{{-- Judul Gambar --}}
<div class="mb-5">
    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
        Judul Gambar <span class="text-red-500">*</span>
    </label>
    <input type="text" name="title" id="title" value="{{ old('title', $image->title ?? '') }}" required
           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror">
    @error('title')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Dropdown Kategori --}}
<div class="mb-5">
    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
        Kategori <span class="text-red-500">*</span>
    </label>
    <select name="category" id="category" required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('category') border-red-500 @enderror">
        @php
            $categories = ['Sesar Aktif', 'Peta Geologi', 'Mitigasi Bencana', 'Studi Lapangan', 'Lainnya'];
            $currentCategory = old('category', $image->category ?? '');
        @endphp
        <option value="" disabled {{ $currentCategory ? '' : 'selected' }}>Pilih Kategori...</option>
        @foreach ($categories as $category)
            <option value="{{ $category }}" {{ $currentCategory == $category ? 'selected' : '' }}>
                {{ $category }}
            </option>
        @endforeach
    </select>
    @error('category')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Deskripsi --}}
<div class="mb-5">
    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
        Deskripsi (Opsional)
    </label>
    <textarea name="description" id="description" rows="4"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
              placeholder="Jelaskan sedikit tentang gambar ini...">{{ old('description', $image->description ?? '') }}</textarea>
    @error('description')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Upload Gambar --}}
<div class="mb-6">
    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
        File Gambar <span class="text-red-500">*</span>
    </label>
    <input type="file" name="image" id="image"
           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 @error('image') border-red-500 @enderror">
    
    {{-- Tampilkan pratinjau jika sedang mengedit dan gambar sudah ada --}}
    @if(isset($image) && $image->image_path)
        <div class="mt-4">
            <p class="text-sm text-gray-500 mb-2">Gambar saat ini:</p>
            {{-- PERBAIKAN: Gunakan asset('storage/' . $path) untuk memanggil gambar --}}
            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Pratinjau gambar" class="h-32 w-auto rounded-md shadow-md object-cover">
            <p class="text-xs text-gray-500 mt-2">Unggah file baru untuk mengganti gambar ini.</p>
        </div>
    @endif

    @error('image')
        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
    @enderror
</div>

{{-- Tombol Aksi --}}
<div class="flex items-center justify-end space-x-4 pt-5 mt-6 border-t border-gray-200">
    <a href="{{ route('gallery.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
        Batal
    </a>
    <button type="submit" class="inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
        {{ $submit ?? 'Simpan' }}
    </button>
</div>
