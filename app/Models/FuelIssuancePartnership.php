<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelIssuancePartnership extends Model
{
    use HasFactory;

    protected $table = 'fuel_issuance_partnership';

    protected $fillable = [
        'partnership_name',
        'valid_from',
        'valid_until',
        'gasoline_price_per_liter',
        'diesel_price_per_liter',
        'fuel_save_price_per_liter',
        'v_power_price_per_liter',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'gasoline_price_per_liter' => 'decimal:2',
        'diesel_price_per_liter' => 'decimal:2',
        'fuel_save_price_per_liter' => 'decimal:2',
        'v_power_price_per_liter' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function fuelIssuances(): HasMany
    {
        return $this->hasMany(FuelIssuance::class, 'fuel_issuance_partnership_id');
    }
}
