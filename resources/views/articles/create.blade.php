@extends('layouts.app')

@section('content')
<h1>Buat Artikel</h1>

<form method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data">
    @csrf

    <input name="title" value="{{ old('title') }}" placeholder="Judul" class="block mb-2 w-full border px-2 py-1">

    <textarea name="content" placeholder="Konten" class="block mb-2 w-full border px-2 py-1" rows="6">{{ old('content') }}</textarea>

    <label class="block mb-1">Thumbnail (opsional)</label>
    <input type="file" name="thumbnail" class="mb-4">

    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Simpan</button>
</form>
@endsection
