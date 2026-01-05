<?php

namespace App\Jobs;

use App\Repositories\V1\SmsRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessSms implements ShouldQueue
{
    use Queueable;

    protected string $phone;
    protected string $message;
    protected string $action;

    public function __construct(string $phone, string $message, string $action)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->action = $action;
    }

    public function handle(): void
    {
        app(SmsRepository::class)
            ->send($this->phone, $this->message, $this->action);
    }
}
