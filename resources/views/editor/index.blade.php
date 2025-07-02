@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-6">Dashboard Editor</h1>

    <ul class="list-disc pl-6 space-y-2">
        <li><a href="{{ route('articles.index') }}" class="text-blue-600 hover:underline">Kelola Artikel Saya</a></li>
    </ul>
</div>
@endsection
