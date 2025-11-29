<?php
namespace App\Services\V1;

use App\Repositories\V1\BookingRepository;

class BookingService
{
    protected $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function getAll()
    {
        return $this->bookingRepository->getAllBookings();
    }

    public function getBookingById($id)
    {
        return $this->bookingRepository->getBookingById($id);
    }

    public function createBooking($request)
    {
        return $this->bookingRepository->createBooking($request);
    }

    public function updateBooking($id, array $data)
    {
        return $this->bookingRepository->updateBooking($id, $data);
    }

    public function cancelBooking($id)
    {
        return $this->bookingRepository->cancelBooking($id);
    }
}
