<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportationRequestFormModel extends Model
{
    use HasFactory;

    protected $table = 'transportation_requests_forms';

    protected $fillable = [
        'form_id',
        'request_date',
        'requested_by',
        'destination',
        'date_time_from',
        'date_time_to',
        'purpose',
        'vehicle_type',
        'vehicle_quantity',
        'business_passengers',
        'division_personnel',
        'vehicle_id',
        'driver_name',
        'attachments',
        'status',
        'generated_filename',
    ];

    protected $casts = [
        'request_date' => 'date',
        'date_time_from' => 'datetime',
        'date_time_to' => 'datetime',
        'business_passengers' => 'array',
        'division_personnel' => 'array',
        'attachments' => 'array',
    ];

    public function getRequestorNameAttribute(): string
    {
        $personnel = $this->division_personnel;

        if (is_array($personnel) && isset($personnel[0]['name']) && $personnel[0]['name'] !== '') {
            return (string) $personnel[0]['name'];
        }

        return (string) $this->requested_by;
    }

    public function getRequestorPositionAttribute(): string
    {
        $personnel = $this->division_personnel;

        if (is_array($personnel) && isset($personnel[0]['position']) && $personnel[0]['position'] !== '') {
            return (string) $personnel[0]['position'];
        }

        return 'N/A';
    }
}
