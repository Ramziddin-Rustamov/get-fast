<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            [
                'phone' => '+998997713909',
            ],
            [
                'first_name'  => 'Ramziddin',
                'last_name'   => 'Rustamov',
                'father_name' => 'utkir ugli',
                'email'       => 'rustamovvramziddin@gmail.com',
                'password'    => Hash::make('1236987456321aaSSdd'),
                'role'        => 'admin',
                'is_verified' => 1,
                'verification_code' => "123321",
                'driving_licence_number' => null,
                'driving_licence_expiry' => null,
                'birth_date' => null,
                'driving_verification_status' => 'none',
                'region_id' => null,
                'district_id' => null,
                'quarter_id' => null,
                'home' => null,
                'image' => null,
            ]
        );
    }
}
