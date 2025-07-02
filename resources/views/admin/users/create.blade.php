@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-6">
        <h1 class="text-xl font-bold mb-4">Tambah Pengguna</h1>
        @include('admin.users.form', ['user' => new \App\Models\User])
    </div>
@endsection
