<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = public_path('sql/states.sql');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }
}
