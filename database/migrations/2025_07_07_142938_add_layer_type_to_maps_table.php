<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->enum('layer_type', ['marker', 'polyline', 'polygon', 'circle'])->nullable();
        });
    }

    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn('layer_type');
        });
    }
};
