<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transportation_requests_forms', 'vehicle_driver_map')) {
            Schema::table('transportation_requests_forms', function (Blueprint $table) {
                $table->json('vehicle_driver_map')->nullable()->after('driver_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transportation_requests_forms', 'vehicle_driver_map')) {
            Schema::table('transportation_requests_forms', function (Blueprint $table) {
                $table->dropColumn('vehicle_driver_map');
            });
        }
    }
};
