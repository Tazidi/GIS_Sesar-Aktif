<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->string('layer_type')->nullable();
            $table->string('stroke_color')->nullable();
            $table->string('fill_color')->nullable();
            $table->float('opacity')->nullable();
            $table->float('weight')->nullable();
            $table->float('radius')->nullable();
            $table->string('icon_url')->nullable();
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
                'radius',
                'icon_url'
            ]);
        });
    }
};