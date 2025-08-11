@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Tombol Kembali ke Dashboard --}}
        <div class="mb-5">
        <a href="{{ route('dashboard') }}" 
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
            Kembali ke Dashboard
        </a>
    </div>
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

        @php
            $user = auth()->user();
            $isAdmin = $user && $user->role === 'admin';
            $isEditor = $user && $user->role === 'editor';
        @endphp

        @if($images->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($images as $image)
                    {{-- Batasi akses jika editor bukan pemilik --}}
                    @if ($isAdmin || ($isEditor && $image->user_id === $user->id))
                        <div class="bg-white rounded-lg shadow-md overflow-hidden relative">
                            {{-- PERUBAHAN: Menampilkan 'main_image' dan menambahkan gambar placeholder jika kosong --}}
                            <img class="w-full h-48 object-cover" 
                                src="{{ $image->main_image ? asset('gallery/' . $image->main_image) : 'https://via.placeholder.com/400x300.png?text=No+Image' }}" 
                                alt="{{ $image->title }}">

                            @if ($image->extra_images)
                                @php
                                    $extraImages = is_array($image->extra_images) ? $image->extra_images : json_decode($image->extra_images, true);
                                @endphp
                                @if (!empty($extraImages))
                                    <div class="absolute top-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                                        +{{ count($extraImages) }} foto
                                    </div>
                                @endif
                            @endif
                                 
                            <div class="p-4">
                                <h3 class="font-bold text-lg">{{ $image->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $image->category }}</p>
                                <p class="text-sm text-gray-500 truncate mt-1">{{ $image->description }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Diposting: {{ $image->created_at->format('d M Y H:i') }}
                                </p>

                                @if ($image->approver)
                                    <p class="text-xs text-green-600">
                                        Disetujui oleh: {{ $image->approver->name }}
                                    </p>
                                @endif

                                @if ($image->editor && $image->updated_at != $image->created_at)
                                    <p class="text-xs text-blue-600">
                                        Terakhir diedit oleh: {{ $image->editor->name }} pada {{ $image->updated_at->format('d M Y H:i') }}
                                    </p>
                                @endif

                                <div class="mt-4 flex justify-between text-sm">
                                    <a href="{{ route('gallery.edit', $image->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                    <form action="{{ route('gallery.destroy', $image->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus gambar ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </div>

                                {{-- Tombol update status --}}
                                @if ($isAdmin)
                                    <form method="POST" action="{{ route('gallery.updateStatus', $image) }}" class="mt-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 rounded px-2 py-1">
                                            @foreach (['pending', 'approved', 'rejected','Needs Revision'] as $status)
                                                <option value="{{ $status }}" {{ $image->status === $status ? 'selected' : '' }}>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @elseif ($isEditor)
                                    <div class="text-xs mt-2 bg-gray-200 px-2 py-1 rounded text-gray-600">
                                        Status: {{ ucfirst($image->status) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-6">
                {{ $images->links() }}
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <p>Tidak ada gambar yang tersedia.</p>
                <p class="mt-2">Silakan <a href="{{ route('gallery.create') }}" class="text-indigo-600 hover:underline">tambahkan gambar baru</a>.</p>
            </div>
        @endif
    </div>
</div>
@endsection