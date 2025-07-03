@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- KARTU STATISTIK --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <h3 class="text-lg font-medium text-gray-500">Jumlah Artikel</h3>
                    {{-- INI PERBAIKANNYA: Menggunakan total() bukan count() --}}
                    <p class="mt-1 text-4xl font-semibold">{{ $articles->total() }}</p>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <h3 class="text-lg font-medium text-gray-500">Gatau jumlah apa lagi yg ini</h3>
                    <p class="mt-1 text-4xl font-semibold">{{ count($maps) }}</p>
                </div>
            </div>
        </div>

        {{-- DAFTAR ARTIKEL --}}
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-semibold text-gray-700">Daftar Artikel</h3>
                <form method="GET" class="flex items-center">
                    <input type="text" name="search" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Cari artikel..." value="{{ request('search') }}">
                    <button type="submit" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cari
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($articles as $article)
                    <div class="relative bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex flex-col h-80 group text-white">
                        <img src="{{ $article->thumbnail }}" alt="Thumbnail for {{ $article->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-110 opacity-50 group-hover:opacity-40">
                        <div class="relative p-6 flex flex-col flex-grow justify-end z-10">
                            <h4 class="font-bold text-lg">{{ $article->title }}</h4>
                            <p class="text-xs text-gray-300 mt-1">Oleh {{ $article->user->name ?? 'N/A' }} &bull; {{ $article->created_at->format('d M Y') }}</p>
                            <a href="{{ route('article.show', $article->id) }}" class="mt-4 font-semibold self-start hover:underline">
                                Baca Selengkapnya &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 text-center">
                            Tidak ada artikel yang ditemukan.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $articles->links() }}
            </div>

        </div>
    </div>
</div>
@endsection