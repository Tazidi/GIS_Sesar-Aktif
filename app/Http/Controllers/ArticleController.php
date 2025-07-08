<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    // Menampilkan halaman daftar artikel untuk PUBLIK
    public function index()
    {
        $articles = Article::where('status', 'approved')->latest()->paginate(10);
        return view('articles.index', compact('articles'));
    }

    // Menampilkan halaman detail artikel untuk PUBLIK
    public function show(Article $article)
    {
        // Pastikan hanya artikel yang sudah 'approved' yang bisa dilihat publik
        if ($article->status !== 'approved' && (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'editor']))) {
            abort(404);
        }
        return view('articles.show', compact('article'));
    }

    // Menampilkan form untuk membuat artikel baru
    public function create()
    {
        return view('articles.create');
    }

    // Menyimpan artikel baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            // Simpan gambar ke storage/app/public/thumbnails
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $data['thumbnail'] = $path;
        }

        $data['user_id'] = Auth::id();
        $data['status'] = Auth::user()->role === 'admin' ? 'approved' : 'pending'; // Admin langsung approve

        Article::create($data);

        return redirect()->route('home')->with('success', 'Artikel berhasil disimpan dan menunggu persetujuan.'); // Arahkan ke home atau halaman manajemen
    }

    // Menampilkan form untuk mengedit artikel
    public function edit(Article $article)
    {
        $this->authorize('update', $article);
        return view('articles.edit', compact('article'));
    }

    // Mengupdate artikel
    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            // Hapus gambar lama jika ada
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $data['thumbnail'] = $path;
        }

        $article->update($data);

        return redirect()->route('home')->with('success', 'Artikel berhasil diperbarui.'); // Arahkan ke home atau halaman manajemen
    }

    // Menghapus artikel
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        // Hapus gambar dari storage
        if ($article->thumbnail) {
            Storage::disk('public')->delete($article->thumbnail);
        }

        $article->delete();
        return back()->with('success', 'Artikel berhasil dihapus.');
    }
    
    // (Khusus Admin) Mengupdate status
    public function updateStatus(Request $request, Article $article)
    {
        $this->authorize('admin'); // Pastikan hanya admin

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision',
        ]);

        $article->status = $request->status;
        $article->save();

        return back()->with('success', 'Status artikel diperbarui.');
    }
}