@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Daftar Layer</h1>
        <a href="{{ route('layers.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Tambah Layer</a>

        <table class="w-full mt-4 border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">Nama</th>
                    <th class="border p-2">Deskripsi</th>
                    <th class="border p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($layers as $layer)
                    <tr>
                        <td class="border p-2">{{ $layer->nama_layer }}</td>
                        <td class="border p-2">{{ $layer->deskripsi ?? '-' }}</td>
                        <td class="border p-2">
                            <a href="{{ route('layers.edit', $layer) }}" class="text-blue-500">Edit</a> |
                            <form action="{{ route('layers.destroy', $layer) }}" method="POST" class="inline"
                                onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
