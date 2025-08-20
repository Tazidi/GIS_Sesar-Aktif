<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\SurveyLocation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SurveyLocationPolicy
{
    use HandlesAuthorization;

    /**
     * Berikan izin super-admin untuk semua aksi.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, $ability)
    {
        // Asumsi Anda memiliki kolom 'role' di model User
        if ($user->role === 'admin') {
            return true;
        }
    }

    /**
     * Tentukan apakah pengguna dapat melihat semua data.
     */
    public function viewAny(User $user): bool
    {
        // Izinkan admin (sudah ditangani di 'before') dan surveyor
        return $user->role === 'surveyor';
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail data.
     */
    public function view(User $user, SurveyLocation $surveyLocation): bool
    {
        // Admin bisa lihat semua (sudah handle di before)
        if ($user->role === 'surveyor') {
            // Semua surveyor boleh lihat semua lokasi (untuk collab)
            return true;
        }
        return false;
    }

    /**
     * Tentukan apakah pengguna dapat membuat data baru.
     */
    public function create(User $user, Project $project): bool
    {
        // Izinkan surveyor membuat data baru
        return $user->role === 'surveyor';
    }

    /**
     * Tentukan apakah pengguna dapat mengupdate data.
     */
    public function update(User $user, SurveyLocation $surveyLocation): bool
    {
        // Izinkan surveyor mengupdate data miliknya sendiri
        return $user->id === $surveyLocation->user_id;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus data.
     */
    public function delete(User $user, SurveyLocation $surveyLocation): bool
    {
        // Izinkan surveyor menghapus data miliknya sendiri
        return $user->id === $surveyLocation->user_id;
    }
}
