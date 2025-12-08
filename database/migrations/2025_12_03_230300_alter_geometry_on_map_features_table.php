<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_features', function (Blueprint $table) {
            // butuh: composer require doctrine/dbal
            $table->longText('geometry')->change();

            // pastikan kolom lain ada (opsional, kalau belum ada)
            if (! Schema::hasColumn('map_features', 'properties')) {
                $table->json('properties')->nullable();
            }

            if (! Schema::hasColumn('map_features', 'image_path')) {
                $table->string('image_path')->nullable();
            }

            if (! Schema::hasColumn('map_features', 'caption')) {
                $table->text('caption')->nullable();
            }

            if (! Schema::hasColumn('map_features', 'technical_info')) {
                $table->json('technical_info')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('map_features', function (Blueprint $table) {
            // sesuaikan dengan tipe lama kalau mau benar-benar rollback
            // $table->text('geometry')->change();
            // $table->dropColumn(['properties', 'image_path', 'caption', 'technical_info']);
        });
    }
};
