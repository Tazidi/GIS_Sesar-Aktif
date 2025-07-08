<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->foreignId('layer_id')->nullable()->constrained('layers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropForeign(['layer_id']);
            $table->dropColumn('layer_id');
        });
    }
};

