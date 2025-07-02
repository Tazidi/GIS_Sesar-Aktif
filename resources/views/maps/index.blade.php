@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-4">Daftar Peta</h1>
        <a href="{{ route('maps.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Tambah Peta</a>

        <table class="table-auto w-full mt-4 border">
            <thead>
                <tr>
                    <th class="border px-2 py-1">Nama</th>
                    <th class="border px-2 py-1">Deskripsi</th>
                    <th class="border px-2 py-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($maps as $map)
                    <tr>
                        <td class="border px-2 py-1">{{ $map->title }}</td>
                        <td class="border px-2 py-1">{{ $map->description }}</td>
                        <td class="border px-2 py-1">
                            <a href="{{ route('maps.edit', $map) }}">Edit</a> |
                            <form action="{{ route('maps.destroy', $map) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
