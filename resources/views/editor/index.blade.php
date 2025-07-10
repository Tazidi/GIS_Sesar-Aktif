@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard Editor</h1>

    <div class="grid grid-cols-1 gap-6">
        <a href="{{ route('articles.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition">
            <h2 class="text-lg font-semibold text-blue-600 mb-1"><i class="fas fa-pen-nib mr-2"></i>Kelola Artikel Saya</h2>
            <p class="text-gray-500 text-sm">Lihat dan kelola artikel yang Anda buat.</p>
        </a>
    </div>
</div>
@endsection
