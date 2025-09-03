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
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WeatherController; 



/*
|--------------------------------------------------------------------------
| Public Routes (Landing Page, Visualisasi, Artikel)
|--------------------------------------------------------------------------
*/

// Halaman Utama
Route::get('/', [HomeController::class, 'index'])->name('home');

// Halaman Visualisasi Peta Publik
Route::get('/visualisasi', [MapController::class, 'visualisasi'])->name('visualisasi.index');

// Endpoint GeoJSON
Route::get('/maps/{map}/geojson', [MapController::class, 'geojson'])->name('maps.geojson');

// Artikel Publik
Route::get('/artikel-publik', [ArticleController::class, 'publik'])->name('artikel.publik');

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
    Route::post('/ckeditor/upload', [ArticleController::class, 'uploadImage'])->name('ckeditor.upload');
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
    Route::get('/galeri-peta/layer/{layer}', [GalleryMapsController::class, 'showLayer'])->name('gallery.layer.show');
});

/*
|--------------------------------------------------------------------------
| Admin Only - Manajemen Galeri, Peta, User, Layer, dan Fitur Peta
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('maps', MapController::class);
    Route::put('/maps/{map}/update-kategori', [MapController::class, 'updateKategori'])
        ->name('maps.updateKategori');
    Route::resource('users', UserController::class);
    Route::resource('layers', LayerController::class);
    Route::get('/maps/{map}/features', [MapFeatureController::class, 'index'])->name('map-features.index');
    Route::get('/map-features/{mapFeature}/edit', [MapFeatureController::class, 'edit'])->name('map-features.edit');
    Route::put('/map-features/{mapFeature}', [MapFeatureController::class, 'update'])->name('map-features.update');
});

/*
|--------------------------------------------------------------------------
| Admin & Surveyor - Manajemen Proyek dan Survey
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,surveyor'])->group(function () {
    // Rute untuk mengelola Proyek (CRUD)
    Route::resource('projects', ProjectController::class);

    // Rute untuk mengelola Lokasi Survey yang bersarang di dalam Proyek
    Route::resource('projects.survey-locations', SurveyLocationController::class)
        ->except(['index', 'show']) // index & show ditangani oleh ProjectController
        ->shallow();
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

Route::get('/galeri-peta', [GalleryMapsController::class, 'galeriPeta'])->name('gallery_maps.peta');
Route::get('/gallery/{id}', [GalleryMapsController::class, 'show'])->name('gallery.show');
Route::get('/gallery-maps', [GalleryMapsController::class, 'galeriPeta'])
     ->name('gallery_maps.index');

// --- Galeri Peta (Maps + Proyek) ---
Route::get('/galeri-peta', [GalleryMapsController::class, 'galeriPeta'])->name('gallery_maps.index');

// Detail Map (eksisting)
Route::get('/galeri-peta/maps/{id}', [GalleryMapsController::class, 'show'])->name('gallery_maps.show');

// Detail Proyek (view-only di galeri)
Route::get('/galeri-peta/projects/{project}', [GalleryMapsController::class, 'showProject'])->name('gallery_maps.projects.show');
Route::get('/galeri-peta', [GalleryMapsController::class, 'galeriPeta'])
    ->name('gallery_maps.peta');
// Detail lokasi proyek di galeri (view-only)
Route::get('/galeri-peta/projects/{project}/locations/{location}', 
    [GalleryMapsController::class, 'showProjectLocation']
)->name('gallery_maps.projects.locations.show');

Route::get('/gallery/layer/{layer}', [GalleryMapsController::class, 'showLayer'])->name('gallery_maps.showLayer');

Route::get('/api/weather', [WeatherController::class, 'get'])->name('weather.get');

Route::patch('/articles/{article}/feature', [App\Http\Controllers\ArticleController::class, 'toggleFeature'])
    ->name('articles.toggleFeature');