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
}