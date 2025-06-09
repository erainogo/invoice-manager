<?php

namespace App\Repositories\Contracts;

interface InvoicePaymentRepositoryInterface{
    public function findByInvoiceAndPayment($id, $id1);
}