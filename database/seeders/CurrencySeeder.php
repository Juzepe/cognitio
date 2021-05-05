<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            ['code' => 'GEL', 'name' => 'Georgian Lari'],
            ['code' => 'USD', 'name' => 'American Dollar'],
            ['code' => 'EUR', 'name' => 'Euro'],
        ];

        array_walk($currencies, function ($currency) {
            Currency::updateOrCreate([
                'code' => $currency['code'],
            ], [
                'name' => $currency['name'],
            ]);
        });
    }
}
