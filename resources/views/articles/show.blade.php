@extends('layouts.app')
@section('content')
<h1>{{ $article->title }}</h1>
<div>{!! nl2br(e($article->content)) !!}</div>
<p>Status: <strong>{{ $article->status }}</strong></p>
@endsection
