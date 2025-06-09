<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

class DbInvoiceRepository extends BaseEloquentRepository implements InvoiceRepositoryInterface
{
    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }

    public function findByEmailAndDate($email, $date)
    {
        return $this->model
            ->where('customer_email', $email)
            ->whereDate('sent_at', $date)
            ->first();
    }
}