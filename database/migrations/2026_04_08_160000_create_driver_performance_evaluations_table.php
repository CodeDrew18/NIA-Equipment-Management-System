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
        Schema::create('driver_performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transportation_request_form_id');
            $table->foreign('transportation_request_form_id', 'drv_perf_eval_trf_fk')
                ->references('id')
                ->on('transportation_requests_forms')
                ->cascadeOnDelete();

            $table->string('driver_name', 255);
            $table->string('status', 40)->default('Pending');
            $table->decimal('overall_rating', 4, 2)->nullable();
            $table->unsignedTinyInteger('timeliness_score')->nullable();
            $table->unsignedTinyInteger('safety_score')->nullable();
            $table->unsignedTinyInteger('compliance_score')->nullable();
            $table->string('evaluator_name', 255)->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->unique('transportation_request_form_id', 'driver_perf_eval_request_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_performance_evaluations');
    }
};
