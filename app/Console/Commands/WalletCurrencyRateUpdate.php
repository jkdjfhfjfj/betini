<?php

namespace App\Console\Commands;

use App\Models\CurrencyRate;
use App\Models\PaymentProviderSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WalletCurrencyRateUpdate extends Command
{
    protected $signature = 'wallet:rates:update';

    protected $description = 'Update native wallet exchange rates using USD as the base currency.';

    public function handle()
    {
        $settings = PaymentProviderSetting::forProvider('currencylayer')->settings;
        $accessKey = $settings['access_key'] ?? null;
        if (!$accessKey) {
            $this->info('CurrencyLayer access key is not configured; wallet rate update skipped.');
            return 0;
        }

        $currencies = array_diff(array_keys(config('currencies.supported', [])), ['USD']);
        try {
            $response = Http::acceptJson()
                ->timeout(20)
                ->get('https://api.currencylayer.com/live', [
                    'access_key' => $accessKey,
                    'source' => 'USD',
                    'currencies' => implode(',', $currencies),
                ]);
        } catch (\Throwable $exception) {
            $this->error('Could not reach CurrencyLayer over HTTPS. Check connectivity and the account plan.');
            return 1;
        }

        if (!$response->successful()) {
            $this->error('CurrencyLayer request failed with HTTP ' . $response->status() . '. Check the access key and account plan.');
            return 1;
        }

        $payload = $response->json();
        if (!is_array($payload) || empty($payload['success']) || !is_array($payload['quotes'] ?? null)) {
            $this->error('CurrencyLayer did not return usable USD-based rates. Check the access key and account plan.');
            return 1;
        }

        $updatedAt = data_get($payload, 'timestamp');
        $timestamp = is_numeric($updatedAt)
            ? \Carbon\Carbon::createFromTimestampUTC((int)$updatedAt)
            : now();
        $updated = [];
        $missing = [];
        foreach ($currencies as $currency) {
            $quote = data_get($payload, 'quotes.USD' . $currency);
            if (!is_numeric($quote) || (float)$quote <= 0) {
                $missing[] = $currency;
                continue;
            }

            $updated[$currency] = (float)$quote;
        }

        DB::transaction(function () use ($updated, $timestamp) {
            CurrencyRate::updateOrCreate(
                ['currency_code' => 'USD'],
                ['usd_to_currency' => 1, 'source' => 'base', 'fetched_at' => $timestamp]
            );

            foreach ($updated as $currency => $rate) {
                CurrencyRate::updateOrCreate(
                    ['currency_code' => $currency],
                    ['usd_to_currency' => $rate, 'source' => 'currencylayer', 'fetched_at' => $timestamp]
                );
            }
        });

        $this->info('Updated ' . count($updated) . ' USD-based wallet rates.');
        if ($missing) {
            $this->warn('CurrencyLayer returned no rates for: ' . implode(', ', $missing) . '. Wallet conversion for those currencies remains unavailable.');
            return 1;
        }

        return 0;
    }
}
