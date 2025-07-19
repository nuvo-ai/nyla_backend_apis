<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentGatewayTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('payment_gateways')->insert([
            [
                'name' => 'Paystack',
                'slug' => 'paystack',
                'is_active' => true,
                'is_default' => true,
                'public_key' => config('services.paystack.public_key'),
                'secret_key' => config('services.paystack.secret_key'),
                'description' => 'Paystack payment gateway integration.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Flutterwave',
                'slug' => 'flutterwave',
                'is_active' => false,
                'is_default' => false,
                'public_key' => null,
                'secret_key' => null,
                'description' => 'Flutterwave payment gateway integration.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
