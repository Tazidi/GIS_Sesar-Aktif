<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapFeaturesTable extends Migration
{
    public function up()
    {
        Schema::create('map_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->onDelete('cascade');
            $table->json('geometry');
            $table->json('properties')->nullable();
            $table->string('image_path')->nullable(); // Gambar untuk fitur ini
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('map_features');
    }
}
