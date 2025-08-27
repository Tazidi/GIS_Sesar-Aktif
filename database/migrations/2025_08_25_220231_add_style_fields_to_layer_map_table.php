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
        Schema::table('layer_map', function (Blueprint $table) {
            // Tambahkan semua kolom gaya yang diperlukan
            $table->string('layer_type')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 11, 7)->nullable();
            $table->string('stroke_color', 7)->nullable(); // #RRGGBB
            $table->string('fill_color', 7)->nullable();   // #RRGGBB
            $table->unsignedTinyInteger('weight')->nullable();
            $table->decimal('opacity', 2, 1)->nullable(); // e.g., 0.8
            $table->unsignedInteger('radius')->nullable();
            $table->string('icon_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layer_map', function (Blueprint $table) {
            // Hapus kolom jika migrasi di-rollback
            $table->dropColumn([
                'layer_type',
                'lat',
                'lng',
                'stroke_color',
                'fill_color',
                'weight',
                'opacity',
                'radius',
                'icon_url'
            ]);
        });
    }
};