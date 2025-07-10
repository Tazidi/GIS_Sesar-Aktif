@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="px-4 sm:px-6 lg:px-8">

        {{-- Judul Halaman dan Tombol Create --}}
        <div class="mb-8 border-b border-gray-300 flex justify-between items-center">
            <h2 class="text-3xl font-bold inline-block pb-2 border-b-4 border-red-600">Semua Artikel</h2>

            @auth
                @if (auth()->user()->role === 'editor' || auth()->user()->role === 'admin')
                    <a href="{{ route('articles.create') }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                        + Buat Artikel
                    </a>
                @endif
            @endauth
        </div>

        {{-- Wadah Artikel --}}
        <div class="space-y-8">
            @forelse ($articles as $article)
                <div class="flex flex-col md:flex-row bg-white shadow-md overflow-hidden h-64 rounded-lg">
                    @if($article->thumbnail)
                        <div class="md:w-1/3 h-64 overflow-hidden">
                            <a href="{{ route('articles.show', $article) }}">
                                <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}"
                                    alt="Thumbnail for {{ $article->title }}"
                                    class="w-full h-full object-cover transition-opacity duration-200 hover:opacity-80">
                            </a>
                        </div>
                    @endif

                    <div class="p-6 flex flex-col justify-between {{ $article->thumbnail ? 'md:w-2/3' : 'w-full' }}">
                        <div>
                            <h3 class="font-bold text-2xl mb-2">
                                <a href="{{ route('articles.show', $article) }}" class="hover:text-red-600 transition-colors">
                                    {{ $article->title }}
                                </a>
                            </h3>
                            <p class="text-gray-600 text-sm mb-4">
                                {{ Str::limit(strip_tags($article->content), 200) }}
                            </p>
                        </div>

                        <div class="text-xs text-gray-500 mt-4 flex items-center justify-between">
                            <span>Oleh {{ $article->author ?? 'N/A' }} • {{ $article->created_at->format('d M Y') }}</span>
                            <a href="{{ route('articles.show', $article) }}"
                            class="font-semibold text-indigo-600 hover:text-indigo-900 text-sm">
                                Baca Selengkapnya →
                            </a>
                        </div>

                        {{-- Tombol CRUD dan Status --}}
                        @auth
                            @php
                                $user = auth()->user();
                                $isOwner = $user->id === $article->user_id;
                                $isEditor = $user->role === 'editor';
                                $isAdmin = $user->role === 'admin';
                            @endphp

                            @if ($isAdmin || ($isEditor && $isOwner))
                                <div class="mt-4 flex flex-wrap items-center space-x-2 w-full">
                                    <a href="{{ route('articles.edit', $article) }}"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">
                                        Edit
                                    </a>

                                    <form action="{{ route('articles.destroy', $article) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                                            Delete
                                        </button>
                                    </form>

                                    @if ($isAdmin)
                                        <form method="POST" action="{{ route('articles.updateStatus', $article) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status"
                                                    onchange="this.form.submit()"
                                                    class="ml-4 text-sm border-gray-300 rounded px-3 py-2 bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-red-500 min-w-[120px]">
                                                @foreach (['pending', 'approved', 'rejected', 'revision'] as $status)
                                                    <option value="{{ $status }}" {{ $article->status === $status ? 'selected' : '' }}>
                                                        {{ ucfirst($status) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @elseif ($isEditor && $isOwner)
                                        <span class="ml-auto inline-block bg-gray-200 text-xs text-gray-700 px-3 py-1 rounded">
                                            Status: {{ ucfirst($article->status) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-md p-12 text-center text-gray-500">
                    <p>Belum ada artikel yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $articles->links() }}
        </div>

    </div>
</div>
@endsection