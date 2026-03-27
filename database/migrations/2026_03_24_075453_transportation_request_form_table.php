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
        Schema::create('transportation_requests_forms', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('form_id')->unique(); // e.g., REQ-2024-0892
            $table->date('request_date'); // Date of request
            $table->string('requested_by'); // Division/Personnel using the vehicle
            $table->string('destination');
            $table->dateTime('date_time_from');
            $table->dateTime('date_time_to');
            $table->text('purpose')->nullable();

            $table->string('vehicle_type');
            $table->unsignedSmallInteger('vehicle_quantity')->default(1);

            // Requesting business passengers info as JSON
            $table->json('business_passengers')->nullable();

            // Requesting Division personnel info as JSON
            $table->json('division_personnel')->nullable();
            // Example: [{"name":"John Doe","id_number":"123456"}]

            // Dispatch certification
            $table->string('vehicle_id')->nullable();
            $table->string('driver_name')->nullable();

            // Attachments (optional)
            $table->json('attachments')->nullable();
            // Example: [{"file_name":"trip_plan.pdf","file_path":"storage/attachments/trip_plan.pdf"}]

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportation_requests_forms');
    }
};
