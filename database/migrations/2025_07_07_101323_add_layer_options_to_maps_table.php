<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->string('layer_type')->nullable()->after('feature_type');
            $table->string('stroke_color')->nullable()->after('layer_type');
            $table->string('fill_color')->nullable()->after('stroke_color');
            $table->float('opacity')->nullable()->after('fill_color');
            $table->integer('weight')->nullable()->after('opacity');
            $table->integer('radius')->nullable()->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn([
                'layer_type',
                'stroke_color',
                'fill_color',
                'opacity',
                'weight',
                'radius'
            ]);
        });
    }
};
