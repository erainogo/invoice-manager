<?php

namespace App\Services;

use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Helpers\CurrencyConverter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PaymentProcessingService
{
    use CurrencyConverter;
    private PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository){

        $this->paymentRepository = $paymentRepository;
    }

    public function getCurrencyRate($base,$target): float
    {
        return $this->getRate($base,$target);
    }

    public function processRow(array $row, int $paymentFileId): void
    {
        Log::info("Processing record: " . json_encode($row));

        $validator = Validator::make($row, [
            'customer_id' => 'required|string',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'amount' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'reference_no' => 'required|string',
            'date_time' => 'required|date',
        ]);

        $lookup = [
            'payment_file_id' => $paymentFileId,
            'reference_number' => $row['reference_no'],
        ];

        $data = [
            'customer_id' => $row['customer_id'],
            'customer_name' => $row['customer_name'],
            'customer_email' => $row['customer_email'],
            'payment_date' => $row['date_time'],
            'original_amount' => $row['amount'],
            'original_currency' => $row['currency'],
            'usd_amount' => $row['amount'],
            'status' => 'unprocessed',
        ];

        if ($validator->fails()) {
            Log::warning('Validation failed', ['row' => $row, 'errors' => $validator->errors()]);

            $data['status'] = 'failed';

            $this->paymentRepository->updateOrCreate($lookup, $data);

            return;
        }

        try {
            // $rate = $this->getCurrencyRate($row['currency'], 'USD');
            // $data['usd_amount'] = $row['amount'] * $rate;

            $this->paymentRepository->updateOrCreate($lookup, $data);

            Log::info("Record saved/updated for reference_no: " . $row['reference_no']);
        } catch (\Exception $e) {
            Log::error('Processing failed', ['row' => $row, 'error' => $e->getMessage()]);
        }
    }

    public function processDailyPayouts($paymentIds, $email, $today): void
    {
        try {
            Log::info("sending email: " . json_encode($paymentIds));

            $payments = $this->paymentRepository->getPaymentsByIds($paymentIds);

            $htmlInvoice = view('emails.invoice', [
                'payments' => $payments,
                'email' => $email,
                'date' => $today,
            ])->render();

            Mail::send([], [], function ($message) use ($email, $htmlInvoice) {
                $message->to($email)
                    ->subject('Customer Daily Invoice')
                    ->html($htmlInvoice);
            });

            $this->paymentRepository->updatePayouts($paymentIds);
        } catch (\Exception $e) {
            Log::error("Failed to send invoice to $email: " . $e->getMessage());
        }
    }
}
