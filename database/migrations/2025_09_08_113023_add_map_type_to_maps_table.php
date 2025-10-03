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
        Schema::table('maps', function (Blueprint $table) {
            // Tambahkan field untuk membedakan tipe map
            $table->enum('map_type', ['single_layer', 'multi_layer'])->default('single_layer')->after('description');
            
            // Tambahkan field untuk status aktif map
            $table->boolean('is_active')->default(true)->after('map_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn(['map_type', 'is_active']);
        });
    }
};