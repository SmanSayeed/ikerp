<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create specific users with predefined attributes
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => true,
            'email_verified_at' => now(), // Ensures email is verified
        ]);

        User::create([
            'name' => 'Service Provider',
            'email' => 'serviceprovider@example.com',
            'password' => Hash::make('password'),
            'role' => 'service_provider',
            'status' => true,
            'email_verified_at' => now(), // Ensures email is verified
        ]);

        Client::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'status' => true,
            'email_verified_at' => now(), // Ensures email is verified
        ]);

        // Create multiple users with email not verified
        for ($i = 1; $i <= 10; $i++) {
            Client::create([
                'name' => "Client User $i",
                'email' => "client$i@example.com",
                'password' => Hash::make('password'),
                'status' => true,
                'email_verified_at' => null, // Email not verified
            ]);
        }

        // Create multiple users with email verified
        for ($i = 11; $i <= 20; $i++) {
            Client::create([
                'name' => "Client User $i",
                'email' => "client$i@example.com",
                'password' => Hash::make('password'),
                'status' => true,
                'email_verified_at' => now(), // Email verified
            ]);
        }

        // Create multiple users with email verified but status inactive
        for ($i = 21; $i <= 30; $i++) {
            Client::create([
                'name' => "Client User $i",
                'email' => "client$i@example.com",
                'password' => Hash::make('password'),
                'status' => false, // Inactive
                'email_verified_at' => now(), // Email verified
            ]);
        }
    }
}
