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
    $table->dateTime('date_time_used');
    $table->text('purpose')->nullable();

    // Equipment details as JSON (vehicle type, quantity)
    $table->json('equipment')->nullable();

    // Requesting Division personnel info as JSON
    $table->json('division_personnel')->nullable(); 
    // Example: [{"name":"John Doe","position":"Manager","id_number":"12345"}]

    // Dispatch certification
    $table->string('vehicle_id')->nullable();
    $table->string('driver_name')->nullable();

    // Attachments (optional)
    $table->json('attachments')->nullable(); 
    // Example: [{"file_name":"trip_plan.pdf","file_path":"storage/attachments/trip_plan.pdf"}]

    // Approval Workflow (optional JSON)
    $table->json('approval_workflow')->nullable(); 
    // Example: {"approved_by":"Division Manager","status":"approved","signed_at":"2024-10-24 10:00:00"}

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
