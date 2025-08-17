<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Map;
use App\Models\Gallery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman utama (home).
     */
    public function index()
    {
        // Artikel terbaru yang di-approve (Latest Post)
        $latestPosts = Article::where('status', 'approved')
            ->latest('created_at')
            ->take(2)
            ->get();

        // Artikel yang di-approve sebelum hari ini (Main Story)
        $mainStories = Article::where('status', 'approved')
            ->orderByDesc('visit_count')
            ->take(5)
            ->get();

        // Artikel populer random (simulasi)
        $popularArticles = Article::where('status', 'approved')
            ->inRandomOrder()
            ->take(5)
            ->get();

        // Galeri terbaru
        $galleries = Gallery::latest()->take(10)->get();

        // Data peta
        $maps = Map::latest()->get();

        // Kirim semua data ke view 'home'
        return view('home', compact(
            'latestPosts',
            'mainStories',
            'popularArticles',
            'galleries',
            'maps'
        ));
    }

    /**
     * Menampilkan detail satu artikel.
     */
    public function show($id)
    {
        $article = Article::findOrFail($id);

        // Menghitung jumlah pengunjung unik per artikel
        $ip = request()->ip();
        $key = 'article_viewed_' . $article->id . '_' . $ip;

        if (!cache()->has($key)) {
            $article->increment('visit_count');
            cache()->put($key, true, now()->addHours(6)); // Hindari view spam dari IP yg sama
        }

        return view('articles.show', compact('article'));
    }
}