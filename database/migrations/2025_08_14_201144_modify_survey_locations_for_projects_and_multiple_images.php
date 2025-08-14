<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_locations', function (Blueprint $table) {
            // 1. Tambahkan foreign key untuk project
            $table->foreignId('project_id')->after('user_id')->constrained()->onDelete('cascade');

            // 2. Hapus kolom 'image' yang lama
            $table->dropColumn('image');

            // 3. Tambahkan kolom 'images' dengan tipe JSON setelah 'deskripsi'
            $table->json('images')->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('survey_locations', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
            $table->dropColumn('images');
            $table->string('image')->nullable()->after('deskripsi'); // Kembalikan kolom lama
        });
    }
};
