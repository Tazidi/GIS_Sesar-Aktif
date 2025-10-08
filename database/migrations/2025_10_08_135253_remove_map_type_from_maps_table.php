<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn('map_type'); // Hapus kolom map_type
        });
    }

    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            // Untuk jaga-jaga jika perlu rollback
            $table->string('map_type')->nullable()->after('description');
        });
    }
};