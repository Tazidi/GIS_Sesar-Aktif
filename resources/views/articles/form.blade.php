<form method="POST" action="{{ isset($article) ? route('articles.update', $article) : route('articles.store') }}">
    @csrf
    @if(isset($article))
        @method('PUT')
    @endif
    <input name="title" value="{{ old('title', $article->title ?? '') }}" placeholder="Judul">
    <textarea name="content">{{ old('content', $article->content ?? '') }}</textarea>
    <button type="submit">Simpan</button>
</form>
