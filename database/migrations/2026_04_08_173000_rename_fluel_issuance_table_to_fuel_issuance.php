<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('fluel_issuance') && !Schema::hasTable('fuel_issuance')) {
            Schema::rename('fluel_issuance', 'fuel_issuance');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fuel_issuance') && !Schema::hasTable('fluel_issuance')) {
            Schema::rename('fuel_issuance', 'fluel_issuance');
        }
    }
};
