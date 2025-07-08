<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('layer_map', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layer_id')->constrained('layers')->onDelete('cascade');
            $table->foreignId('map_id')->constrained('maps')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('layer_map');
    }
};
