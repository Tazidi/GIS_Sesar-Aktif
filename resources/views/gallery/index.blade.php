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
                            <img class="w-full h-48 object-cover" src="{{ asset('gallery/' . $image->image_path) }}" alt="{{ $image->title }}">
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