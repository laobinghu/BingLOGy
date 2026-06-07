<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upload_policies', function (Blueprint $table) {
            $table->dropColumn(['disk', 'disk_config']);
        });
    }

    public function down(): void
    {
        Schema::table('upload_policies', function (Blueprint $table) {
            $table->string('disk')->default('public')->after('label');
            $table->json('disk_config')->default('{}')->after('disk');
        });
    }
};
