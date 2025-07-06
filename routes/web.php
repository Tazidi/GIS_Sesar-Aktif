<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Article;
use App\Models\Map;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Public Routes (Landing Page)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/article/{id}', [HomeController::class, 'show'])->name('article.show');

/*
|--------------------------------------------------------------------------
| Redirect /dashboard -> Berdasarkan Role
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $role = Auth::check() ? Auth::user()->role : null;

    return match ($role) {
        'admin' => redirect()->route('admin.index'),
        'editor' => redirect()->route('editor.index'),
        default => redirect()->route('home'),
    };
})->middleware(['auth'])->name('dashboard');

// Route visualisasi halaman
Route::get('/visualisasi-peta', function () {
    $maps = \App\Models\Map::all();
    return view('visualisasi.index', compact('maps'));
})->name('visualisasi.index');

// Route untuk ambil GeoJSON via visualisasi
Route::get('/maps/{map}/geojson', [MapController::class, 'geojson'])->name('maps.geojson');

/*
|--------------------------------------------------------------------------
| Admin Dashboard (Tanpa Statistik)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->get('/admin', function () {
    return view('admin.index');
})->name('admin.index');

Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    // Route update status artikel (khusus admin misalnya)
    Route::patch('/articles/{article}/status', [ArticleController::class, 'updateStatus'])
        ->name('articles.updateStatus');
});

/*
|--------------------------------------------------------------------------
| Editor Dashboard (Tanpa Statistik)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:editor'])->get('/editor', function () {
    return view('editor.index');
})->name('editor.index');

/*
|--------------------------------------------------------------------------
| Admin & Editor - Artikel CRUD
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:editor,admin'])->group(function () {
    Route::resource('/articles', ArticleController::class);
});

/*
|--------------------------------------------------------------------------
| Admin Only - Peta dan User CRUD
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('/maps', MapController::class);
    Route::resource('/users', UserController::class);
});

/*
|--------------------------------------------------------------------------
| Optional Profile Route
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Login/Register/Forgot Password)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';