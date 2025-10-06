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

    /**
     * Create a new job instance.
     */
    public function __construct(string $phone, string $message, string $action)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsRepository $repository): void
    {
        $repository->send($this->phone, $this->message, $this->action);
    }
}
