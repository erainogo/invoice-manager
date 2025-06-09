<?php

namespace App\Services;

use App\Mail\CustomerInvoiceMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CurrencyConverter;
use App\Repositories\Contracts\InvoicePaymentRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentProcessingService
{
    use CurrencyConverter;

    private PaymentRepositoryInterface $paymentRepository;

    private InvoiceRepositoryInterface $invoiceRepository;

    private InvoicePaymentRepositoryInterface $invoicePaymentRepository;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        InvoicePaymentRepositoryInterface $invoicePaymentRepository
    ){
        $this->paymentRepository = $paymentRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoicePaymentRepository = $invoicePaymentRepository;
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
            'reference_number' => $row['reference_no'],
        ];

        $data = [
            'payment_file_id' => $paymentFileId,
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

    public function processDailyPayouts($paymentIds, $email, $yesterday): void
    {
        try {
            $payments = $this->paymentRepository->getPaymentsByIds($paymentIds);

            $htmlInvoice = view('emails.invoice', [
                'payments' => $payments,
                'email' => $email,
                'date' => $yesterday,
            ])->render();

            DB::beginTransaction();

            $today = Carbon::now()->format('Y-m-d');

            $existingInvoice = $this->invoiceRepository->findByEmailAndDate(
                $email, $today);

            if ($existingInvoice) {
                Log::warning("Invoice already exists for $email on $yesterday. Skipping.");

                return;
            }

            $invoice = $this->invoiceRepository->create([
                'customer_email' => $email,
                'html_content' => html_entity_decode($htmlInvoice),
                'sent_at' => $today,
            ]);

            // update the pivot table.
            // Each invoice can include many payments, and each payment can potentially be linked to an invoice.
            // Each invoice can contain multiple payments for that customer on that day.
            // this will help us to track which payments were included in which invoice.
            foreach ($paymentIds as $id) {
                $existing = $this->invoicePaymentRepository
                    ->findByInvoiceAndPayment($invoice->id, $id);

                if (!$existing) {
                    $this->invoicePaymentRepository->create([
                        'invoice_id' => $invoice->id,
                        'payment_id' => $id,
                    ]);
                }
            }

            $this->paymentRepository->updatePayouts($paymentIds);

            DB::commit();

            Log::info("sending email: " . $email);

            Mail::to($email)->send(new CustomerInvoiceMail($htmlInvoice));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to send invoice to $email: " . $e->getMessage());
        }
    }
}
