<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class PublicArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()->where('status', 'approved');

        // Filter berdasarkan category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Sorting
        $sort = $request->get('sort');
        $order = $request->get('order', 'desc'); // default ke 'desc'

        if (in_array($sort, ['author', 'title', 'created_at'])) {
            $query->orderBy($sort, $order);
        } else {
            $query->orderBy('created_at', 'desc'); // default
        }

        // Ambil semua category unik untuk dropdown filter
        $categories = Article::whereNotNull('category')->pluck('category')->unique()->filter()->values();

        // Pagination
        $articles = $query->paginate(10)->withQueryString();

        return view('articles.publik', compact('articles', 'categories'));
    }
}
