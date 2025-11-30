<?php

namespace App\Services\V1;

use App\Http\Requests\V1\StoreRequest;
use App\Http\Requests\V1\UpdateRequest;
use App\Repositories\V1\VehicleRepository;
use Illuminate\Http\Request;

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

    public function create(StoreRequest $data)
    {
        return $this->vehicleRepository->create($data);
    }

    public function delete($id)
    {
        $this->vehicleRepository->delete($id);
    }
}
