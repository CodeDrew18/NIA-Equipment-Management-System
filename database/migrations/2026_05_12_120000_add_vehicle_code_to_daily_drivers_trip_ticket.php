<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('daily_drivers_trip_ticket', 'assigned_vehicle_code')) {
            Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
                $table->string('assigned_vehicle_code')->nullable()->after('assigned_driver_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('daily_drivers_trip_ticket', 'assigned_vehicle_code')) {
            Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
                $table->dropColumn('assigned_vehicle_code');
            });
        }
    }
};
