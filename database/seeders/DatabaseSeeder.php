<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Panggil ArticleSeeder yang baru kita buat
        $this->call([
            ArticleSeeder::class,
            // Anda bisa menambahkan Seeder lain di sini jika ada
        ]);
    }
}