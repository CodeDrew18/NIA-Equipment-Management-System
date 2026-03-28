<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminVehicleAvailability extends Model
{
    use HasFactory;

    protected $table = 'admin_vehicle_availability';

    protected $fillable = [
        'vehicle_code',
        'vehicle_type',
        'capacity_label',
        'driver_name',
        'status',
        'image_url',
        'remarks',
    ];
}
