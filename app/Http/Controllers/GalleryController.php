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
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $filename = time() . '_' . $request->file('image')->getClientOriginalName();
        $request->file('image')->move(public_path('gallery'), $filename);

        Gallery::create([
            'user_id'    => Auth::id(),
            'title'      => $validatedData['title'],
            'description'=> $validatedData['description'],
            'image_path' => $filename, // hanya nama file, TANPA 'gallery/'
            'category'   => trim($validatedData['category']),
            'status'     => Auth::user()->role === 'admin' ? 'approved' : 'pending',
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
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $filename = $gallery->image_path;

        if ($request->hasFile('image')) {
            // Hapus file lama
            $oldPath = public_path('gallery/' . $gallery->image_path);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }

            // Simpan file baru
            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('gallery'), $filename);
        }

        $gallery->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'image_path' => $filename,
            'category' => trim($validatedData['category']),
            'last_edited_by' => Auth::id(),
        ]);

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diperbarui!');
    }

    public function destroy(Gallery $gallery)
    {
        if (Auth::user()->role === 'editor' && Auth::id() !== $gallery->user_id) {
            abort(403);
        }

        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();

        return redirect()->route('gallery.index')->with('success', 'Gambar berhasil dihapus.');
    }

    public function getByCategory($category)
    {
        // $category sudah di-decode secara otomatis oleh Laravel
        // Query ini sekarang membandingkan dengan membersihkan spasi dan mengabaikan huruf besar/kecil
        $images = Gallery::where(DB::raw('LOWER(TRIM(category))'), 'like', strtolower(trim($category)))
                        ->where('status', 'approved') // hanya yang disetujui
                        ->latest()
                        ->paginate(12);

        return response()->json($images);
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

    public function petaInteraktif(Request $request)
    {
        // Ambil semua peta yang tersedia
        $maps = \App\Models\Map::all();

        // Jika request AJAX (dipanggil dari JavaScript)
        if ($request->ajax()) {
            if ($maps->isEmpty()) {
                return response('<div class="col-span-full py-8 text-center text-gray-500">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Belum ada peta interaktif</h3>
                    <p class="text-gray-500">Tidak ada peta interaktif untuk kategori Sesar Jawa Bagian Barat.</p>
                </div>', 200);
            }

            // Kembalikan view dengan data maps
            return view('gallery_maps.index', compact('maps'));
        }

        // Jika bukan AJAX, kembalikan view lengkap
        return view('gallery_maps.index', compact('maps'));
    }
}