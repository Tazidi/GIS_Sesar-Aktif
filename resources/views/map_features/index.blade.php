@extends('layouts.app') {{-- Sesuaikan dengan layout Anda --}}

@section('title', 'Daftar Fitur untuk Peta: ' . $map->name)

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Fitur untuk Peta: <span class="font-normal">{{ $map->name }}</span>
        </h1>
        <a href="{{ route('maps.index') }}" class="text-blue-600 hover:text-blue-800">
            &larr; Kembali ke Daftar Peta
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Properti (Contoh)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Geometri</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($features as $feature)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $feature->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{-- Tampilkan properti 'name' atau properti pertama jika ada --}}
                                {{ $feature->properties['popupInfo'] ?? $feature->properties['Name'] ?? 'Tidak ada nama' }}

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $feature->geometry['type'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($feature->image_path)
                                    <img src="{{ asset($feature->image_path) }}" alt="Feature Image" class="h-12 w-12 object-cover rounded">
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('map-features.edit', $feature) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                Belum ada fitur untuk peta ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $features->links() }} {{-- Tampilkan link pagination --}}
        </div>
    </div>
</div>
@endsection