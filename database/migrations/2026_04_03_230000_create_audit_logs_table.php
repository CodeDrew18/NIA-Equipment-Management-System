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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('personnel_id', 20)->nullable();
            $table->string('user_name')->nullable();
            $table->string('action_category', 60);
            $table->text('activity_description');
            $table->string('method', 10)->nullable();
            $table->string('route_name')->nullable();
            $table->string('request_path')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('status', 20)->default('SUCCESS');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('route_name');
            $table->index('status');
            $table->index('personnel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
