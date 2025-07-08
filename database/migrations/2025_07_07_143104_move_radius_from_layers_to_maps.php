<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('layers', function (Blueprint $table) {
            $table->dropColumn('radius');
        });

        Schema::table('maps', function (Blueprint $table) {
            $table->integer('radius')->nullable()->after('weight'); // tambahkan setelah kolom 'weight'
        });
    }

    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn('radius');
        });

        Schema::table('layers', function (Blueprint $table) {
            $table->integer('radius')->nullable();
        });
    }
};
