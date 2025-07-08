<form action="{{ $layer->exists ? route('layers.update', $layer) : route('layers.store') }}" method="POST">
    @csrf
    @if ($layer->exists)
        @method('PUT')
    @endif

    <div class="mb-4">
        <label class="block font-medium">Nama Layer</label>
        <input type="text" name="nama_layer" class="w-full border px-2 py-1" value="{{ old('nama_layer', $layer->nama_layer) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Deskripsi</label>
        <textarea name="deskripsi" class="w-full border px-2 py-1">{{ old('deskripsi', $layer->deskripsi) }}</textarea>
    </div>

    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
        {{ $layer->exists ? 'Update' : 'Simpan' }}
    </button>
</form>
