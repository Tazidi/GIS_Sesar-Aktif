<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     */
    public function index()
    {
        $query = Project::query()->withCount('surveyLocations');

        // Admin & Surveyor lihat semua
        if (!in_array(Auth::user()->role, ['admin','surveyor'])) {
            $query->where('user_id', Auth::id());
        }

        $projects = $query->latest()->get();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // FIX: Create project directly to bypass potential caching issues
        // on the User model relationship.
        $data['user_id'] = Auth::id();
        Project::create($data);

        return redirect()->route('projects.index')->with('success', 'Proyek baru berhasil dibuat.');
    }

    /**
     * Display the specified project with its locations.
     */
    public function show(Project $project)
    {
        // Ensure the user is authorized to view this project
        Gate::authorize('view', $project);

        // Eager load locations for the map
        $project->load('surveyLocations');

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        Gate::authorize('update', $project);
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($data);

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil diperbarui.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        // Logic to delete associated images can be added here if needed
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus.');
    }
}
