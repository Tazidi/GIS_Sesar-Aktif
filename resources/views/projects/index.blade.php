@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Header Halaman --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Daftar Proyek Survey
        </h1>
        {{-- Tombol ini mengarah ke form pembuatan proyek baru --}}
        <a href="{{ route('projects.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Proyek Baru
        </a>
    </div>

    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
            <p class="font-bold">Sukses</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- Container Tabel Proyek --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-center">No</th>
                        @if(auth()->user()->role === 'admin')
                            <th scope="col" class="px-6 py-3">Surveyor</th>
                        @endif
                        <th scope="col" class="px-6 py-3">Nama Proyek</th>
                        <th scope="col" class="px-6 py-3">Deskripsi</th>
                        <th scope="col" class="px-6 py-3 text-center">Jumlah Lokasi</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    @forelse ($projects as $project)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-center">{{ $loop->iteration }}</td>
                            @if(auth()->user()->role === 'admin')
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $project->user->name ?? '-' }}</td>
                            @endif
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $project->name }}</td>
                            <td class="px-6 py-4 text-gray-500 max-w-sm truncate" title="{{ $project->description }}">
                                {{ $project->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{-- Pastikan Anda menggunakan withCount('surveyLocations') di controller --}}
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">
                                    {{ $project->survey_locations_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap space-x-2">
                                {{-- Tombol untuk melihat detail proyek (peta dan daftar lokasi) --}}
                                <a href="{{ route('projects.show', $project) }}" class="px-3 py-1 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Lihat</a>
                                <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Edit</a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus proyek ini beserta semua lokasinya?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? 6 : 5 }}" class="text-center py-16 text-gray-500">
                                <h3 class="text-lg font-semibold">Belum Ada Proyek</h3>
                                <p class="text-sm mt-1">Silakan klik tombol "Tambah Proyek Baru" untuk memulai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
