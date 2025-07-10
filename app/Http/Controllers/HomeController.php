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
            ->latest()
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
        return view('articles.show', compact('article'));
    }
}
