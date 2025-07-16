@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-3xl font-bold">Manajemen Galeri</h2>
            <a href="{{ route('gallery.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                + Tambah Gambar
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($images->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($images as $image)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden relative">
                        <img class="w-full h-48 object-cover" src="{{ asset('gallery/' . $image->image_path) }}" alt="{{ $image->title }}">
                        <div class="p-4">
                            <h3 class="font-bold text-lg">{{ $image->title }}</h3>
                            <p class="text-sm text-gray-500 truncate">{{ $image->description }}</p>
                            <div class="mt-4 flex justify-between text-sm">
                                <a href="{{ route('gallery.edit', $image->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('gallery.destroy', $image->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus gambar ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $images->links() }}
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <p>Tidak ada gambar yang tersedia.</p>
            </div>
        @endif
    </div>
</div>
@endsection