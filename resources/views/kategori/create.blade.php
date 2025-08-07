@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">
            Buat Kategori Baru
        </h1>
        @include('kategori.form', ['kategori' => new \App\Models\Kategori])
    </div>
@endsection
