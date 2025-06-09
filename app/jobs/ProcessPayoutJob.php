<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PaymentProcessingService;

class ProcessPayoutJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable, Batchable;

    public $payment;

    public $email;

    public $yesterday;

    public function __construct($yesterday, $email, $payment)
    {
        $this->payment = $payment;
        $this->email = $email;
        $this->yesterday = $yesterday;

        $this->onQueue('payment-payout-processing-queue');
    }

    public function handle(PaymentProcessingService $service): void
    {
        Log::info("{$this->email} processing has started");

        $service->processDailyPayouts($this->payment, $this->email, $this->yesterday);
    }
}
