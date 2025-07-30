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
        // This function is executed when you run the 'php artisan migrate' command.
        // We are adding a new column named 'tags' to the 'articles' table.
        Schema::table('articles', function (Blueprint $table) {
            // Adds a 'tags' column of type STRING.
            // It can be nullable, meaning it's okay if an article doesn't have tags.
            // We place it after the 'content' column for better organization in the database structure.
            $table->string('tags')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This function is executed when you run 'php artisan migrate:rollback'.
        // It's the reverse of the 'up' method. Here, we remove the 'tags' column.
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
