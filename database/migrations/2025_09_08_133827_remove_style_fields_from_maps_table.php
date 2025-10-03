<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {
            // Menghapus semua kolom yang diminta dalam satu perintah
            $table->dropColumn([
                'layer_type',
                'lat',
                'lng',
                'stroke_color',
                'fill_color',
                'weight',
                'opacity',
                'radius',
                'icon_url',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            // Menambahkan kembali kolom jika migration di-rollback
            // Catatan: Tipe data dan properti (nullable, default) di bawah ini adalah asumsi.
            // Sesuaikan dengan migration asli Anda untuk hasil terbaik.
            $table->string('layer_type')->nullable()->after('description');
            $table->double('lat')->nullable()->after('layer_type');
            $table->double('lng')->nullable()->after('lat');
            $table->string('stroke_color', 7)->nullable()->default('#3388ff')->after('lng');
            $table->string('fill_color', 7)->nullable()->default('#3388ff')->after('stroke_color');
            $table->integer('weight')->nullable()->default(3)->after('fill_color');
            $table->float('opacity', 2, 1)->nullable()->default(0.5)->after('weight');
            $table->integer('radius')->nullable()->default(300)->after('opacity');
            $table->string('icon_url')->nullable()->after('radius');
        });
    }
};