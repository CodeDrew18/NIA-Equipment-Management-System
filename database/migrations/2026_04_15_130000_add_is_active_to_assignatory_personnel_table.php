<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('assignatory_personnel')) {
            return;
        }

        if (!Schema::hasColumn('assignatory_personnel', 'is_active')) {
            Schema::table('assignatory_personnel', function (Blueprint $table) {
                $table->boolean('is_active')->default(false)->after('position');
            });
        }

        $hasActive = DB::table('assignatory_personnel')
            ->where('is_active', true)
            ->exists();

        if ($hasActive) {
            return;
        }

        $latestId = DB::table('assignatory_personnel')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('id');

        if ($latestId !== null) {
            DB::table('assignatory_personnel')
                ->where('id', $latestId)
                ->update(['is_active' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('assignatory_personnel')) {
            return;
        }

        if (Schema::hasColumn('assignatory_personnel', 'is_active')) {
            Schema::table('assignatory_personnel', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
