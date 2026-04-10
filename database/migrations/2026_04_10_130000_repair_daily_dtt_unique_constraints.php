<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM daily_drivers_trip_ticket'))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->all();

        if (in_array('daily_dtt_request_form_unique', $indexes, true)) {
            DB::statement('ALTER TABLE daily_drivers_trip_ticket DROP INDEX daily_dtt_request_form_unique');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM daily_drivers_trip_ticket'))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->all();

        if (!in_array('daily_dtt_request_form_unique', $indexes, true)) {
            DB::statement('ALTER TABLE daily_drivers_trip_ticket ADD UNIQUE daily_dtt_request_form_unique (transportation_request_form_id)');
        }
    }
};
