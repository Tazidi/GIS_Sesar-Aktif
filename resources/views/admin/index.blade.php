@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-6">Dashboard Admin</h1>

    <ul class="list-disc pl-6 space-y-2">
        <li><a href="{{ route('articles.index') }}" class="text-blue-600 hover:underline">Kelola Artikel</a></li>
        <li><a href="{{ route('maps.index') }}" class="text-blue-600 hover:underline">Kelola Peta</a></li>
        <li><a href="{{ route('users.index') }}" class="text-blue-600 hover:underline">Kelola User</a></li>
        <li><a href="{{ route('layers.index') }}" class="text-blue-600 hover:underline">Kelola Layer</a></li>
    </ul>
</div>
@endsection
