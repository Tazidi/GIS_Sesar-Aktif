<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article; // <-- Import model Article

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Perintah ini akan memanggil ArticleFactory sebanyak 6 kali
        // dan menyimpannya ke database.
        Article::factory()->count(20)->create();
    }
}