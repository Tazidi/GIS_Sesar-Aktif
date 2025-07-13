@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard Admin</h1>

    {{-- Semua kartu sekarang ada di dalam satu grid yang sama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        {{-- Kartu Kelola Galeri --}}
        <a href="{{ route('admin.gallery.create') }}" class="p-6 bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition">
            <h2 class="text-lg font-semibold text-green-600 mb-1"><i class="fas fa-images mr-2"></i>Kelola Galeri</h2>
            <p class="text-gray-500 text-sm">Tambah atau hapus gambar dari galeri publik.</p>
        </a>

        {{-- Kartu Kelola Artikel --}}
        <a href="{{ route('articles.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition">
            <h2 class="text-lg font-semibold text-blue-600 mb-1"><i class="fas fa-newspaper mr-2"></i>Kelola Artikel</h2>
            <p class="text-gray-500 text-sm">Lihat, edit, dan moderasi semua artikel pengguna.</p>
        </a>

        {{-- Kartu Kelola Peta --}}
        <a href="{{ route('maps.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition">
            <h2 class="text-lg font-semibold text-green-600 mb-1"><i class="fas fa-map mr-2"></i>Kelola Peta</h2>
            <p class="text-gray-500 text-sm">Manajemen data spasial dan peta interaktif.</p>
        </a>

        {{-- Kartu Kelola User --}}
        <a href="{{ route('users.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition">
            <h2 class="text-lg font-semibold text-purple-600 mb-1"><i class="fas fa-users mr-2"></i>Kelola User</h2>
            <p class="text-gray-500 text-sm">Atur role, akses, dan informasi pengguna.</p>
        </a>

        {{-- Kartu Kelola Layer --}}
        <a href="{{ route('layers.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition">
            <h2 class="text-lg font-semibold text-yellow-600 mb-1"><i class="fas fa-layer-group mr-2"></i>Kelola Layer</h2>
            <p class="text-gray-500 text-sm">Tambahkan dan atur layer untuk visualisasi peta.</p>
        </a>

    </div>
</div>
@endsection