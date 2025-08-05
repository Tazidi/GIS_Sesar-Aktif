<?php

namespace App\Http\Controllers;

use App\Models\SurveyLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 1. Import trait

class SurveyLocationController extends BaseController
{
    use AuthorizesRequests; // 2. Gunakan trait di dalam kelas

    public function index()
    {
        if (Auth::user()->role === 'admin') {
            // Admin bisa lihat semua data dan relasi user-nya
            $locations = SurveyLocation::with('user')->latest()->get();
        } else {
            // Surveyor hanya lihat miliknya sendiri
            $locations = SurveyLocation::with('user')->where('user_id', Auth::id())->latest()->get();
        }

        return view('survey_locations.index', compact('locations'));
    }

    public function create()
    {
        return view('survey_locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('survey'), $filename);
            $data['image'] = $filename;
        }

        $data['geometry'] = ['lat' => $data['lat'], 'lng' => $data['lng']];
        $data['user_id'] = Auth::id();

        SurveyLocation::create($data);

        return redirect()->route('survey-locations.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(SurveyLocation $surveyLocation)
    {
        // Sekarang metode authorize() akan tersedia
        $this->authorize('update', $surveyLocation);
        return view('survey_locations.edit', compact('surveyLocation'));
    }

    public function update(Request $request, SurveyLocation $surveyLocation)
    {
        $this->authorize('update', $surveyLocation);
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($surveyLocation->image && File::exists(public_path('survey/' . $surveyLocation->image))) {
                File::delete(public_path('survey/' . $surveyLocation->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('survey'), $filename);
            $data['image'] = $filename;
        }

        $data['geometry'] = ['lat' => $data['lat'], 'lng' => $data['lng']];
        $surveyLocation->update($data);

        return redirect()->route('survey-locations.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(SurveyLocation $surveyLocation)
    {
        $this->authorize('delete', $surveyLocation);

        if ($surveyLocation->image && File::exists(public_path('survey/' . $surveyLocation->image))) {
            File::delete(public_path('survey/' . $surveyLocation->image));
        }

        $surveyLocation->delete();
        return back()->with('success', 'Data berhasil dihapus.');
    }
}
