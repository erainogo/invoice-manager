<?php

namespace App\Repositories;

use App\Models\InvoicePayment;
use App\Repositories\Contracts\InvoicePaymentRepositoryInterface;

class DbInvoicePaymentRepository extends BaseEloquentRepository implements InvoicePaymentRepositoryInterface
{
    public function __construct(InvoicePayment $model)
    {
        $this->model = $model;
    }

    public function findByInvoiceAndPayment($iid, $pid)
    {
        return $this->model
            ->where('invoice_id', $iid)
            ->where('payment_id', $pid)
            ->first();
    }
}