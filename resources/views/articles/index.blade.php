@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Daftar Artikel Saya</h1>

<a href="{{ route('articles.create') }}" class="bg-blue-500 text-white px-3 py-1 rounded inline-block mb-4">
    + Tambah Artikel
</a>

@foreach($articles as $a)
    <div class="border p-4 mb-4 rounded shadow-sm bg-white flex gap-4">
        {{-- Thumbnail --}}
        @if($a->thumbnail)
            <img src="{{ asset($a->thumbnail) }}" alt="Thumbnail" class="w-32 h-32 object-cover">
        @endif

        <div class="flex-grow">
            <h3 class="text-xl font-semibold">{{ $a->title }}</h3>
            <p class="text-sm text-gray-500">Status: 
                @auth
                    @if(auth()->user()->role === 'admin')
                        <form method="POST" action="{{ route('articles.updateStatus', $a) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="text-sm">
                                <option value="pending" {{ $a->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $a->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $a->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="revision" {{ $a->status == 'revision' ? 'selected' : '' }}>Revision</option>
                            </select>
                        </form>
                    @else
                        <span class="inline-block bg-gray-200 px-2 py-1 rounded text-sm">{{ ucfirst($a->status) }}</span>
                    @endif
                @endauth
            </p>

            <div class="space-x-2 mt-2">
                <a href="{{ route('articles.show', $a) }}" class="text-blue-600 underline">Preview</a>
                <a href="{{ route('articles.edit', $a) }}" class="text-yellow-600 underline">Edit</a>
                <form method="POST" action="{{ route('articles.destroy', $a) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('Hapus artikel ini?')" class="text-red-600 underline">Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endforeach

@if($articles->isEmpty())
    <p class="text-gray-500">Belum ada artikel.</p>
@endif
@endsection
