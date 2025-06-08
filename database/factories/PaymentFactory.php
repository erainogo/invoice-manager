<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Payment;
use App\Models\PaymentFile;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'payment_file_id' => PaymentFile::factory(),
            'customer_id' => fake()->word(),
            'customer_email' => fake()->word(),
            'customer_name' => fake()->word(),
            'reference_number' => fake()->word(),
            'payment_date' => fake()->dateTime(),
            'original_amount' => fake()->randomFloat(2, 0, 9999999999999.99),
            'original_currency' => fake()->regexify('[A-Za-z0-9]{3}'),
            'usd_amount' => fake()->randomFloat(2, 0, 9999999999999.99),
            'status' => fake()->randomElement(["unprocessed","processed","failed"]),
            'error_message' => fake()->text(),
        ];
    }
}
