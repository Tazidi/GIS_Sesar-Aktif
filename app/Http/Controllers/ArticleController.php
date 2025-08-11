<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    public function publik(Request $request)
    {
        $query = Article::query()->where('status', 'approved');

        // Terapkan filter PENCARIAN
        $query->when($request->filled('search'), function ($q) use ($request) {
            $searchTerm = $request->input('search');
            return $q->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('title', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('content', 'LIKE', "%{$searchTerm}%");
            });
        });

        // Terapkan filter TAG
        if ($request->filled('tag')) {
            $query->where('tags', $request->input('tag'));
        }

        // Terapkan PENGURUTAN
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        $query->orderBy($sort, $order);

        $articles = $query->paginate(10)->withQueryString();
        $tags = Article::where('status', 'approved')->whereNotNull('tags')->distinct()->pluck('tags');

        // [PERUBAHAN UTAMA] Cek apakah ini permintaan AJAX dari JavaScript
        if ($request->wantsJson()) {
            // Jika ya, kirim data dalam format JSON
            return response()->json([
                'articles' => $articles,
                'tags' => $tags,
                // Render view partial untuk konten dan pagination
                'contentHTML' => view('partials.article_list', ['articles' => $articles])->render(),
                'paginationHTML' => (string) $articles->links(),
            ]);
        }

        // Jika tidak, tampilkan halaman HTML seperti biasa
        return view('articles.publik', compact('articles', 'tags'));
    }

    // ... (method lainnya tidak diubah) ...
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            $articles = Article::latest()->paginate(10);
        } elseif ($user && $user->role === 'editor') {
            $articles = Article::where('user_id', $user->id)->latest()->paginate(10);
        } else {
            abort(403); 
        }

        return view('articles.index', compact('articles'));
    }

    public function show(Article $article)
    {
        if (
            $article->status !== 'approved' &&
            (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'editor']))
        ) {
            abort(404);
        }

        $ip = request()->ip();
        $key = 'article_viewed_' . $article->id . '_' . $ip;

        if (!cache()->has($key)) {
            $article->increment('visit_count');
            cache()->put($key, true, now()->addHours(6));
        }

        return view('articles.show', compact('article'));
    }

    public function create()
    {
        $tags = Article::whereNotNull('tags')->distinct()->pluck('tags');
        return view('articles.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|max:255',
            'author'    => 'required|max:255',
            'content'   => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'tags'      => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('thumbnails'), $filename);
            $data['thumbnail'] = 'thumbnails/' . $filename;
        }

        $data['user_id'] = Auth::id();
        $data['last_edited_by'] = Auth::id();

        if (Auth::user()->role === 'admin') {
            $data['status'] = 'approved';
            $data['approved_by'] = Auth::id();
        } else {
            $data['status'] = 'pending';
        }

        Article::create($data);

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil disimpan dan menunggu persetujuan.');
    }

    public function edit(Article $article)
    {
        $this->authorize('update', $article);
        $tags = Article::whereNotNull('tags')->distinct()->pluck('tags');
        return view('articles.edit', compact('article', 'tags'));
    }

    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'title'     => 'required|max:255',
            'author'    => 'required|max:255',
            'content'   => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'tags'      => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($article->thumbnail && File::exists(public_path($article->thumbnail))) {
                File::delete(public_path($article->thumbnail));
            }

            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('thumbnails'), $filename);
            $data['thumbnail'] = 'thumbnails/' . $filename;
        }

        $article->update($data);

        $article->last_edited_by = Auth::id();
        $article->save();

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        if ($article->thumbnail && File::exists(public_path($article->thumbnail))) {
            File::delete(public_path($article->thumbnail));
        }

        $article->delete();

        return back()->with('success', 'Artikel berhasil dihapus.');
    }

    public function updateStatus(Request $request, Article $article)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision',
        ]);

        if ($request->status === 'approved') {
            $article->approved_by = Auth::id();
        }
        $article->status = $request->status;
        $article->save();

        return back()->with('success', 'Status artikel diperbarui.');
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/articles'), $filename);

            $url = asset('uploads/articles/' . $filename);

            return response()->json([
                'uploaded' => true,
                'url' => $url
            ]);
        }

        return response()->json([
            'uploaded' => false,
            'error' => [
                'message' => 'Tidak ada file yang diupload.'
            ]
        ]);
    }
}