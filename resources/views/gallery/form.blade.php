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

{{-- Gambar Utama --}}
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Utama</label>
    <div class="w-40 h-40 border-2 border-dashed rounded-md flex items-center justify-center relative overflow-hidden">
        <input type="file" name="main_image" id="main_image" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
        <img 
            id="preview_main_image" 
            class="w-full h-full object-cover {{ isset($image) && $image->main_image ? '' : 'hidden' }}"
            src="{{ isset($image) && $image->main_image ? asset('gallery/' . $image->main_image) : '' }}"
        >
        <span 
            id="placeholder_main_image" 
            class="text-gray-400 {{ isset($image) && $image->main_image ? 'hidden' : '' }}"
        >+</span>
    </div>
</div>

{{-- Gambar Tambahan --}}
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Tambahan</label>
    <div class="grid grid-cols-5 gap-3">
        @php
            if (isset($image) && $image->extra_images) {
                $extraImages = is_array($image->extra_images) 
                    ? $image->extra_images 
                    : json_decode($image->extra_images, true);
            } else {
                $extraImages = [];
            }
        @endphp
        @for ($i = 0; $i < 9; $i++)
            <div class="w-24 h-24 border-2 border-dashed rounded-md flex items-center justify-center relative overflow-hidden">
                <input type="file" name="extra_images[]" id="extra_image_{{ $i }}" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                <img 
                    id="preview_extra_image_{{ $i }}" 
                    class="w-full h-full object-cover {{ isset($extraImages[$i]) ? '' : 'hidden' }}"
                    src="{{ isset($extraImages[$i]) ? asset('gallery/' . $extraImages[$i]) : '' }}"
                >
                <span 
                    id="placeholder_extra_image_{{ $i }}" 
                    class="text-gray-400 {{ isset($extraImages[$i]) ? 'hidden' : '' }}"
                >+</span>
            </div>
        @endfor
    </div>
</div>

<script>
    // Fungsi preview upload
    function previewImage(input, previewId, placeholderId) {
        let file = input.files[0];
        let preview = document.getElementById(previewId);
        let placeholder = document.getElementById(placeholderId);
        if (file) {
            let reader = new FileReader();
            reader.onload = function(ev) {
                preview.src = ev.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    // Main image event
    document.getElementById('main_image').addEventListener('change', function() {
        previewImage(this, 'preview_main_image', 'placeholder_main_image');
    });

    // Extra images event
    for (let i = 0; i < 9; i++) {
        document.getElementById(`extra_image_${i}`).addEventListener('change', function() {
            previewImage(this, `preview_extra_image_${i}`, `placeholder_extra_image_${i}`);
        });
    }
</script>

{{-- Tombol Aksi --}}
<div class="flex items-center justify-end space-x-4 pt-5 mt-6 border-t border-gray-200">
    <a href="{{ route('gallery.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
        Batal
    </a>
    <button type="submit" class="inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
        {{ $submit ?? 'Simpan' }}
    </button>
</div>
