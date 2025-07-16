<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    // ✅ 1. Galeri Publik (untuk semua pengunjung)
    public function publik()
    {
        $images = Gallery::latest()->paginate(12);
        return view('gallery.publik', compact('images'));
    }

    // ✅ 2. Galeri Admin (hanya untuk admin yang login)
    public function index()
    {
        $images = Gallery::latest()->paginate(12);
        return view('gallery.index', compact('images'));
    }

    // ✅ 3. Form Upload
    public function create()
    {
        return view('gallery.create');
    }

    // ✅ 4. Menyimpan Gambar
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'description' => 'nullable|string',
        ]);

        // Simpan ke public/gallery
        $file = $request->file('image');
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('gallery'), $filename);

        Gallery::create([
            'user_id' => Auth::id(),
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'image_path' => $filename,
        ]);

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diunggah!');
    }

    // ✅ 5. Edit
    public function edit(Gallery $gallery)
    {
        return view('gallery.edit', ['image' => $gallery]);
    }

    // ✅ 6. Update
    public function update(Request $request, Gallery $gallery)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'description' => 'nullable|string',
        ]);

        // Jika upload file baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama
            $oldPath = public_path('gallery/' . $gallery->image_path);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }

            // Upload baru
            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('gallery'), $filename);

            $gallery->image_path = $filename;
        }

        $gallery->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'image_path' => $gallery->image_path,
        ]);

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diperbarui!');
    }

    // ✅ 7. Hapus
    public function destroy(Gallery $gallery)
    {
        $path = public_path('gallery/' . $gallery->image_path);
        if (file_exists($path)) {
            unlink($path);
        }

        $gallery->delete();

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil dihapus.');
    }
}