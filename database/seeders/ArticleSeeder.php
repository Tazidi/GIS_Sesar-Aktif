<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tentukan path sumber dan tujuan
        $sourcePath = database_path('seeders/images/thumbnails');
        $destinationPath = storage_path('app/public/thumbnails');

        // 2. Buat folder tujuan jika belum ada
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // 3. Pastikan folder sumber ada sebelum melanjutkan
        if (!File::exists($sourcePath)) {
            $this->command->info('Folder sumber untuk thumbnail seeder tidak ditemukan. Lewati ArticleSeeder.');
            return; // Hentikan seeder jika folder gambar tidak ada
        }

        // 4. Ambil semua file gambar dari folder sumber
        $thumbnails = File::files($sourcePath);

        foreach ($thumbnails as $thumbnail) {
            $fileName = $thumbnail->getFilename();
            $destinationFile = $destinationPath . '/' . $fileName;

            // 5. Salin file hanya jika belum ada di tujuan
            if (!File::exists($destinationFile)) {
                File::copy($thumbnail->getPathname(), $destinationFile);
            }

            // 6. Buat atau update record artikel di database
            // Ini mencegah duplikat jika seeder dijalankan lagi
            Article::updateOrCreate(
                ['thumbnail' => 'thumbnails/' . $fileName], // Kondisi untuk mencari
                [
                    'title' => Str::title(str_replace(['-', '_'], ' ', pathinfo($fileName, PATHINFO_FILENAME))),
                    'slug' => Str::slug(pathinfo($fileName, PATHINFO_FILENAME)),
                    'content' => 'Ini adalah konten default yang dibuat secara otomatis oleh seeder. Silakan ganti dengan konten yang sebenarnya.',
                    'category' => 'Berita', // Ganti dengan kategori default
                    'status' => 'approved',
                    'visit_count' => rand(10, 500), // Jumlah pengunjung acak
                ]
            );
        }
    }
}