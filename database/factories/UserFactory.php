<?php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // or Hash::make('password')
            'role' => 'client',
            'status' => true,
            'email_verified_at' => null,
        ];
    }

    public function verified()
    {
        return $this->state([
            'email_verified_at' => now(),
        ]);
    }

    public function inactive()
    {
        return $this->state([
            'status' => false,
        ]);
    }
}
