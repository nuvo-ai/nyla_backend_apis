<?php

namespace Database\Seeders;

use App\Constants\General\CurrencyConstants;
use App\Constants\General\StatusConstants;
use App\Models\General\Currency;
use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach (CurrencyConstants::CURRENCY_CODES as $code) {
            $currencies[] = [
                "name" => CurrencyConstants::CURRENCY_NAMES[$code],
                "short_name" => $code,
                "type" => $code,
                "symbol" => CurrencyConstants::CURRENCY_SYMBOLS[$code],
                "status" => StatusConstants::ACTIVE,
            ];
        }

        foreach ($currencies as $currency) {
            // Check for an existing record based on the unique 'symbol'
            $existingCurrency = Currency::where('symbol', $currency['symbol'])->first();

            if (!$existingCurrency) {
                Currency::create($currency);
            }
        }
    }
}
