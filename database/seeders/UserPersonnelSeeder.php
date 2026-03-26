<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserPersonnelSeeder extends Seeder
{
    /**
     * Seed users used by the transportation request personnel section.
     */
    public function run(): void
    {
        $users = [
            [
                'personnel_id' => '100001',
                'name' => 'Eduardo Santos',
                'email' => 'eduardo.santos@nia.local',
            ],
            [
                'personnel_id' => '100002',
                'name' => 'Ricardo Dela Cruz',
                'email' => 'ricardo.delacruz@nia.local',
            ],
            [
                'personnel_id' => '100003',
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@nia.local',
            ],
            [
                'personnel_id' => '100004',
                'name' => 'Juan Bautista',
                'email' => 'juan.bautista@nia.local',
            ],
            [
                'personnel_id' => '100005',
                'name' => 'Ramon Reyes',
                'email' => 'ramon.reyes@nia.local',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['personnel_id' => $userData['personnel_id']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
