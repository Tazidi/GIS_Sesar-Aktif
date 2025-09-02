<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function publik()
    {
        return view('gallery.publik');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            $images = Gallery::latest()->paginate(12);
        } elseif ($user && $user->role === 'editor') {
            $images = Gallery::where('user_id', $user->id)->latest()->paginate(12);
        } else {
            abort(403);
        }

        return view('gallery.index', compact('images'));
    }

    public function create()
    {
        return view('gallery.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'         => 'required|string|max:255',
            'category'      => 'required|string',
            'description'   => 'nullable|string',
            'main_image'    => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'extra_images.*'=> 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        // Simpan foto utama
        $mainImageName = time() . '_main.' . $request->file('main_image')->extension();
        $request->file('main_image')->move(public_path('gallery'), $mainImageName);

        // Simpan foto tambahan (max 9)
        $extraImages = [];
        if ($request->hasFile('extra_images')) {
            foreach ($request->file('extra_images') as $file) {
                $name = time() . '_' . uniqid() . '.' . $file->extension();
                $file->move(public_path('gallery'), $name);
                $extraImages[] = $name;
                if (count($extraImages) >= 9) break;
            }
        }

        Gallery::create([
            'user_id'       => Auth::id(),
            'title'         => $validatedData['title'],
            'description'   => $validatedData['description'] ?? null,
            'main_image'    => $mainImageName,
            'extra_images'  => $extraImages,
            'category'      => trim($validatedData['category']),
            'status'        => Auth::user()->role === 'admin' ? 'approved' : 'pending',
        ]);

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diunggah!');
    }

    public function edit(Gallery $gallery)
    {
        if (Auth::user()->role === 'editor' && Auth::id() !== $gallery->user_id) {
            abort(403);
        }

        return view('gallery.edit', ['image' => $gallery]);
    }

    public function update(Request $request, Gallery $gallery)
    {
        if (Auth::user()->role === 'editor' && Auth::id() !== $gallery->user_id) {
            abort(403);
        }

        $validatedData = $request->validate([
            'title'         => 'required|string|max:255',
            'category'      => 'required|string',
            'description'   => 'nullable|string',
            'main_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'extra_images.*'=> 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        // Ganti foto utama jika ada
        if ($request->hasFile('main_image')) {
            if ($gallery->main_image && file_exists(public_path('gallery/' . $gallery->main_image))) {
                unlink(public_path('gallery/' . $gallery->main_image));
            }
            $mainImageName = time() . '_main.' . $request->file('main_image')->extension();
            $request->file('main_image')->move(public_path('gallery'), $mainImageName);
            $gallery->main_image = $mainImageName;
        }

        // Tambah foto tambahan (merge dengan existing)
        if ($request->hasFile('extra_images')) {
            $currentExtras = $gallery->extra_images ?? [];
            foreach ($request->file('extra_images') as $file) {
                if (count($currentExtras) >= 9) break;
                $name = time() . '_' . uniqid() . '.' . $file->extension();
                $file->move(public_path('gallery'), $name);
                $currentExtras[] = $name;
            }
            $gallery->extra_images = $currentExtras;
        }

        $gallery->title = $validatedData['title'];
        $gallery->description = $validatedData['description'] ?? null;
        $gallery->category = trim($validatedData['category']);
        $gallery->last_edited_by = Auth::id();
        $gallery->save();

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diperbarui!');
    }

    public function destroy(Gallery $gallery)
    {
        if (Auth::user()->role === 'editor' && Auth::id() !== $gallery->user_id) {
            abort(403);
        }

        // Hapus main image
        if ($gallery->main_image) {
            $mainPath = public_path('gallery/' . $gallery->main_image);
            if (file_exists($mainPath)) {
                unlink($mainPath);
            }
        }

        // Hapus extra images (jika ada)
        if (is_array($gallery->extra_images)) {
            foreach ($gallery->extra_images as $extra) {
                $extraPath = public_path('gallery/' . $extra);
                if (file_exists($extraPath)) {
                    unlink($extraPath);
                }
            }
        }

        // Hapus record dari database
        $gallery->delete();

        return redirect()->route('gallery.index')
                        ->with('success', 'Gambar berhasil dihapus.');
    }

    /**
     * PERUBAHAN UTAMA: Menambahkan logika pencarian dan memperbaiki respons JSON.
     */
    public function getByCategory(Request $request, $category)
    {
        // Ambil term pencarian dari request, default string kosong
        $searchTerm = $request->input('search', '');

        $query = Gallery::where(DB::raw('LOWER(TRIM(category))'), 'like', strtolower(trim($category)))
                        ->where('status', 'approved');

        // Jika ada term pencarian, tambahkan kondisi WHERE
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Lanjutkan dengan paginasi
        $images = $query->latest()->paginate(12);

        // Penting: Tambahkan query string pencarian ke link paginasi
        // agar filter tetap aktif saat berpindah halaman.
        if (!empty($searchTerm)) {
            $images->appends(['search' => $searchTerm]);
        }
        
        // **PERBAIKAN**: Mengembalikan data paginasi sebagai array yang rapi.
        // Ini membuat file `simple-json.blade.php` tidak lagi diperlukan.
        return response()->json([
            'data' => $images->items(),
            'links' => $images->toArray()['links'], // Kirim array link paginasi
        ]);
    }

    public function getForHome($category)
    {
        $images = Gallery::where(DB::raw('LOWER(TRIM(category))'), 'like', strtolower(trim($category)))
                        ->where('status', 'approved')
                        ->latest()
                        ->take(6)
                        ->get();

        return response()->json($images);
    }
    
    public function updateStatus(Request $request, Gallery $gallery)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,revision',
        ]);

        if ($request->status === 'approved') {
            $gallery->approved_by = Auth::id();
        }
        $gallery->status = $request->status;
        $gallery->save();

        return back()->with('success', 'Status galeri berhasil diperbarui.');
    }
}
