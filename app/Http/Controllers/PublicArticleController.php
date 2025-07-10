<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Article;

class PublicArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('status', 'approved')->latest()->paginate(10);
        return view('articles.publik', compact('articles'));
    }
}
