<?php


namespace App\Services\V1;

use App\Repositories\V1\PaymentRepository;

class PaymentService
{
    protected $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function getAllPayments()
    {
        return $this->paymentRepository->getAll();
    }

    public function getPaymentById($id)
    {
        return $this->paymentRepository->findById($id);
    }

    public function createPayment(array $data)
    {
        return $this->paymentRepository->create($data);
    }

    public function updatePayment($id, array $data)
    {
        return $this->paymentRepository->update($id, $data);
    }

    public function deletePayment($id)
    {
        return $this->paymentRepository->delete($id);
    }
}
