<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('map_features', function (Blueprint $table) {
            $table->text('technical_info')->nullable();
        });
    }

    public function down()
    {
        Schema::table('map_features', function (Blueprint $table) {
            $table->dropColumn('technical_info');
        });
    }

};
