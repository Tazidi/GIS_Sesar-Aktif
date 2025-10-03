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
        Schema::table('feature_layer', function (Blueprint $table) {
            $table->string('layer_type')->nullable();
            $table->string('stroke_color', 7)->nullable();
            $table->string('fill_color', 7)->nullable();
            $table->integer('weight')->nullable();
            $table->float('opacity', 2, 1)->nullable();
            $table->integer('radius')->nullable();
            $table->string('icon_url')->nullable();
            // Anda mungkin juga butuh lat & lng jika ingin menyimpannya di sini
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_layer', function (Blueprint $table) {
            //
        });
    }
};
