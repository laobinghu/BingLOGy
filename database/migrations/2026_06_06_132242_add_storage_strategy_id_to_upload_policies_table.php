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
        Schema::table('upload_policies', function (Blueprint $table) {
            $table->foreignId('storage_strategy_id')
                ->nullable()
                ->after('disk_config')
                ->constrained('storage_strategies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('upload_policies', function (Blueprint $table) {
            $table->dropForeign(['storage_strategy_id']);
            $table->dropColumn('storage_strategy_id');
        });
    }
};
