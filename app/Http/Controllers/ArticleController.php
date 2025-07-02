<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        if (Auth::user()->role === 'admin') {
            $articles = Article::latest()->get();
        } else {
            $articles = Article::where('user_id', Auth::id())->latest()->get();
        }

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $data['user_id'] = Auth::id();
        Article::create($data);

        return redirect()->route('articles.index')->with('success', 'Artikel disimpan!');
    }

    public function edit(Article $article)
    {
        $this->authorize('update', $article); // optional
        return view('articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $article->update($data);
        return redirect()->route('articles.index')->with('success', 'Artikel diperbarui!');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return back()->with('success', 'Artikel dihapus.');
    }

    public function show(Article $article)
    {
        return view('articles.show', compact('article'));
    }

    public function updateStatus(Request $request, Article $article)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision',
        ]);

        $article->status = $request->status;
        $article->save();

        return redirect()->back()->with('success', 'Status artikel diperbarui.');
    }
}
