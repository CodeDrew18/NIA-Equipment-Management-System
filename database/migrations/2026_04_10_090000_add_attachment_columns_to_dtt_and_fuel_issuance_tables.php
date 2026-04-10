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
        if (Schema::hasTable('daily_drivers_trip_ticket') && !Schema::hasColumn('daily_drivers_trip_ticket', 'attachment')) {
            Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
                $table->json('attachment')->nullable()->after('request_form_data');
            });
        }

        if (Schema::hasTable('fuel_issuance') && !Schema::hasColumn('fuel_issuance', 'attachment')) {
            Schema::table('fuel_issuance', function (Blueprint $table) {
                $table->json('attachment')->nullable()->after('request_form_data');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('daily_drivers_trip_ticket') && Schema::hasColumn('daily_drivers_trip_ticket', 'attachment')) {
            Schema::table('daily_drivers_trip_ticket', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }

        if (Schema::hasTable('fuel_issuance') && Schema::hasColumn('fuel_issuance', 'attachment')) {
            Schema::table('fuel_issuance', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }
    }
};
