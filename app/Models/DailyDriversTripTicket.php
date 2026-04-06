<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyDriversTripTicket extends Model
{
    use HasFactory;

    protected $table = 'daily_drivers_trip_ticket';

    protected $fillable = [
        'transportation_request_form_id',
        'request_form_data',
        'departure_time',
        'arrival_time_destination',
        'departure_time_destination',
        'arrival_time_office',
        'odometer_end',
        'odometer_start',
        'distance_travelled',
        'fuel_balance_before',
        'fuel_issued_regional',
        'fuel_purchased_trip',
        'fuel_issued_nia',
        'fuel_total',
        'fuel_used',
        'fuel_balance_after',
        'gear_oil_liters',
        'engine_oil_liters',
        'grease_kgs',
        'remarks',
    ];

    protected $casts = [
        'request_form_data' => 'array',
        'departure_time' => 'datetime',
        'arrival_time_destination' => 'datetime',
        'departure_time_destination' => 'datetime',
        'arrival_time_office' => 'datetime',
        'odometer_end' => 'decimal:2',
        'odometer_start' => 'decimal:2',
        'distance_travelled' => 'decimal:2',
        'fuel_balance_before' => 'decimal:2',
        'fuel_issued_regional' => 'decimal:2',
        'fuel_purchased_trip' => 'decimal:2',
        'fuel_issued_nia' => 'decimal:2',
        'fuel_total' => 'decimal:2',
        'fuel_used' => 'decimal:2',
        'fuel_balance_after' => 'decimal:2',
        'gear_oil_liters' => 'decimal:2',
        'engine_oil_liters' => 'decimal:2',
        'grease_kgs' => 'decimal:2',
    ];

    public function transportationRequestForm(): BelongsTo
    {
        return $this->belongsTo(TransportationRequestFormModel::class, 'transportation_request_form_id');
    }
}
