<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Modifikasi tabel map_features
        Schema::table('map_features', function (Blueprint $table) {
            // Hapus foreign key lama jika ada
            // Nama constraint bisa berbeda, cek database Anda. Biasanya: 'map_features_map_id_foreign'
            $table->dropForeign(['map_id']); 
            
            $table->dropColumn('map_id'); // Hapus kolom map_id

            // Tambahkan kolom layer_id
            $table->foreignId('layer_id')->after('id')->constrained('layers')->onDelete('cascade');
        });

        // 2. Hapus tabel pivot 'feature_layer' yang tidak lagi diperlukan
        Schema::dropIfExists('feature_layer');
    }

    public function down()
    {
        // Logika untuk rollback jika diperlukan
        Schema::table('map_features', function (Blueprint $table) {
            $table->dropForeign(['layer_id']);
            $table->dropColumn('layer_id');
            $table->foreignId('map_id')->constrained('maps')->onDelete('cascade');
        });

        // Buat kembali tabel feature_layer jika di-rollback
        // (Isi dengan skema lama jika perlu)
    }
};