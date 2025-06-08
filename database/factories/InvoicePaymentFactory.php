<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Payment;

class InvoicePaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoicePayment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'payment_id' => Payment::factory(),
        ];
    }
}
