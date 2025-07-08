<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Map;

class HomeController extends Controller
{
public function index()
{
    $todayPosts = Article::where('status', 'approved')->latest()->take(2)->get();

    // REVISI: Ubah take(5) menjadi take(3) untuk menampilkan 1 besar + 2 kecil
    $mainStories = Article::where('status', 'approved')->latest()->skip(2)->take(3)->get();

    $popularArticles = Article::where('status', 'approved')->inRandomOrder()->take(5)->get();

    $maps = Map::all();

    return view('home', compact('todayPosts', 'mainStories', 'popularArticles', 'maps'));
}

    public function show($id)
    {
        $article = Article::findOrFail($id);
        return view('articles.show', compact('article'));
    }
}
