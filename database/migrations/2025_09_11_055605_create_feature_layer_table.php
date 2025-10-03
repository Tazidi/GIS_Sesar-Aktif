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
        Schema::create('feature_layer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('map_features')->onDelete('cascade');
            $table->foreignId('layer_id')->constrained('layers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_layer');
    }
};
