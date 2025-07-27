<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import DB Facade
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    // ... (method publik, index, create, store, edit, update, destroy tetap sama seperti kode saya sebelumnya) ...
    public function publik()
    {
        return view('gallery.publik');
    }

    public function index()
    {
        $images = Gallery::latest()->paginate(12);
        return view('gallery.index', compact('images'));
    }

    public function create()
    {
        return view('gallery.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $path = $request->file('image')->store('gallery', 'public');

        Gallery::create([
            'user_id' => Auth::id(),
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'image_path' => $path,
            'category' => trim($validatedData['category']), // Tambahkan trim() di sini untuk membersihkan spasi
        ]);

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diunggah!');
    }

    public function edit(Gallery $gallery)
    {
        return view('gallery.edit', ['image' => $gallery]);
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $path = $gallery->image_path;

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($gallery->image_path);
            $path = $request->file('image')->store('gallery', 'public');
        }

        $gallery->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'image_path' => $path,
            'category' => trim($validatedData['category']), // Tambahkan trim() di sini juga
        ]);

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diperbarui!');
    }

    public function destroy(Gallery $gallery)
    {
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();
        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil dihapus.');
    }

    public function getByCategory($category)
    {
        // $category sudah di-decode secara otomatis oleh Laravel
        // Query ini sekarang membandingkan dengan membersihkan spasi dan mengabaikan huruf besar/kecil
        $images = Gallery::where(DB::raw('LOWER(TRIM(category))'), 'like', strtolower(trim($category)))
                          ->latest()
                          ->paginate(12);

        return response()->json($images);
    }

    public function getForHome($category)
    {
        $images = Gallery::where(DB::raw('LOWER(TRIM(category))'), 'like', strtolower(trim($category)))
                          ->latest()
                          ->take(6)
                          ->get();

        return response()->json($images);
    }
}
