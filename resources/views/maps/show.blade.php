@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">{{ $map->name }}</h1>
    <p><strong>File:</strong> <a href="{{ asset($map->file_path) }}" target="_blank">Lihat File</a></p>
</div>
@endsection
