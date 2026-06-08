<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
            $table->string('color', 7)->nullable()->after('description');
            $table->string('icon')->nullable()->after('color');
            $table->integer('sort_order')->default(0)->after('icon');
            $table->integer('posts_count')->default(0)->after('sort_order');
            $table->json('meta')->nullable()->after('posts_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn(['description', 'color', 'icon', 'sort_order', 'posts_count', 'meta']);
        });
    }
};
