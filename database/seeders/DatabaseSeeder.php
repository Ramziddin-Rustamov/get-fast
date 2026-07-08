<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ParcelTypeSeeder::class,
        ]);

        User::updateOrCreate(
            ['phone' => '+998993196532'],
            [
                'first_name' => 'Ramziddin',
                'last_name' => 'Rustamov',
                'father_name' => 'Rustamovich',
                'email' => 'ramzidasaddss3233in@example.com',
                'password' => Hash::make('password'),
                'image' => null,
                'region_id' => 1,
                'district_id' => 1,
                'quarter_id' => 1,
                'home' => '12A',
                'role' => 'driver',
                'is_verified' => true,
                'verification_code' => null,
                'driving_licence_number' => 'AB12234567',
                'driving_licence_expiry' => '2031-01-01',
                'birth_date' => '2000-05-10',
                'driving_verification_status' => 'approved',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
