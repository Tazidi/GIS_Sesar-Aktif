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
        Schema::table('map_features', function (Blueprint $table) {
            // Tambahkan kolom 'caption' setelah 'image_path'
            $table->string('caption')->nullable()->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map_features', function (Blueprint $table) {
            $table->dropColumn('caption');
        });
    }
};