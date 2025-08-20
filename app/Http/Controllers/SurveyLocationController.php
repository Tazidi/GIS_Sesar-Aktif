<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SurveyLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class SurveyLocationController extends Controller
{
    /**
     * Show the form for creating a new survey location within a project.
     */
    public function create(Project $project)
    {
        Gate::authorize('create', [SurveyLocation::class, $project]);
        return view('survey_locations.create', compact('project'));
    }

    /**
     * Store a newly created survey location in storage.
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('create', [SurveyLocation::class, $project]);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'image_primary' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePaths = [];

        // Process primary image
        if ($request->hasFile('image_primary')) {
            $file = $request->file('image_primary');
            $filename = time() . '_1_' . $file->getClientOriginalName();
            $file->move(public_path('survey'), $filename);
            $imagePaths[] = $filename;
        }

        // Process additional images
        if ($request->hasFile('image_2')) {
            $file = $request->file('image_2');
            $filename = time() . '_2_' . $file->getClientOriginalName();
            $file->move(public_path('survey'), $filename);
            $imagePaths[] = $filename;
        }
        if ($request->hasFile('image_3')) {
            $file = $request->file('image_3');
            $filename = time() . '_3_' . $file->getClientOriginalName();
            $file->move(public_path('survey'), $filename);
            $imagePaths[] = $filename;
        }

        // Create the location with the array of image paths
        $project->surveyLocations()->create([
            'user_id' => Auth::id(),
            'nama' => $data['nama'],
            'deskripsi' => $data['deskripsi'],
            'geometry' => ['lat' => $data['lat'], 'lng' => $data['lng']],
            'images' => $imagePaths, // Save the array to the JSON column
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Lokasi baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified survey location.
     */
    public function edit(SurveyLocation $surveyLocation)
    {
        Gate::authorize('update', $surveyLocation);
        return view('survey_locations.edit', compact('surveyLocation'));
    }

    /**
     * Update the specified survey location in storage.
     */
    public function update(Request $request, SurveyLocation $surveyLocation)
    {
        Gate::authorize('update', $surveyLocation);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'image_primary' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $currentImages = $surveyLocation->images ?? [];
        $newImagePaths = $currentImages;

        // Helper function to process and replace an image
        $processImage = function ($file, $index, &$paths) {
            // Delete old image if it exists
            if (isset($paths[$index]) && File::exists(public_path('survey/' . $paths[$index]))) {
                File::delete(public_path('survey/' . $paths[$index]));
            }
            // Store new image
            $filename = time() . '_' . ($index + 1) . '_' . $file->getClientOriginalName();
            $file->move(public_path('survey'), $filename);
            $paths[$index] = $filename;
        };

        if ($request->hasFile('image_primary')) {
            $processImage($request->file('image_primary'), 0, $newImagePaths);
        }
        if ($request->hasFile('image_2')) {
            $processImage($request->file('image_2'), 1, $newImagePaths);
        }
        if ($request->hasFile('image_3')) {
            $processImage($request->file('image_3'), 2, $newImagePaths);
        }

        $surveyLocation->update([
            'nama' => $data['nama'],
            'deskripsi' => $data['deskripsi'],
            'geometry' => ['lat' => $data['lat'], 'lng' => $data['lng']],
            'images' => array_values($newImagePaths), // Re-index array
        ]);

        return redirect()->route('projects.show', $surveyLocation->project)->with('success', 'Data lokasi berhasil diperbarui.');
    }

    /**
     * Remove the specified survey location from storage.
     */
    public function destroy(SurveyLocation $surveyLocation)
    {
        Gate::authorize('delete', $surveyLocation);

        // Delete all associated images from the server
        if (!empty($surveyLocation->images)) {
            foreach ($surveyLocation->images as $image) {
                if (File::exists(public_path('survey/' . $image))) {
                    File::delete(public_path('survey/' . $image));
                }
            }
        }

        $surveyLocation->delete();
        return back()->with('success', 'Data lokasi berhasil dihapus.');
    }
}
