@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Tombol ke halaman visualisasi peta interaktif --}}
    <a href="{{ route('visualisasi.index') }}" class="btn btn-primary mb-4">
        🌍 Lihat Visualisasi Peta Interaktif
    </a>

    {{-- Daftar Artikel --}}
    <h1>Daftar Artikel</h1>
    <form method="GET">
        <input type="text" name="search" placeholder="Cari artikel..." value="{{ request('search') }}" />
    </form>

    @foreach($articles as $article)
        <a href="{{ route('article.show', $article->id) }}">
            <h2>{{ $article->title }}</h2>
        </a>
    @endforeach
</div>
@endsection