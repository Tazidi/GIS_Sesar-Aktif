<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    // Menampilkan halaman galeri publik
    public function index()
    {
        $images = Gallery::latest()->paginate(12);
        return view('gallery.index', compact('images'));
    }

    // (Khusus Admin) Menampilkan form untuk menambah gambar
    public function create()
    {
        return view('gallery.create');
    }

    // (Khusus Admin) Menyimpan gambar baru
public function store(Request $request)
{
    // Validasi input
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        'description' => 'nullable|string',
    ]);

    // Proses upload file, Laravel akan memberi nama unik secara otomatis
    $path = $request->file('image')->store('gallery', 'public');

    // Simpan ke database dengan data yang sudah dipisah
    Gallery::create([
        'user_id' => Auth::id(),
        'title' => $validatedData['title'], // Diambil dari input 'title'
        'description' => $validatedData['description'], // Diambil dari input 'description'
        'image_path' => $path, // Diambil dari file yang di-upload
    ]);

    return redirect()->route('admin.gallery.create')->with('success', 'Gambar berhasil diunggah!');
}
}