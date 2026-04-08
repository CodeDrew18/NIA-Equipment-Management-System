<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelIssuance extends Model
{
    use HasFactory;

    protected $table = 'fuel_issuance';

    protected $fillable = [
        'transportation_request_form_id',
        'copy_key',
        'copy_number',
        'ctrl_number',
        'vehicle_id',
        'driver_name',
        'dealer',
        'gasoline_quantity',
        'gasoline_price',
        'diesel_quantity',
        'diesel_price',
        'fuel_save_quantity',
        'fuel_save_price',
        'v_power_quantity',
        'v_power_price',
        'total_amount',
        'request_form_data',
        'dispatched_at',
    ];

    protected $casts = [
        'copy_number' => 'integer',
        'gasoline_quantity' => 'decimal:2',
        'gasoline_price' => 'decimal:2',
        'diesel_quantity' => 'decimal:2',
        'diesel_price' => 'decimal:2',
        'fuel_save_quantity' => 'decimal:2',
        'fuel_save_price' => 'decimal:2',
        'v_power_quantity' => 'decimal:2',
        'v_power_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'request_form_data' => 'array',
        'dispatched_at' => 'datetime',
    ];

    public function transportationRequestForm(): BelongsTo
    {
        return $this->belongsTo(TransportationRequestFormModel::class, 'transportation_request_form_id');
    }
}
