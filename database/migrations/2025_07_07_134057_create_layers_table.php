<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('layers', function (Blueprint $table) {
            $table->id();
            $table->string('nama_layer');
            $table->text('deskripsi')->nullable();
            $table->enum('layer_type', ['marker', 'polyline', 'polygon', 'circle'])->default('marker');
            $table->integer('radius')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layers');
    }
};

