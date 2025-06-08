<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Invoice;

class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_email' => fake()->word(),
            'html_content' => fake()->text(),
            'total_usd' => fake()->randomFloat(2, 0, 9999999999999.99),
            'sent_at' => fake()->dateTime(),
        ];
    }
}
