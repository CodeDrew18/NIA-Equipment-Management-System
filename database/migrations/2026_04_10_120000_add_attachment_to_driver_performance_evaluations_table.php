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
        if (Schema::hasTable('driver_performance_evaluations') && !Schema::hasColumn('driver_performance_evaluations', 'attachment')) {
            Schema::table('driver_performance_evaluations', function (Blueprint $table) {
                $table->json('attachment')->nullable()->after('evaluation_payload');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('driver_performance_evaluations') && Schema::hasColumn('driver_performance_evaluations', 'attachment')) {
            Schema::table('driver_performance_evaluations', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }
    }
};
