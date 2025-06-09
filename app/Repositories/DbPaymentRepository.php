<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class DbPaymentRepository extends BaseEloquentRepository implements PaymentRepositoryInterface
{
    public function __construct(Payment $model)
    {
        $this->model = $model;
    }

    public function getPaymentsGroupedByUser($today) {
        return $this->model
            ->where('status',"unprocessed")
            ->whereDate('created_at', $today)
            ->get()
            ->groupBy('customer_email');
    }

    public function updatePayouts($paymentIds)
    {
        return $this->model
            ->whereIn('id',$paymentIds)
            ->update([
//                'processed_at' => now(),
                'status' => 'processed'
            ]);
    }

    public function getPaymentsByIds($paymentIds)
    {
        return $this->model
            ->whereIn('id',$paymentIds)
            ->select([
                'id',
                'payment_date',
                'reference_number',
                'original_amount',
                'original_currency',
                'usd_amount',
                'customer_email',
                'customer_name',
            ])
            ->get();
    }
}