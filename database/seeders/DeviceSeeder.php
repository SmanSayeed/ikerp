<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceSeeder extends Seeder
{
    /**
     * Seed the devices table.
     */
    public function run(): void
    {
        $devices = [];

        for ($i = 1; $i <= 100; $i++) {
            $devices[] = [
                'name' => 'Device ' . $i,
                'description' => 'Description for device ' . $i,
                'status' => rand(0, 1), // Randomly set status to true or false
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert the devices into the database
        DB::table('devices')->insert($devices);
    }
}
