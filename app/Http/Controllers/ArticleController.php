<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ArticleController extends Controller
{
    use AuthorizesRequests;
    // Tampilkan daftar artikel untuk publik
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            // Admin bisa melihat semua artikel
            $articles = Article::latest()->paginate(10);
        } elseif ($user && $user->role === 'editor') {
            // Editor hanya melihat artikelnya sendiri
            $articles = Article::where('user_id', $user->id)->latest()->paginate(10);
        } else {
            // Publik hanya melihat artikel yang sudah disetujui
            $articles = Article::where('status', 'approved')->latest()->paginate(10);
        }

        return view('articles.index', compact('articles'));
    }

    // Tampilkan detail artikel
    public function show(Article $article)
    {
        if (
            $article->status !== 'approved' &&
            (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'editor']))
        ) {
            abort(404);
        }

        // Hitung visitor berdasarkan IP
        $ip = request()->ip();
        $key = 'article_viewed_' . $article->id . '_' . $ip;

        if (!cache()->has($key)) {
            $article->increment('visit_count');
            cache()->put($key, true, now()->addHours(6));
        }

        return view('articles.show', compact('article'));
    }

    // Form tambah artikel
    public function create()
    {
        return view('articles.create');
    }

    // Simpan artikel
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|max:255',
            'author'    => 'required|max:255',
            'content'   => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('thumbnails'), $filename);
            $data['thumbnail'] = 'thumbnails/' . $filename;
        }

        $data['user_id'] = Auth::id();
        $data['status'] = Auth::user()->role === 'admin' ? 'approved' : 'pending';

        Article::create($data);

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil disimpan dan menunggu persetujuan.');
    }

    // Form edit
    public function edit(Article $article)
    {
        $this->authorize('update', $article);
        return view('articles.edit', compact('article'));
    }

    // Update artikel
    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'title'     => 'required|max:255',
            'author'    => 'required|max:255',
            'content'   => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        if ($request->hasFile('thumbnail')) {
            // Hapus thumbnail lama
            if ($article->thumbnail && file_exists(public_path($article->thumbnail))) {
                unlink(public_path($article->thumbnail));
            }

            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('thumbnails'), $filename);
            $data['thumbnail'] = 'thumbnails/' . $filename;
        }

        $article->update($data);

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    // Hapus artikel
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        if ($article->thumbnail && file_exists(public_path($article->thumbnail))) {
            unlink(public_path($article->thumbnail));
        }

        $article->delete();

        return back()->with('success', 'Artikel berhasil dihapus.');
    }

    // Admin update status artikel
    public function updateStatus(Request $request, Article $article)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision',
        ]);

        $article->status = $request->status;
        $article->save();

        return back()->with('success', 'Status artikel diperbarui.');
    }
}