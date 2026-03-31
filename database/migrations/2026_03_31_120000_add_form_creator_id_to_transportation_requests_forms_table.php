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
            $table->string('form_creator_id', 6)
                ->nullable()
                ->after('form_id');

            $table->foreign('form_creator_id')
                ->references('personnel_id')
                ->on('users')
                ->nullOnDelete();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transportation_requests_forms', function (Blueprint $table) {
            $table->dropForeign(['form_creator_id']);
            $table->dropColumn('form_creator_id');
        });
    }
};
