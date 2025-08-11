<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            if (Schema::hasColumn('maps', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });
    }

};
