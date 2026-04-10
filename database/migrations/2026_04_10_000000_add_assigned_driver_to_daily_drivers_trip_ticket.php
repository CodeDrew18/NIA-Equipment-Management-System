<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add column to track which driver this DTT belongs to (if it doesn't exist)
        if (!Schema::hasColumn('daily_drivers_trip_ticket', 'assigned_driver_name')) {
            Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
                $table->string('assigned_driver_name')->nullable()->after('transportation_request_form_id');
            });
        }

        $indexes = collect(DB::select('SHOW INDEX FROM daily_drivers_trip_ticket'))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->all();

        // Ensure composite unique exists first, so MySQL can keep FK support when old index is removed.
        if (!in_array('daily_dtt_request_driver_unique', $indexes, true)) {
            Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
                $table->unique(['transportation_request_form_id', 'assigned_driver_name'], 'daily_dtt_request_driver_unique');
            });
        }

        if (in_array('daily_dtt_request_form_unique', $indexes, true)) {
            DB::statement('ALTER TABLE daily_drivers_trip_ticket DROP INDEX daily_dtt_request_form_unique');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
            $table->dropUnique('daily_dtt_request_driver_unique');
        });

        Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
            $table->unique('transportation_request_form_id', 'daily_dtt_request_form_unique');
        });

        Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
            $table->dropColumn('assigned_driver_name');
        });
    }
};
