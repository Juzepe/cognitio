<?php

namespace Database\Seeders;

use App\Models\TransactionStatus;
use Illuminate\Database\Seeder;

class TransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transactionStatuses = [
            ['code' => 'pending', 'name' => 'Pending'],
            ['code' => 'reject', 'name' => 'Reject'],
            ['code' => 'confirmed', 'name' => 'Confirmed'],
        ];

        array_walk($transactionStatuses, function ($transactionStatus) {
            TransactionStatus::updateOrCreate([
                'code' => $transactionStatus['code'],
            ], [
                'name' => $transactionStatus['name'],
            ]);
        });
    }
}
