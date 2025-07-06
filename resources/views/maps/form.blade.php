<form action="{{ $map->exists ? route('maps.update', $map) : route('maps.store') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @if ($map->exists)
        @method('PUT')
    @endif

    <div class="mb-4">
        <label class="block font-medium">Nama Peta</label>
        <input type="text" name="title" class="w-full border rounded px-2 py-1" value="{{ old('title', $map->title) }}"
            required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Deskripsi Peta</label>
        <textarea name="description" class="w-full border rounded px-2 py-1" rows="4">{{ old('description', $map->description) }}</textarea>
    </div>

    <div class="mb-4">
        <label class="block font-medium">File Peta (GeoJSON/Shape/CSV)</label>
        <input type="file" name="file" class="w-full border rounded px-2 py-1"
            {{ $map->exists ? '' : 'required' }}>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Jenis Layer</label>
        <select name="layer_type" class="w-full border rounded px-2 py-1" required>
            <option value="">-- Pilih --</option>
            <option value="marker" {{ old('layer_type', $map->layer_type) == 'marker' ? 'selected' : '' }}>Marker
            </option>
            <option value="polyline" {{ old('layer_type', $map->layer_type) == 'polyline' ? 'selected' : '' }}>Polyline
            </option>
            <option value="polygon" {{ old('layer_type', $map->layer_type) == 'polygon' ? 'selected' : '' }}>Polygon
            </option>
            <option value="circle" {{ old('layer_type', $map->layer_type) == 'circle' ? 'selected' : '' }}>Circle
            </option>
        </select>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Warna Garis (Stroke)</label>
        <input type="color" name="stroke_color" value="{{ old('stroke_color', $map->stroke_color ?? '#000000') }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Warna Isi (Fill)</label>
        <input type="color" name="fill_color" value="{{ old('fill_color', $map->fill_color ?? '#ff0000') }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Opacity</label>
        <input type="number" step="0.1" max="1" min="0" name="opacity"
            value="{{ old('opacity', $map->opacity ?? 0.8) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Ketebalan Garis</label>
        <input type="number" step="1" min="0" name="weight"
            value="{{ old('weight', $map->weight ?? 2) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Radius (untuk Circle)</label>
        <input type="number" step="1" name="radius" value="{{ old('radius', $map->radius ?? 300) }}">
    </div>

    <div class="mb-4">
        <label class="block font-medium">Custom Icon URL (untuk Marker)</label>
        <input type="text" name="icon_url" class="w-full border rounded px-2 py-1" placeholder="https://..."
            value="{{ old('icon_url', $map->icon_url) }}">
    </div>

    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">
        {{ $map->exists ? 'Perbarui' : 'Simpan' }}
    </button>
</form>
