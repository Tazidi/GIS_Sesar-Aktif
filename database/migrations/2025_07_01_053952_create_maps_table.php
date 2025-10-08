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
        Schema::create('maps', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100); // Disesuaikan dari VARCHAR(100)
            $table->text('description')->nullable();
            $table->string('layer', 50)->nullable(); // VARCHAR(50), nullable jika tidak selalu diisi

            $table->enum('feature_type', ['point', 'line', 'polygon']); // ENUM

            $table->decimal('lat', 10, 6)->nullable();  // DECIMAL(10,6)
            $table->decimal('lng', 10, 6)->nullable(); // DECIMAL(10,6)

            $table->float('distance')->nullable();     // FLOAT
            $table->string('image_path', 255)->nullable(); // VARCHAR(255)

            $table->enum('icon_url', [
                'public/marker/marker_hijau.png',
                'public/marker/marker_kuning.png',
                'public/marker/marker_merah.png'
            ])->nullable(); // ENUM, dibuat nullable jika tidak selalu wajib
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maps');
    }
};
