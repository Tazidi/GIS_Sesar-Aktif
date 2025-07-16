<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Map;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Artikel yang di-approve hari ini (Today Post)
        $todayPosts = Article::where('status', 'approved')
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->take(2)
            ->get();

        // Artikel yang di-approve sebelum hari ini (Main Story)
        $mainStories = Article::where('status', 'approved')
            ->whereDate('created_at', '<', Carbon::today())
            ->orderByDesc('visit_count')
            ->take(3)
            ->get();

        // Artikel populer random (simulasi)
        $popularArticles = Article::where('status', 'approved')
            ->inRandomOrder()
            ->take(5)
            ->get();

        $maps = Map::all();

        return view('home', compact('todayPosts', 'mainStories', 'popularArticles', 'maps'));
    }

    public function show($id)
    {
        $article = Article::findOrFail($id);

        $ip = request()->ip();
        $key = 'article_viewed_' . $article->id . '_' . $ip;

        if (!cache()->has($key)) {
            $article->increment('visit_count');
            cache()->put($key, true, now()->addHours(6)); // Hindari view spam dari IP yg sama
        }

        return view('articles.show', compact('article'));
    }
}
