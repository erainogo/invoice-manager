<?php

namespace App\Jobs;

use App\Models\PaymentFile;
use App\Repositories\Contracts\PaymentUploadRepositoryInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\Exception;
use League\Csv\Reader;
use Throwable;

class ProcessPaymentFileJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Batchable, Dispatchable;

    protected int $fileId;

    protected ?PaymentFile $file = null;

    public function __construct(int $fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    public function handle(PaymentUploadRepositoryInterface $paymentUploadRepository): void
    {
        $this->file = $paymentUploadRepository->findOrFail($this->fileId);

        Log::info("Started ProcessPaymentFileJob for file ID: {$this->fileId}");

        if (empty($this->file->path) || !Storage::disk('s3')->exists($this->file->path)) {
            throw new \Exception("Payment file path is missing or does not exist.");
        }

        Log::info("{$this->file->file_name} processing has started");

        $paymentUploadRepository->update($this->file, ['status' => 'processing']);

        $stream = $this->file->tryGetStream();

        if (!$stream) {
            Log::warning("Failed to get stream for file ID {$this->fileId}");
            return;
        }

        $csv = Reader::createFromStream($stream);
        $csv->setHeaderOffset(0);

        $chunkSize = 100;
        $rowJobs = [];
        $rowCount = 0;

        foreach ($csv->getRecords() as $row) {
            $rowJobs[] = new ProcessPaymentRowJob($row, $this->file->id);
            $rowCount++;

            if ($rowCount % $chunkSize === 0) {
                $this->dispatchBatch($rowJobs);
                $rowJobs = [];
            }
        }

        // Dispatch remaining rows
        if (!empty($rowJobs)) {
            $this->dispatchBatch($rowJobs);
        }

        $paymentUploadRepository->update($this->file, [
            'status' => 'processed',
            'processed_at' => now()
        ]);

        Log::info("File ID {$this->fileId} marked as processed.");
    }

    /**
     * Dispatch a batch of row jobs
     *
     * @param array $jobs
     * @throws Throwable
     */
    protected function dispatchBatch(array $jobs): void
    {
        $batch = Bus::batch($jobs)
            ->allowFailures()
            ->onConnection('redis')
            ->onQueue('payment-file-read-queue')
            ->dispatch();

        // Optionally update the file with last_batch_id
//        if ($this->file) {
//            $this->file->update(['last_batch_id' => $batch->id]);
//        }

        Log::info("Dispatched batch ID {$batch->id} for file ID {$this->fileId}");
    }

    /**
     * Handle job failure
     */
    public function failed(PaymentUploadRepositoryInterface $paymentUploadRepository, Throwable $exception): void
    {
        try {
            $file = $this->file ?? $paymentUploadRepository->find($this->fileId);

            if ($file) {
                $file->update(['status' => 'failed']);
            }

            Log::error("File processing failed for ID {$this->fileId}: " . $exception->getMessage());
        } catch (Throwable $e) {
            Log::critical("Failure handler crashed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }
}
