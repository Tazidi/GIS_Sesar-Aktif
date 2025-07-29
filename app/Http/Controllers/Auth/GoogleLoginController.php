<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    /**
     * Redirect pengguna ke halaman autentikasi Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Dapatkan informasi pengguna dari Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cari atau buat user baru
            $user = User::updateOrCreate(
                [
                    // Cari user berdasarkan email
                    'email' => $googleUser->getEmail(),
                ],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    // 'password' akan otomatis NULL karena kita tidak menyertakannya
                ]
            );

            // --- Modifikasi Role di Sini ---
            // Jika pengguna ini baru dibuat (created_at == updated_at),
            // berikan role default.
            // Anda bisa menambahkan logika yang lebih kompleks sesuai kebutuhan.
            if ($user->wasRecentlyCreated) {
                $user->role = 'user'; // Atur role default untuk pengguna baru
                $user->save();
            }

            // Login pengguna ke sistem
            Auth::login($user);

            // Redirect ke halaman yang diinginkan setelah login
            return redirect('/dashboard'); // Ganti dengan rute tujuan Anda

        } catch (\Throwable $th) {
            // Jika terjadi error, kembali ke halaman login dengan pesan error
            return redirect('/login')->with('error', 'Terjadi masalah saat login dengan Google.');
        }
    }
}