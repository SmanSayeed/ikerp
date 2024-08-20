<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => true,
        ]);

        User::create([
            'name' => 'Service Provider',
            'email' => 'serviceprovider@example.com',
            'password' => Hash::make('password'),
            'role' => 'service_provider',
            'status' => true,
        ]);

        User::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => true,
        ]);

         // Some are active and email not verified
         User::factory()->count(70)->create([
            'role' => 'client',
            'status' => true,
            'email_verified_at' => null, // Email not verified
        ]);

        // Some are active and email verified
        User::factory()->count(80)->create([
            'role' => 'client',
            'status' => true,
            'email_verified_at' => now(), // Email verified
        ]);

        // Some are inactive and email verified
        User::factory()->count(50)->create([
            'role' => 'client',
            'status' => false, // Inactive
            'email_verified_at' => now(), // Email verified
        ]);
    }
}
