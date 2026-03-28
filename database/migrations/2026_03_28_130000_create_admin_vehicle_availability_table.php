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
        if (!Schema::hasTable('admin_vehicle_availability')) {
            Schema::create('admin_vehicle_availability', function (Blueprint $table) {
                $table->id();
                $table->string('vehicle_code')->unique();
                $table->string('vehicle_type');
                $table->string('capacity_label')->nullable();
                $table->string('driver_name')->nullable();
                $table->string('status')->default('Available');
                $table->text('image_url')->nullable();
                $table->string('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_vehicle_availability');
    }
};
