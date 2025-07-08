<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah kolom 'icon_url' menjadi string (dari enum)
        Schema::table('maps', function (Blueprint $table) {
            $table->string('icon_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika perlu rollback ke enum lama, pastikan daftarnya sesuai
        Schema::table('maps', function (Blueprint $table) {
            $table->enum('icon_url', [
                'https://cdn.jsdelivr.net/npm/@vectorial1024/leaflet-color-markers/img/marker-icon-2x-green.png',
                'https://cdn.jsdelivr.net/npm/@vectorial1024/leaflet-color-markers/img/marker-icon-2x-yellow.png',
                'https://cdn.jsdelivr.net/npm/@vectorial1024/leaflet-color-markers/img/marker-icon-2x-red.png'
            ])->nullable()->change();
        });
    }
};
