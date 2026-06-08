<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->string('period'); // daily, weekly, monthly
            $table->date('period_start');
            $table->unsignedInteger('post_count')->default(0);
            $table->unsignedInteger('searches_count')->default(0);
            $table->timestamps();

            $table->unique(['tag_id', 'period', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_analytics');
    }
};
