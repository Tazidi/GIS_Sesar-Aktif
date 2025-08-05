<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LayerController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\GalleryController;
use App\Models\Map;
use App\Http\Controllers\MapFeatureController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\SurveyLocationController;
use App\Http\Controllers\GalleryMapsController;

/*
|--------------------------------------------------------------------------
| Public Routes (Landing Page, Visualisasi, Artikel)
|--------------------------------------------------------------------------
*/

// Halaman Utama
Route::get('/', [HomeController::class, 'index'])->name('home');

// Halaman Visualisasi Peta Publik
Route::get('/visualisasi-peta', function () {
    $maps = Map::all();
    return view('visualisasi.index', compact('maps'));
})->name('visualisasi.index');

// Endpoint GeoJSON
Route::get('/maps/{map}/geojson', [MapController::class, 'geojson'])->name('maps.geojson');

// Artikel Publik
Route::get('/artikel-publik', [PublicArticleController::class, 'index'])->name('artikel.publik');

// Galeri Publik (tanpa login)
Route::get('/galeri-publik', [GalleryController::class, 'publik'])->name('gallery.publik');
Route::get('/gallery/category/{category}', [GalleryController::class, 'getByCategory'])->name('gallery.getByCategory');
Route::get('/gallery/category/{category}/home', [GalleryController::class, 'getForHome'])->name('gallery.getForHome');

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
        'surveyor' => redirect()->route('surveyor.index'), // ✅ arahkan ke dashboard khusus
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
| Surveyor Dashboard (BARU)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:surveyor'])->get('/surveyor', function () {
    return view('surveyor.index'); // ✅ pastikan file ini ada di resources/views/surveyor/index.blade.php
})->name('surveyor.index');

/*
|--------------------------------------------------------------------------
| Admin & Editor - Manajemen Artikel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    Route::resource('articles', ArticleController::class)->except(['show']);
    Route::patch('articles/{article}/status', [ArticleController::class, 'updateStatus'])->name('articles.updateStatus');
});

Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

/*
|--------------------------------------------------------------------------
| Admin & Editor - Manajemen Galeri
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    Route::get('/galeri', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/galeri/create', [GalleryController::class, 'create'])->name('gallery.create');
    Route::post('/galeri', [GalleryController::class, 'store'])->name('gallery.store');
    Route::get('/galeri/{gallery}/edit', [GalleryController::class, 'edit'])->name('gallery.edit');
    Route::put('/galeri/{gallery}', [GalleryController::class, 'update'])->name('gallery.update');
    Route::delete('/galeri/{gallery}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::patch('/gallery/{gallery}/status', [GalleryController::class, 'updateStatus'])->name('gallery.updateStatus');
});

/*
|--------------------------------------------------------------------------
| Admin Only - Manajemen Galeri, Peta, User, Layer, dan Fitur Peta
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('maps', MapController::class);
    Route::resource('users', UserController::class);
    Route::resource('layers', LayerController::class);
    Route::get('/maps/{map}/features', [MapFeatureController::class, 'index'])->name('map-features.index');
    Route::get('/map-features/{mapFeature}/edit', [MapFeatureController::class, 'edit'])->name('map-features.edit');
    Route::put('/map-features/{mapFeature}', [MapFeatureController::class, 'update'])->name('map-features.update');
});

/*
|--------------------------------------------------------------------------
| Admin & Surveyor - Manajemen Survey
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,surveyor'])->group(function () {
    Route::resource('survey-locations', SurveyLocationController::class)->except(['show']);
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
| Auth (Google Login)
|--------------------------------------------------------------------------
*/
Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback'])->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Auth (Login, Register, Forgot Password)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

Route::get('/galeri-peta', [GalleryMapsController::class, 'index'])->name('gallery_maps.peta');