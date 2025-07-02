@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-4">Daftar Peta</h1>
        <a href="{{ route('maps.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Tambah Peta</a>

        <table class="table-auto w-full mt-4 border">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>File</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($maps as $map)
                    <tr>
                        <td>{{ $map->name }}</td>
                        <td><a href="{{ asset('storage/' . $map->file_path) }}" target="_blank">Lihat File</a></td>
                        <td>
                            <a href="{{ route('maps.show', $map) }}">Lihat</a> |
                            <a href="{{ route('maps.edit', $map) }}">Edit</a> |
                            <form action="{{ route('maps.destroy', $map) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
