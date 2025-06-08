<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\ExchangeRate;

class ExchangeRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExchangeRate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'base_currency' => fake()->regexify('[A-Za-z0-9]{3}'),
            'target_currency' => fake()->regexify('[A-Za-z0-9]{3}'),
            'rate' => fake()->randomFloat(6, 0, 999999999.999999),
            'fetched_at' => fake()->dateTime(),
        ];
    }
}
