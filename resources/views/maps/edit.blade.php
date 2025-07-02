@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-4">Edit Peta</h1>
        @include('maps.form', ['map' => $map])
    </div>
@endsection