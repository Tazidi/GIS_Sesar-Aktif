<form action="{{ $map->exists ? route('maps.update', $map) : route('maps.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if ($map->exists)
        @method('PUT')
    @endif

    <div class="mb-4">
        <label class="block font-medium">Nama Peta</label>
        <input type="text" name="title" class="w-full border rounded px-2 py-1"
               value="{{ old('title', $map->title) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Deskripsi Peta</label>
        <textarea name="description" class="w-full border rounded px-2 py-1" rows="4">{{ old('description', $map->description) }}</textarea>
    </div>

    <div class="mb-4">
        <label class="block font-medium">File Peta (GeoJSON/Shape/CSV)</label>
        <input type="file" name="file" class="w-full border rounded px-2 py-1" {{ $map->exists ? '' : 'required' }}>
    </div>

    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">
        {{ $map->exists ? 'Perbarui' : 'Simpan' }}
    </button>
</form>
