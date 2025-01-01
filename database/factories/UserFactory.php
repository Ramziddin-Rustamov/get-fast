<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(), 
            'password' => static::$password ??= Hash::make('password'), // Parolni yashirish
            'region' => fake()->city(), // Foydalanuvchi uchun region generatsiyasi
            'district' => fake()->city(), // Optional: tuman
            'village' => fake()->city(), // Optional: qishloq
            'home' => fake()->address(), // Optional: uy manzili
            'role' => fake()->randomElement(['client', 'driver', 'admin']), // Random role
            'remember_token' => Str::random(10),
        ];
    }

}
