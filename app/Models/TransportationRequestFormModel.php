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
        'division_personnel',
        'vehicle_id',
        'driver_name',
        'attachments',
    ];

    protected $casts = [
        'request_date' => 'date',
        'date_time_from' => 'datetime',
        'date_time_to' => 'datetime',
        'division_personnel' => 'array',
        'attachments' => 'array',
    ];
}
