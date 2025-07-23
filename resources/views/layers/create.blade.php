@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4"></h1>
        @include('layers.form', ['layer' => new \App\Models\Layer])
    </div>
@endsection
