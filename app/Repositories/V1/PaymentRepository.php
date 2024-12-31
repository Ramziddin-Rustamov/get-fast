<?php

namespace App\Repositories\V1;

use App\Models\V1\Payment;

class PaymentRepository
{
    protected $model;

    public function __construct(Payment $payment)
    {
        $this->model = $payment;
    }

    public function getAll()
    {
        return $this->model->paginate(10); // Pagination bilan
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $payment = $this->findById($id);
        $payment->update($data);
        return $payment;
    }

    public function delete($id)
    {
        $payment = $this->findById($id);
        return $payment->delete();
    }
}
