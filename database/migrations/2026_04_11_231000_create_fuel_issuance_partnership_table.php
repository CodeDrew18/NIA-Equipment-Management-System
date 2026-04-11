<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fuel_issuance_partnership')) {
            Schema::create('fuel_issuance_partnership', function (Blueprint $table) {
                $table->id();
                $table->string('partnership_name', 255);
                $table->date('valid_from');
                $table->date('valid_until');
                $table->decimal('gasoline_price_per_liter', 12, 2)->default(0);
                $table->decimal('diesel_price_per_liter', 12, 2)->default(0);
                $table->decimal('fuel_save_price_per_liter', 12, 2)->default(0);
                $table->decimal('v_power_price_per_liter', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $exists = DB::table('fuel_issuance_partnership')
            ->where('partnership_name', 'Petron Fuel')
            ->exists();

        if (!$exists) {
            DB::table('fuel_issuance_partnership')->insert([
                'partnership_name' => 'Petron Fuel',
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addYear()->toDateString(),
                'gasoline_price_per_liter' => 0,
                'diesel_price_per_liter' => 0,
                'fuel_save_price_per_liter' => 0,
                'v_power_price_per_liter' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_issuance_partnership');
    }
};
