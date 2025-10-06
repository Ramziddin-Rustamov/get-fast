<?php

namespace App\Services\V1;

use App\Jobs\ProcessSms;
use App\Repositories\V1\SmsRepository;

class SmsService
{
    protected SmsRepository $repository;

    public function __construct(SmsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function sendQueued(string $phone, string $message, string $action): void
    {
        ProcessSms::dispatch($phone, $message, $action);
    }

}
