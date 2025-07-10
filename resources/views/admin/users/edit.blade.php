@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Pengguna</h1>
    @include('admin.users.form', ['user' => $user])
</div>
@endsection
