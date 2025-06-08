<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\PaymentFile;
use App\Models\User;

class PaymentFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentFile::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'file_name' => fake()->word(),
            'path' => fake()->word(),
            'status' => fake()->randomElement(["uploaded","processing","processed","failed"]),
            'uploaded_at' => fake()->dateTime(),
            'processed_at' => fake()->dateTime(),
            'user_id' => User::factory(),
        ];
    }
}
