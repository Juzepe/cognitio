<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\LatestCurrencyRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch-currency-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch currency rates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $currencies = Currency::all();

        $currencies->each(function ($currency) use ($currencies) {
            $allOtherCurrencies = $this->allOtherCurrencies($currencies, $currency);

            $rates = $this->rates($this->currencyQueryString($allOtherCurrencies, $currency));

            $allOtherCurrencies->each(function ($cur) use ($currency, $rates) {
                if (isset($rates[$currency->code . '_' . $cur->code])) {
                    $this->saveCurrencies($currency, $cur, $rates);
                }
            }) ;
        });
    }

    private function allOtherCurrencies($currencies, $currency)
    {
        return $currencies->filter(function ($cur) use ($currency) {
            return $cur->code != $currency->code;
        });
    }

    private function currencyQueryString($allOtherCurrencies, $currency)
    {
        $currencyPairs = $allOtherCurrencies->reduce(function ($carry, $cur) use ($currency) {
            return array_merge($carry, [$currency->code . '_' . $cur->code]);
        }, []);

        return implode(',', $currencyPairs);
    }

    private function rates($query)
    {
        return Http::get('https://free.currconv.com/api/v7/convert', [
            'apiKey' => '8a4504a15715912f3987',
            'compact' => 'ultra',
            'q' => $query,
        ])->json();
    }

    private function saveCurrencies($currency, $cur, $rates)
    {
        CurrencyRate::create([
            'currency_from' => $currency->id,
            'currency_to' => $cur->id,
            'rate' => $rates[$currency->code . '_' . $cur->code],
        ]);

        LatestCurrencyRate::updateOrCreate([
            'currency_from' => $currency->id,
            'currency_to' => $cur->id,
        ], [
            'rate' => $rates[$currency->code . '_' . $cur->code],
        ]);
    }
}
