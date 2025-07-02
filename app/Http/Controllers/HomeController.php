<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Map;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::where('status', 'approved');

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $articles = $query->get();
        $maps = Map::all();

        return view('home', compact('articles', 'maps'));
    }

    public function show($id)
    {
        $article = Article::findOrFail($id);
        return view('articles.show', compact('article'));
    }
}
