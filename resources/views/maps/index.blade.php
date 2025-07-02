@extends('layouts.app')
@section('content')
<h1>Data Peta</h1>
<a href="{{ route('maps.create') }}">+ Tambah Peta</a>
@foreach($maps as $map)
    <div>
        <strong>{{ $map->title }}</strong>
        <iframe src="{{ asset('storage/' . $map->file_path) }}" width="100%" height="300"></iframe>
        <form method="POST" action="{{ route('maps.destroy', $map) }}">
            @csrf @method('DELETE')
            <button>Hapus</button>
        </form>
    </div>
@endforeach
@endsection
