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
        if (!Schema::hasColumn('transportation_requests_forms', 'business_passengers')) {
            Schema::table('transportation_requests_forms', function (Blueprint $table) {
                $table->json('business_passengers')->nullable()->after('vehicle_quantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('transportation_requests_forms', 'business_passengers')) {
            Schema::table('transportation_requests_forms', function (Blueprint $table) {
                $table->dropColumn('business_passengers');
            });
        }
    }
};
