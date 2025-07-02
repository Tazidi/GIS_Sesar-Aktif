@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-4">Tambah Peta</h1>
        @include('maps.form', ['map' => new \App\Models\Map])
    </div>
@endsection
