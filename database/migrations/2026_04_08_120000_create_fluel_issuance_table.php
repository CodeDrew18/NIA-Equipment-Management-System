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
        Schema::create('fuel_issuance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transportation_request_form_id')
                ->constrained('transportation_requests_forms')
                ->cascadeOnDelete();

            $table->string('copy_key', 64);
            $table->unsignedSmallInteger('copy_number')->default(1);
            $table->string('ctrl_number', 120);

            $table->string('vehicle_id')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('dealer');

            $table->decimal('gasoline_quantity', 12, 2)->default(0);
            $table->decimal('gasoline_price', 12, 2)->default(0);
            $table->decimal('diesel_quantity', 12, 2)->default(0);
            $table->decimal('diesel_price', 12, 2)->default(0);
            $table->decimal('fuel_save_quantity', 12, 2)->default(0);
            $table->decimal('fuel_save_price', 12, 2)->default(0);
            $table->decimal('v_power_quantity', 12, 2)->default(0);
            $table->decimal('v_power_price', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);

            $table->json('request_form_data')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();

            $table->unique(['transportation_request_form_id', 'copy_key'], 'fuel_issuance_request_copy_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_issuance');
    }
};
