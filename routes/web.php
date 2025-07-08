<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 1. DAFTARKAN SEMUA CONTROLLER YANG DIGUNAKAN DI SINI
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LayerController;

// Model (jika dibutuhkan langsung di route)
use App\Models\Map;

/*
|--------------------------------------------------------------------------
| RUTE PUBLIK (Dapat Diakses Siapa Saja)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/visualisasi-peta', function () {
    $maps = Map::all();
    return view('visualisasi.index', compact('maps'));
})->name('visualisasi.index');

Route::get('/maps/{map}/geojson', [MapController::class, 'geojson'])->name('maps.geojson');

// Rute PUBLIK untuk Artikel
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');


/*
|--------------------------------------------------------------------------
| RUTE YANG MEMERLUKAN LOGIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Redirect /dashboard berdasarkan role
    Route::get('/dashboard', function () {
        $role = Auth::user()->role;
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'), // Ubah ke nama rute dashboard admin yang baru
            'editor' => redirect()->route('editor.dashboard'), // Ubah ke nama rute dashboard editor yang baru
            default => redirect()->route('home'),
        };
    })->name('dashboard');

    // Rute Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | RUTE MANAJEMEN KONTEN (EDITOR & ADMIN)
    | Semua URL di sini akan diawali dengan /manage/...
    |--------------------------------------------------------------------------
    */
    Route::prefix('manage')->name('manage.')->middleware('role:editor,admin')->group(function () {
        // Hanya rute untuk create, store, edit, update, destroy
        Route::resource('articles', ArticleController::class)->except(['index', 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | RUTE KHUSUS ADMIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Halaman dashboard admin
        Route::get('/dashboard', function () {
            $userCount = \App\Models\User::count();
            $articleCount = \App\Models\Article::count();
            return view('admin.index', compact('userCount', 'articleCount'));
        })->name('dashboard');
        
        // Rute untuk manajemen Peta, User, dll.
        Route::resource('maps', MapController::class);
        Route::resource('users', UserController::class);
        Route::resource('layers', LayerController::class);

        // Rute untuk admin mengubah status artikel
        Route::patch('articles/{article}/status', [ArticleController::class, 'updateStatus'])
            ->name('articles.updateStatus');
    });

    // Rute dashboard editor (jika ada halaman khusus)
    Route::get('/editor/dashboard', function () {
        return view('editor.index');
    })->middleware('role:editor')->name('editor.index');

});


/*
|--------------------------------------------------------------------------
| Auth Routes (Login/Register/Forgot Password)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';