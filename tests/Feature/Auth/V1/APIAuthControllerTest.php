<?php

namespace Tests\Feature\Auth\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\V1\UserLanguage;

class APIAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_successfully()
    {
        $payload = [
            'first_name' => 'Ali',
            'last_name' => 'Valiyev',
            'father_name' => 'Hasan',
            'phone' => '998901234567',
            'email' => 'ali@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'user_phone' => '998901234567',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'user_phone',
                'code',
            ]);

        // User bazaga yozilganmi
        $this->assertDatabaseHas('users', [
            'phone' => '998901234567',
            'email' => 'ali@test.com',
            'is_verified' => false,
        ]);

        // UserLanguage yaratilganmi
        $user = User::where('phone', '998901234567')->first();

        $this->assertDatabaseHas('user_languages', [
            'user_id' => $user->id,
            'language' => 'uz',
        ]);
    }

    /** @test */
    public function register_fails_if_phone_is_duplicate()
    {
        User::factory()->create([
            'phone' => '998901234567',
            'email' => 'old@test.com',
        ]);

        $payload = [
            'phone' => '998901234567',
            'email' => 'new@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ])
            ->assertJsonStructure([
                'errors' => ['phone'],
            ]);
    }

    /** @test */
    public function register_fails_if_password_confirmation_is_missing()
    {
        $payload = [
            'phone' => '998901234568',
            'email' => 'test@test.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);
    }



    public function user_can_verify_code_successfully()
    {
        $user = User::factory()->create([
            'phone' => '998901234567',
            'verification_code' => '123456',
            'is_verified' => false,
        ]);

        $response = $this->postJson('/api/auth/verify-code', [
            'phone' => '998901234567',
            'code' => '123456',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'go' => 'login page',
            ]);

        // user verified bo‘ldimi
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_verified' => true,
            'verification_code' => null,
        ]);

        // user balance yaratildimi
        $this->assertDatabaseHas('user_balances', [
            'user_id' => $user->id,
            'balance' => 0.00,
        ]);

        // user language mavjudmi
        $this->assertDatabaseHas('user_languages', [
            'user_id' => $user->id,
            'language' => 'uz',
        ]);
    }

    /** @test */
    public function verify_code_fails_if_code_is_wrong()
    {
        $user = User::factory()->create([
            'phone' => '998901234568',
            'verification_code' => '123456',
            'is_verified' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/verify-code', [
            'phone' => '998901234568',
            'code' => '999999',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'status' => 'error',
            ]);

        // user verified bo‘lib ketmasligi kerak
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_verified' => false,
            'verification_code' => '123456',
        ]);
    }
}
