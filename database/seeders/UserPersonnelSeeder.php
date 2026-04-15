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
                'name' => 'System Admin',
                'email' => 'admin@nia.local',
                'role' => 'admin',
            ],
            [
                'personnel_id' => '100002',
                'name' => 'Eduardo Malubag',
                'email' => 'chief.motorpool@nia.local',
                'role' => 'chief_of_motorpool_section',
            ],

            [
                'personnel_id' => '100003',
                'name' => 'Ricardo Dela Cruz',
                'email' => 'operator1@nia.local',
                'role' => 'operator',
            ],
            [
                'personnel_id' => '100004',
                'name' => 'Antonio Ramos',
                'email' => 'operator2@nia.local',
                'role' => 'operator',
            ],
            [
                'personnel_id' => '100005',
                'name' => 'Joel Mendoza',
                'email' => 'operator3@nia.local',
                'role' => 'operator',
            ],
            [
                'personnel_id' => '100006',
                'name' => 'Nestor Villanueva',
                'email' => 'operator4@nia.local',
                'role' => 'operator',
            ],
            [
                'personnel_id' => '100007',
                'name' => 'Rodolfo Bautista',
                'email' => 'operator5@nia.local',
                'role' => 'operator',
            ],
            [
                'personnel_id' => '100008',
                'name' => 'Ernesto Garcia',
                'email' => 'operator6@nia.local',
                'role' => 'operator',
            ],

            [
                'personnel_id' => '100009',
                'name' => 'Mario Santos',
                'email' => 'driver1@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100010',
                'name' => 'Felix Navarro',
                'email' => 'driver2@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100011',
                'name' => 'Arnel Domingo',
                'email' => 'driver3@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100012',
                'name' => 'Jeric Panganiban',
                'email' => 'driver4@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100013',
                'name' => 'Samuel Rivera',
                'email' => 'driver5@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100014',
                'name' => 'Paulo Mercado',
                'email' => 'driver6@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100037',
                'name' => 'Victor Ramos',
                'email' => 'driver7@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100038',
                'name' => 'Gilbert Manalo',
                'email' => 'driver8@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100039',
                'name' => 'Dennis Laurente',
                'email' => 'driver9@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100040',
                'name' => 'Marvin Galvez',
                'email' => 'driver10@nia.local',
                'role' => 'driver',
            ],
            [
                'personnel_id' => '100041',
                'name' => 'Rogelio Bautista',
                'email' => 'driver11@nia.local',
                'role' => 'driver',
            ],

            [
                'personnel_id' => '100015',
                'name' => 'Andrew Arcalas',
                'email' => 'user1@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100016',
                'name' => 'Mark Anthony Reyes',
                'email' => 'user2@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100017',
                'name' => 'John Carlo Dizon',
                'email' => 'user3@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100018',
                'name' => 'Paolo Mendoza',
                'email' => 'user4@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100019',
                'name' => 'Kevin Bautista',
                'email' => 'user5@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100020',
                'name' => 'Joshua Villanueva',
                'email' => 'user6@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100021',
                'name' => 'Michael Ramos',
                'email' => 'user7@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100022',
                'name' => 'Jerome Salazar',
                'email' => 'user8@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100023',
                'name' => 'Carlo Manansala',
                'email' => 'user9@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100024',
                'name' => 'Rico Domingo',
                'email' => 'user10@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100025',
                'name' => 'Ryan Magno',
                'email' => 'user11@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100026',
                'name' => 'Christian Alonzo',
                'email' => 'user12@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100027',
                'name' => 'Jomar dela Rosa',
                'email' => 'user13@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100028',
                'name' => 'Alvin Fernandez',
                'email' => 'user14@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100029',
                'name' => 'Noel Panganiban',
                'email' => 'user15@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100030',
                'name' => 'Samuel Cruz',
                'email' => 'user16@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100031',
                'name' => 'Patrick Javier',
                'email' => 'user17@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100032',
                'name' => 'Renzo Aquino',
                'email' => 'user18@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100033',
                'name' => 'Luis Navarro',
                'email' => 'user19@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100034',
                'name' => 'Bryan Mercado',
                'email' => 'user20@nia.local',
                'role' => 'user',
            ],
            [
                'personnel_id' => '100035',
                'name' => 'Arturo Meldea',
                'email' => 'dispatcher21@nia.local',
                'role' => 'dispatcher',
            ],
            [
                'personnel_id' => '100036',
                'name' => 'Bayaksan Dela Cruz',
                'email' => 'dispatcher22@nia.local',
                'role' => 'dispatcher',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['personnel_id' => $userData['personnel_id']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'role' => $userData['role'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
