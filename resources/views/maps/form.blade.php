@extends('layouts.app')

@section('title', $map->exists ? 'Edit Peta' : 'Tambah Peta Baru')

@section('content')
<div class="container mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-4 text-gray-800">{{ $map->exists ? 'Edit Peta' : 'Tambah Peta' }}</h1>

    <form action="{{ $map->exists ? route('maps.update', $map) : route('maps.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($map->exists)
        @method('PUT')
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Judul Map</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('name', $map->name) }}" required>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" rows="4">{{ old('description', $map->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Pilih Layer (Opsional)</label>
                <p class="text-sm text-gray-500 mb-2">Pilih satu atau lebih layer yang akan ditampilkan pada peta ini.</p>
                <div class="mt-2 space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-4">
                    @forelse ($layers as $layer)
                        <label class="flex items-center mb-2">
                            <input type="checkbox" name="layer_ids[]" value="{{ $layer->id }}"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                @if(in_array($layer->id, old('layer_ids', $map->layers->pluck('id')->toArray()))) checked @endif>
                            <span class="ml-2 text-gray-700">
                                {{ $layer->nama_layer }}
                                @if($layer->deskripsi) - <span class="text-gray-500 text-sm">{{ $layer->deskripsi }}</span> @endif
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500">Tidak ada layer tersedia. <a href="{{ route('layers.create') }}" class="text-blue-600 hover:underline">Buat layer baru</a></p>
                    @endforelse
                </div>
            </div>
            
            {{-- BAGIAN KATEGORI DIHAPUS DARI SINI --}}

            <div>
                <label for="image_path" class="block text-sm font-medium text-gray-700">Upload Gambar (Thumbnail)</label>
                <input type="file" name="image_path" id="image_path" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                
                <div id="image-preview-container" class="mt-2">
                    <img id="image-preview" src="#" alt="Image preview" class="w-full rounded-md shadow-sm" style="display: none;"/>
                    @if ($map->exists && $map->image_path)
                        <img id="existing-image" src="{{ asset('map_images/' . $map->image_path) }}" alt="Gambar lama" class="w-full rounded-md shadow-sm">
                    @endif
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('maps.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Batal
            </a>
            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ $map->exists ? 'Update Peta' : 'Simpan Peta' }}
            </button>
        </div>
    </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    document.getElementById('image_path').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('image-preview');
                const existing = document.getElementById('existing-image');
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (existing) existing.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection