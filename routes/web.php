<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LayerController;
use App\Models\Article;
use App\Models\Map;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Public Routes (Landing Page, Visualisasi, Artikel)
|--------------------------------------------------------------------------
*/

// Beranda
Route::get('/', [HomeController::class, 'index'])->name('home');

// Detail artikel publik
Route::get('/article/{id}', [HomeController::class, 'show'])->name('article.show');

// Halaman visualisasi peta publik
Route::get('/visualisasi-peta', function () {
    $maps = Map::all();
    return view('visualisasi.index', compact('maps'));
})->name('visualisasi.index');

// Endpoint GeoJSON untuk peta
Route::get('/maps/{map}/geojson', [MapController::class, 'geojson'])->name('maps.geojson');

/*
|--------------------------------------------------------------------------
| Redirect Dashboard Berdasarkan Role
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

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->get('/admin', function () {
    return view('admin.index');
})->name('admin.index');

/*
|--------------------------------------------------------------------------
| Editor Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:editor'])->get('/editor', function () {
    return view('editor.index');
})->name('editor.index');

/*
|--------------------------------------------------------------------------
| Admin & Editor - Manajemen Artikel (CRUD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    Route::resource('articles', ArticleController::class);
    Route::patch('articles/{article}/status', [ArticleController::class, 'updateStatus'])->name('articles.updateStatus');
});

/*
|--------------------------------------------------------------------------
| Admin Only - Manajemen Peta, User, Layer (CRUD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('maps', MapController::class);
    Route::resource('users', UserController::class);
    Route::resource('layers', LayerController::class);
});

/*
|--------------------------------------------------------------------------
| Profile (Semua Authenticated User)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Auth (Login, Register, Forgot Password)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';