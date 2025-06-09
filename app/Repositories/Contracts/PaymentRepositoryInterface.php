<?php namespace App\Repositories\Contracts;

interface PaymentRepositoryInterface {
  public function getPaymentsGroupedByUser($today);
  public function updatePayouts($paymentIds);

  public function getPaymentsByIds($paymentIds);
}