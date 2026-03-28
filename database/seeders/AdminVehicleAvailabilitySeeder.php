<?php

namespace Database\Seeders;

use App\Models\AdminVehicleAvailability;
use Illuminate\Database\Seeder;

class AdminVehicleAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'vehicle_code' => 'NIA-CO-001',
                'vehicle_type' => 'Coaster',
                'capacity_label' => '29 Seater',
                'driver_name' => 'Eduardo Santos',
                'status' => 'Available',
            ],
            [
                'vehicle_code' => 'NIA-CO-002',
                'vehicle_type' => 'Coaster',
                'capacity_label' => '29 Seater',
                'driver_name' => 'Maintenance Staff',
                'status' => 'Maintenance',
            ],
            [
                'vehicle_code' => 'NIA-VN-001',
                'vehicle_type' => 'Van',
                'capacity_label' => '12 Seater',
                'driver_name' => 'Ricardo Dela Cruz',
                'status' => 'On Business Trip',
            ],
            [
                'vehicle_code' => 'NIA-VN-002',
                'vehicle_type' => 'Van',
                'capacity_label' => '12 Seater',
                'driver_name' => 'Maria Garcia',
                'status' => 'Available',
            ],
            [
                'vehicle_code' => 'NIA-PU-001',
                'vehicle_type' => 'Pickup',
                'capacity_label' => '5 Seater',
                'driver_name' => 'Juan Bautista',
                'status' => 'Reserved',
            ],
            [
                'vehicle_code' => 'NIA-PU-002',
                'vehicle_type' => 'Pickup',
                'capacity_label' => '5 Seater',
                'driver_name' => 'Ramon Reyes',
                'status' => 'Available',
            ],
        ];

        foreach ($vehicles as $vehicle) {
            AdminVehicleAvailability::query()->updateOrCreate(
                ['vehicle_code' => $vehicle['vehicle_code']],
                $vehicle
            );
        }
    }
}
