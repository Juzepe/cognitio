<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\LatestCurrencyRate;
use Illuminate\Database\Eloquent\Factories\Factory;

class LatestCurrencyRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LatestCurrencyRate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'currency_from' => Currency::factory(),
            'currency_to' => Currency::factory(),
            'rate' => $this->faker->randomFloat(2, 1, 4),
        ];
    }
}
