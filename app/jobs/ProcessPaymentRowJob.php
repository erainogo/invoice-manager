<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PaymentProcessingService;

class ProcessPaymentRowJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable, Batchable;

    protected array $row;
    protected int $paymentFileId;

    public function __construct(array $row, int $paymentFileId)
    {
        $this->row = $row;
        $this->paymentFileId = $paymentFileId;

        $this->onQueue('payment-file-read-queue');
        $this->onConnection('redis');
    }

    public function handle(PaymentProcessingService $service): void
    {
        $service->processRow($this->row, $this->paymentFileId);
    }
}
