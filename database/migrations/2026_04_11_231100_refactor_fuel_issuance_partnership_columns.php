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
        if (!Schema::hasTable('fuel_issuance')) {
            return;
        }

        Schema::table('fuel_issuance', function (Blueprint $table) {
            if (!Schema::hasColumn('fuel_issuance', 'fuel_issuance_partnership_id')) {
                $table->foreignId('fuel_issuance_partnership_id')
                    ->nullable()
                    ->after('dealer')
                    ->constrained('fuel_issuance_partnership')
                    ->nullOnDelete();
            }
        });

        Schema::table('fuel_issuance', function (Blueprint $table) {
            if (Schema::hasColumn('fuel_issuance', 'fuel_issuance_name')) {
                $table->dropColumn('fuel_issuance_name');
            }
            if (Schema::hasColumn('fuel_issuance', 'fuel_partnership_name')) {
                $table->dropColumn('fuel_partnership_name');
            }
            if (Schema::hasColumn('fuel_issuance', 'fuel_partnership_valid_from')) {
                $table->dropColumn('fuel_partnership_valid_from');
            }
            if (Schema::hasColumn('fuel_issuance', 'fuel_partnership_valid_until')) {
                $table->dropColumn('fuel_partnership_valid_until');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('fuel_issuance')) {
            return;
        }

        Schema::table('fuel_issuance', function (Blueprint $table) {
            if (!Schema::hasColumn('fuel_issuance', 'fuel_issuance_name')) {
                $table->string('fuel_issuance_name')->nullable()->after('dealer');
            }
            if (!Schema::hasColumn('fuel_issuance', 'fuel_partnership_valid_from')) {
                $table->date('fuel_partnership_valid_from')->nullable()->after('fuel_issuance_name');
            }
            if (!Schema::hasColumn('fuel_issuance', 'fuel_partnership_valid_until')) {
                $table->date('fuel_partnership_valid_until')->nullable()->after('fuel_partnership_valid_from');
            }

            if (Schema::hasColumn('fuel_issuance', 'fuel_issuance_partnership_id')) {
                $table->dropConstrainedForeignId('fuel_issuance_partnership_id');
            }
        });
    }
};
