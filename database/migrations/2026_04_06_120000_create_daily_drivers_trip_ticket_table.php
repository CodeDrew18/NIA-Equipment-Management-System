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
        Schema::create('daily_drivers_trip_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transportation_request_form_id')
                ->constrained('transportation_requests_forms')
                ->cascadeOnDelete();

            $table->json('request_form_data')->nullable();

            $table->dateTime('departure_time')->nullable();
            $table->dateTime('arrival_time_destination')->nullable();
            $table->dateTime('departure_time_destination')->nullable();
            $table->dateTime('arrival_time_office')->nullable();

            $table->decimal('odometer_end', 12, 2)->nullable();
            $table->decimal('odometer_start', 12, 2)->nullable();
            $table->decimal('distance_travelled', 12, 2)->nullable();

            $table->decimal('fuel_balance_before', 12, 2)->nullable();
            $table->decimal('fuel_issued_regional', 12, 2)->nullable();
            $table->decimal('fuel_purchased_trip', 12, 2)->nullable();
            $table->decimal('fuel_issued_nia', 12, 2)->nullable();
            $table->decimal('fuel_total', 12, 2)->nullable();
            $table->decimal('fuel_used', 12, 2)->nullable();
            $table->decimal('fuel_balance_after', 12, 2)->nullable();

            $table->decimal('gear_oil_liters', 12, 2)->nullable();
            $table->decimal('engine_oil_liters', 12, 2)->nullable();
            $table->decimal('grease_kgs', 12, 2)->nullable();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique('transportation_request_form_id', 'daily_dtt_request_form_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_drivers_trip_ticket');
    }
};
