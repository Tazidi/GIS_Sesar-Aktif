<?php

namespace App\Http\Controllers;

use App\Models\Layer;
use Illuminate\Http\Request;

class LayerController extends Controller
{
    public function index()
    {
        $layers = Layer::all();
        return view('layers.index', compact('layers'));
    }

    public function create()
    {
        return view('layers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_layer' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        Layer::create($data);

        return redirect()->route('layers.index')->with('success', 'Layer berhasil disimpan!');
    }

    public function edit(Layer $layer)
    {
        return view('layers.edit', compact('layer'));
    }

    public function update(Request $request, Layer $layer)
    {
        $data = $request->validate([
            'nama_layer' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $layer->update($data);

        return redirect()->route('layers.index')->with('success', 'Layer berhasil diperbarui!');
    }

    public function destroy(Layer $layer)
    {
        $layer->delete();
        return redirect()->route('layers.index')->with('success', 'Layer berhasil dihapus.');
    }
}
