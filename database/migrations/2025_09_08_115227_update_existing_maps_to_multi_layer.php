<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update semua map existing menjadi multi_layer
        DB::table('maps')->update([
            'map_type' => 'multi_layer',
            'is_active' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke single_layer
        DB::table('maps')->update([
            'map_type' => 'single_layer'
        ]);
    }
};