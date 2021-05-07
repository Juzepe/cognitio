<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'currency_id' => Currency::factory(),
            'name' => $this->faker->catchPhrase,
            'amount' => $this->faker->randomFloat(2, 1000, 2000),
        ];
    }
}
