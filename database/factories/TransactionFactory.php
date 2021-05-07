<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $amount = $this->faker->randomFloat(2, 10, 20);

        return [
            'wallet_from' => Wallet::factory(),
            'wallet_to' => Wallet::factory(),
            'transaction_status_id' => TransactionStatus::idByCode('pending'),
            'amount' => $amount,
            'fee' => $amount * 0.01,
            'rate' => 0.01,
        ];
    }
}
