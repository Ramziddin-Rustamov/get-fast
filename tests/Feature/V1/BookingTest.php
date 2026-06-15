<?php

namespace Tests\Feature\V1;

use App\Models\User;
use App\Models\UserBalance;
use App\Models\V1\Booking;
use App\Models\V1\CompanyBalance;
use App\Models\V1\Trip;
use App\Models\V1\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // SMS yuborilishining oldini olamiz (BookingRepository ProcessSms job dispatch qiladi).
        Queue::fake();
    }

    // ----------------------------------------------------------------------
    // Yordamchi metodlar
    // ----------------------------------------------------------------------

    private function makeDriverWithVehicle(): array
    {
        $driver = User::factory()->create(['role' => 'driver']);

        $vehicle = Vehicle::create([
            'user_id'              => $driver->id,
            'color_id'             => 1, // colors jadvali migration tomonidan seed qilinadi
            'model'                => 'Cobalt',
            'car_number'           => '01A' . random_int(100, 999) . 'AA',
            'tech_passport_number' => 'TP' . random_int(10000, 99999),
            'seats'                => 4,
        ]);

        return [$driver, $vehicle];
    }

    private function makeTrip(User $driver, Vehicle $vehicle, array $overrides = []): Trip
    {
        return Trip::create(array_merge([
            'driver_id'       => $driver->id,
            'vehicle_id'      => $vehicle->id,
            'start_time'      => now()->addDay(),
            'end_time'        => now()->addDay()->addHours(3),
            'price_per_seat'  => 100000,
            'total_seats'     => 4,
            'available_seats' => 4,
            'status'          => 'active',
        ], $overrides));
    }

    private function giveBalance(int $userId, float $balance): void
    {
        UserBalance::create([
            'user_id'  => $userId,
            'balance'  => $balance,
            'currency' => 'UZS',
        ]);
    }

    private function passenger(array $overrides = []): array
    {
        return array_merge([
            'name'      => 'Passenger',
            'phone'     => '998901112233',
            'latitude'  => 41.3111,
            'longitude' => 69.2797,
        ], $overrides);
    }

    // ----------------------------------------------------------------------
    // createBooking (bookTrip)
    // ----------------------------------------------------------------------

    /** @test */
    public function client_can_book_a_trip_and_balances_are_updated_correctly()
    {
        [$driver, $vehicle] = $this->makeDriverWithVehicle();
        $trip = $this->makeTrip($driver, $vehicle);

        $client = User::factory()->create(['role' => 'client']);
        $this->giveBalance($client->id, 200000);

        $response = $this->actingAs($client, 'api')->postJson('/api/v1/client/booking', [
            'trip_id'    => $trip->id,
            'passengers' => [$this->passenger()],
        ]);

        $response->assertStatus(201);

        // Booking yaratildi
        $this->assertDatabaseHas('bookings', [
            'trip_id'      => $trip->id,
            'user_id'      => $client->id,
            'seats_booked' => 1,
            'status'       => 'confirmed',
        ]);

        // Mijoz balansidan to'liq narx (100000) yechildi: 200000 - 100000 = 100000
        $this->assertDatabaseHas('user_balances', [
            'user_id' => $client->id,
            'balance' => '100000.00',
        ]);

        // Haydovchiga sof tushum (100000 - 5% = 95000) o'tdi
        $this->assertDatabaseHas('user_balances', [
            'user_id' => $driver->id,
            'balance' => '95000.00',
        ]);

        // Kompaniya xizmat haqi (5%) oldi
        $this->assertDatabaseHas('company_balances', [
            'balance' => '5000.00',
        ]);

        // Joylar 1 taga kamaydi
        $this->assertDatabaseHas('trips', [
            'id'              => $trip->id,
            'available_seats' => 3,
        ]);

        // Balans tranzaksiyalari yozildi
        $this->assertDatabaseHas('balance_transactions', [
            'user_id' => $client->id,
            'type'    => 'debit',
            'amount'  => '100000.00',
        ]);
        $this->assertDatabaseHas('balance_transactions', [
            'user_id' => $driver->id,
            'type'    => 'credit',
            'amount'  => '95000.00',
        ]);
    }

    /** @test */
    public function booking_fails_with_insufficient_balance_and_seats_are_not_changed()
    {
        [$driver, $vehicle] = $this->makeDriverWithVehicle();
        $trip = $this->makeTrip($driver, $vehicle);

        $client = User::factory()->create(['role' => 'client']);
        $this->giveBalance($client->id, 50000); // 100000 dan kam

        $response = $this->actingAs($client, 'api')->postJson('/api/v1/client/booking', [
            'trip_id'    => $trip->id,
            'passengers' => [$this->passenger()],
        ]);

        $response->assertStatus(422);

        // Hech qanday booking yaratilmadi
        $this->assertDatabaseMissing('bookings', [
            'trip_id' => $trip->id,
            'user_id' => $client->id,
        ]);

        // MUHIM: balans yetmaganda joylar o'zgarmasligi kerak (tuzatishimiz aynan shu).
        $this->assertDatabaseHas('trips', [
            'id'              => $trip->id,
            'available_seats' => 4,
        ]);

        // Mijoz balansi o'zgarmadi
        $this->assertDatabaseHas('user_balances', [
            'user_id' => $client->id,
            'balance' => '50000.00',
        ]);
    }

    /** @test */
    public function booking_fails_when_not_enough_seats_available()
    {
        [$driver, $vehicle] = $this->makeDriverWithVehicle();
        $trip = $this->makeTrip($driver, $vehicle, ['available_seats' => 4]);

        $client = User::factory()->create(['role' => 'client']);
        $this->giveBalance($client->id, 1000000);

        // 4 ta joy bor, 5 ta yo'lovchi so'ralyapti
        $passengers = [];
        for ($i = 0; $i < 5; $i++) {
            $passengers[] = $this->passenger(['name' => "P{$i}"]);
        }

        $response = $this->actingAs($client, 'api')->postJson('/api/v1/client/booking', [
            'trip_id'    => $trip->id,
            'passengers' => $passengers,
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('trips', [
            'id'              => $trip->id,
            'available_seats' => 4,
        ]);
    }

    /** @test */
    public function driver_cannot_book_their_own_trip()
    {
        [$driver, $vehicle] = $this->makeDriverWithVehicle();
        $trip = $this->makeTrip($driver, $vehicle);

        $this->giveBalance($driver->id, 1000000);

        $response = $this->actingAs($driver, 'api')->postJson('/api/v1/client/booking', [
            'trip_id'    => $trip->id,
            'passengers' => [$this->passenger()],
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('bookings', [
            'trip_id' => $trip->id,
            'user_id' => $driver->id,
        ]);
    }

    // ----------------------------------------------------------------------
    // cancelBooking
    // ----------------------------------------------------------------------

    /** @test */
    public function cancelling_a_missing_booking_returns_404()
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client, 'api')
            ->deleteJson('/api/v1/client/booking/cancel/999999');

        // Tuzatishimizdan oldin bu xato 200 (keyin 500) qaytarardi; endi to'g'ri 404.
        $response->assertStatus(404)->assertJson(['status' => 'error']);
    }

    /** @test */
    public function client_can_cancel_a_booking_and_receive_refund()
    {
        [$driver, $vehicle] = $this->makeDriverWithVehicle();
        // 1 ta joy allaqachon band qilingan deb hisoblaymiz
        $trip = $this->makeTrip($driver, $vehicle, ['available_seats' => 3]);

        $client = User::factory()->create(['role' => 'client']);

        // Booking qilingandan keyingi holatni o'rnatamiz
        $this->giveBalance($client->id, 0);       // mijoz allaqachon to'lagan
        $this->giveBalance($driver->id, 95000);   // haydovchi tushumini olgan
        CompanyBalance::create(['balance' => 5000, 'total_income' => 5000]);

        $booking = Booking::create([
            'trip_id'      => $trip->id,
            'user_id'      => $client->id,
            'seats_booked' => 1,
            'total_price'  => 100000,
            'status'       => 'confirmed',
            'expired_at'   => $trip->end_time,
        ]);

        $response = $this->actingAs($client, 'api')
            ->deleteJson("/api/v1/client/booking/cancel/{$booking->id}");

        $response->assertStatus(200)->assertJson(['status' => 'success']);

        // Booking bekor qilindi
        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => 'cancelled',
        ]);

        // Mijozga qaytarildi: 100000 - 5% komissiya = 95000
        $this->assertDatabaseHas('user_balances', [
            'user_id' => $client->id,
            'balance' => '95000.00',
        ]);

        // Haydovchi: 95000 + 5000 (komissiya) - 100000 (qaytarish) + 1000 (kompensatsiya) = 1000
        $this->assertDatabaseHas('user_balances', [
            'user_id' => $driver->id,
            'balance' => '1000.00',
        ]);

        // Kompaniya: 5000 - 1000 (haydovchi kompensatsiyasi) = 4000
        $this->assertDatabaseHas('company_balances', [
            'balance' => '4000.00',
        ]);

        // Joy qaytarildi va trip yana active
        $this->assertDatabaseHas('trips', [
            'id'              => $trip->id,
            'available_seats' => 4,
            'status'          => 'active',
        ]);
    }

    /** @test */
    public function cancelling_an_already_cancelled_booking_returns_422()
    {
        [$driver, $vehicle] = $this->makeDriverWithVehicle();
        $trip = $this->makeTrip($driver, $vehicle);

        $client = User::factory()->create(['role' => 'client']);

        $booking = Booking::create([
            'trip_id'      => $trip->id,
            'user_id'      => $client->id,
            'seats_booked' => 1,
            'total_price'  => 100000,
            'status'       => 'cancelled',
            'expired_at'   => $trip->end_time,
        ]);

        $response = $this->actingAs($client, 'api')
            ->deleteJson("/api/v1/client/booking/cancel/{$booking->id}");

        $response->assertStatus(422)->assertJson(['status' => 'error']);
    }
}
