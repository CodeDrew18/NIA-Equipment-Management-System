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
        Schema::table('transportation_requests_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('transportation_requests_forms', 'status')) {
                $table->string('status')->default('Pending')->after('attachments');
            }

            if (!Schema::hasColumn('transportation_requests_forms', 'generated_filename')) {
                $table->string('generated_filename')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transportation_requests_forms', function (Blueprint $table) {
            if (Schema::hasColumn('transportation_requests_forms', 'generated_filename')) {
                $table->dropColumn('generated_filename');
            }

            if (Schema::hasColumn('transportation_requests_forms', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
