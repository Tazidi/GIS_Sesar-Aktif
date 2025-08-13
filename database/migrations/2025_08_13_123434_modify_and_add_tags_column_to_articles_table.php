<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            // 1. Ubah nama kolom 'tags' yang ada menjadi 'category'
            $table->renameColumn('tags', 'category');

            // 2. Tambahkan kolom 'tags' yang baru untuk hashtags setelah kolom 'category'
            $table->string('tags')->nullable()->after('category')->comment('Comma-separated tags for hashtags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            // Balikkan proses dalam urutan terbalik

            // 1. Hapus kolom 'tags' yang baru
            $table->dropColumn('tags');

            // 2. Kembalikan nama kolom 'category' menjadi 'tags'
            $table->renameColumn('category', 'tags');
        });
    }
};