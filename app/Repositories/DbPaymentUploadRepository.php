<?php

namespace App\Repositories;

use App\Models\PaymentFile;
use App\Repositories\Contracts\PaymentUploadRepositoryInterface;

class DbPaymentUploadRepository extends BaseEloquentRepository implements PaymentUploadRepositoryInterface
{
    public function __construct(PaymentFile $model)
    {
        $this->model = $model;
    }
}