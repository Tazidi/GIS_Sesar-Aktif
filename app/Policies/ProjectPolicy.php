<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'surveyor';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Admin bisa lihat semua
        if ($user->role === 'admin') {
            return true;
        }
        // Surveyor juga boleh lihat semua (read-only)
        if ($user->role === 'surveyor') {
            return true;
        }
        // Role lain default false
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'surveyor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        // Surveyor hanya bisa mengupdate proyek miliknya
        return $user->id === $project->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        // Surveyor hanya bisa menghapus proyek miliknya
        return $user->id === $project->user_id;
    }
}