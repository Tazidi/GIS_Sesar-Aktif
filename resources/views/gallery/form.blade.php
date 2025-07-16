@csrf

<div>
    <label for="title" class="block text-sm font-medium text-gray-700">Judul Gambar</label>
    <input type="text" name="title" id="title" value="{{ old('title', $image->title ?? '') }}" required
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror">
    @error('title')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="image" class="block text-sm font-medium text-gray-700">File Gambar</label>
    <input type="file" name="image" id="image"
        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 @error('image') border-red-500 @enderror">
    @if(isset($image) && $image->image_path)
        <p class="text-sm text-gray-400 mt-1">Gambar saat ini: <span class="underline">{{ $image->image_path }}</span></p>
    @endif
    @error('image')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi (Opsional)</label>
    <textarea name="description" id="description" rows="3"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $image->description ?? '') }}</textarea>
    @error('description')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <button type="submit" class="inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
        {{ $submit ?? 'Simpan' }}
    </button>
</div>
