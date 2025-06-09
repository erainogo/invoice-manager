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

    public $today;

    public function __construct($today, $email, $payment)
    {
        $this->payment = $payment;
        $this->email = $email;
        $this->today = $today;

        $this->onQueue('payment-payout-processing-queue');
    }

    public function handle(PaymentProcessingService $service): void
    {
        Log::info("{$this->email} processing has started");

        $service->processDailyPayouts($this->payment, $this->email, $this->today);
    }
}
