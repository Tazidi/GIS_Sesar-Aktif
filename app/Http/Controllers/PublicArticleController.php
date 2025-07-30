<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class PublicArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()->where('status', 'approved');

        // Filter berdasarkan tag
        if ($request->filled('tag')) {
            $query->where('tags', $request->tag);
        }

        // Sorting
        $sort = $request->get('sort');
        $order = $request->get('order', 'desc'); // default ke 'desc'

        if (in_array($sort, ['author', 'title', 'created_at'])) {
            $query->orderBy($sort, $order);
        } else {
            $query->orderBy('created_at', 'desc'); // default
        }

        // Ambil semua tag unik untuk dropdown filter
        $tags = Article::whereNotNull('tags')->pluck('tags')->unique()->filter()->values();

        // Pagination
        $articles = $query->paginate(10)->withQueryString();

        return view('articles.publik', compact('articles', 'tags'));
    }
}
