<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Menambahkan kolom string bernama 'thumbnail' yang boleh kosong (nullable)
            // Diletakkan setelah kolom 'content'
            $table->string('thumbnail')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Cara untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('thumbnail');
        });
    }
};