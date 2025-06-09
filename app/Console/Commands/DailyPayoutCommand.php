<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPayoutJob;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyPayoutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-payout-command --frequency=daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily payout invoices and send to customers';

    public function handle(PaymentRepositoryInterface $paymentRepository): void
    {
        $this->info("Starting daily payout process...");

        $today = Carbon::today()->toDateString();

        $groupedPayments = $paymentRepository->getPaymentsGroupedByUser($today);

        $jobCount = count($groupedPayments);

        // let's process each grouped payment asynchronously by distributing them with horizon workers
        foreach ($groupedPayments as $email => $payment) {
            $paymentIds = $payment->pluck('id')->all();

            $this->info("Dispatching payout job for $email with " . count($paymentIds) . " payments");

            ProcessPayoutJob::dispatch($today, $email, $paymentIds);
        }

        $this->info("Total $jobCount daily payout update jobs dispatched.");
    }
}
