<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn(['layer', 'layer_type', 'feature_type', 'radius']);
        });
    }

    public function down(): void {
        Schema::table('maps', function (Blueprint $table) {
            $table->string('layer')->nullable();
            $table->string('layer_type')->nullable();
            $table->string('feature_type')->nullable();
            $table->float('radius')->nullable();
        });
    }
};
