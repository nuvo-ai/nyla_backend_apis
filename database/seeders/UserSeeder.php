<?php

namespace Database\Seeders;

use App\Models\Portal;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $portal = Portal::firstOrCreate([
            'name' => 'Hospital',
        ]);
        User::create([
            'portal_id' => $portal->id,
            'first_name' => 'Tope',
            'last_name' => 'Olotu',
            'email' => 'topeolotu75@gmail.com',
            'phone' => '08087541225',
            'role' => 'User',
            'address' => '123 Market Street',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'status' => 1,
            'password' => bcrypt('@tintin123'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'portal_id' => $portal->id,
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@nyla.ai',
            'phone' => '08098765432',
            'role' => 'Admin',
            'address' => '456 Admin Avenue',
            'state' => 'Abuja',
            'city' => 'Maitama',
            'status' => 1,
            'password' => bcrypt('NylaAi@2025'),
            'email_verified_at' => now(),
        ]);
    }
}
