<?php namespace App\Repositories\Contracts;

interface InvoiceRepositoryInterface {
    public function findByEmailAndDate($email, $yesterday);
}