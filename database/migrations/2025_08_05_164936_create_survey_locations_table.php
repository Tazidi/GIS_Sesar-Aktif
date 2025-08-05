<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('survey_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // surveyor
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->json('geometry'); // lat long
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_locations');
    }
}

