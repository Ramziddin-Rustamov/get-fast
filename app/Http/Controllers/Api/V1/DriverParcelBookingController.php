<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\ParcelBookingService;

/**
 * Haydovchi tomoni — o'z safarlariga kelgan posilkalarni ko'rish.
 * Haydovchi trip yaratishда posilka olishga allaqachon rozi bo'lgani uchun
 * bu yerda tasdiqlash/rad etish yo'q — faqat ma'lumot.
 */
class DriverParcelBookingController extends Controller
{
    protected $service;

    public function __construct(ParcelBookingService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->service->driverBookings();
    }

    public function forTrip($tripId)
    {
        return $this->service->driverBookingsForTrip($tripId);
    }
}
