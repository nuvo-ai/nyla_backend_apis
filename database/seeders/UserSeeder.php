<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Tope',
            'last_name' => 'Olotu',
            'email' => 'topeolotu75@gmail.com',
            'phone_number' => '08087541225',
            'role' => 'User',
            'address' => '123 Market Street',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'status' => 1,
            'password' => bcrypt('@tintin123'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@bongoexpressonline.com',
            'phone_number' => '08098765432',
            'role' => 'Admin',
            'address' => '456 Admin Avenue',
            'state' => 'Abuja',
            'city' => 'Maitama',
            'status' => 1,
            'password' => bcrypt('Bongoexpressonline2025@'),
            'email_verified_at' => now(),
        ]);
    }
}
