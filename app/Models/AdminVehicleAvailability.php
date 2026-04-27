<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function getResolvedImageUrlAttribute(): ?string
    {
        $candidate = trim((string) ($this->image_url ?? ''));
        if ($candidate === '') {
            return null;
        }

        if (Str::startsWith($candidate, ['http://', 'https://', 'data:'])) {
            return $candidate;
        }

        if (Str::startsWith($candidate, '/storage/')) {
            return asset(ltrim($candidate, '/'));
        }

        if (Str::startsWith($candidate, 'storage/')) {
            return asset($candidate);
        }

        $normalized = ltrim($candidate, '/');
        if (Str::startsWith($normalized, 'public/')) {
            $normalized = ltrim(substr($normalized, strlen('public/')), '/');
        }

        if ($normalized === '') {
            return null;
        }

        try {
            return Storage::url($normalized);
        } catch (\Throwable) {
            return asset('storage/' . $normalized);
        }
    }
}
