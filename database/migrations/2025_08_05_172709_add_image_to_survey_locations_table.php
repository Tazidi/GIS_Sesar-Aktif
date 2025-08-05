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
        Schema::table('survey_locations', function (Blueprint $table) {
            // Menambahkan kolom untuk menyimpan nama file gambar
            // Dibuat nullable karena mungkin ada data lama yang tidak punya gambar
            $table->string('image')->nullable()->after('deskripsi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('survey_locations', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn('image');
        });
    }
};
