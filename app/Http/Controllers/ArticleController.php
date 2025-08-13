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
                         ->orWhere('content', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('tags', 'LIKE', "%{$searchTerm}%");
            });
        });

        // Terapkan filter KATEGORI
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Terapkan PENGURUTAN
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        $query->orderBy($sort, $order);

        $articles = $query->paginate(9)->withQueryString();

        // Ambil daftar kategori unik
        $categories = Article::where('status', 'approved')->whereNotNull('category')->distinct()->pluck('category');

        // Note: AJAX functionality might need adjustment due to complex layout.
        // For now, this focuses on the layout generation on page load.
        if ($request->wantsJson()) {
            // This part might need to be refactored if complex AJAX is kept.
            // Returning a simpler list for now.
            return response()->json([
                'contentHTML' => view('partials.article_list', ['articles' => $articles])->render(),
                'paginationHTML' => (string) $articles->links(),
            ]);
        }
        
    return view('articles.publik', compact('articles', 'categories'));
    }
    
    // ... (method index dan show tidak berubah secara signifikan) ...
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
        $categories = Article::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return view('articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|max:255',
            'author'    => 'required|max:255',
            'content'   => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'category'  => 'nullable|string|max:255', // Diubah dari tags
            'tags'      => 'nullable|string|max:255', // Field baru untuk hashtag
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
        // Ambil daftar kategori unik untuk dropdown
        $categories = Article::whereNotNull('category')->distinct()->pluck('category');
        return view('articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'title'     => 'required|max:255',
            'author'    => 'required|max:255',
            'content'   => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'category'  => 'nullable|string|max:255', // Diubah dari tags
            'tags'      => 'nullable|string|max:255', // Field baru untuk hashtag
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
    
    // ... (method destroy, updateStatus, dan uploadImage tidak berubah) ...
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