<?php

namespace App\Services\V1;

use App\Repositories\V1\VehicleRepository;

class VehicleService
{
    protected $vehicleRepository;

    public function __construct(VehicleRepository $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function getAll()
    {
        return $this->vehicleRepository->getAll();
    }

    public function findById($id)
    {
        return $this->vehicleRepository->findById($id);
    }

    public function create(array $data)
    {
        return $this->vehicleRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->vehicleRepository->update($id, $data);
    }

    public function delete($id)
    {
        $this->vehicleRepository->delete($id);
    }
}
