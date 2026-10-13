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
        $settings = PaymentProviderSetting::forProvider('currencyapi_com')->settings;
        $accessKey = $settings['access_key'] ?? null;
        if (!$accessKey) {
            $this->info('CurrencyAPI.com API key is not configured; wallet rate update skipped.');
            return 0;
        }

        $currencies = array_diff(array_keys(config('currencies.supported', [])), ['USD']);
        try {
            $response = Http::acceptJson()
                ->withHeaders(['apikey' => $accessKey])
                ->timeout(20)
                ->get('https://api.currencyapi.com/v3/latest', [
                    'base_currency' => 'USD',
                    'currencies' => implode(',', $currencies),
                ]);
        } catch (\Throwable $exception) {
            $this->error('Could not reach CurrencyAPI.com over HTTPS. Check connectivity and the account plan.');
            return 1;
        }

        if (!$response->successful()) {
            $this->error('CurrencyAPI.com request failed with HTTP ' . $response->status() . '. Check the API key and account plan.');
            return 1;
        }

        $payload = $response->json();
        if (!is_array($payload) || !is_array($payload['data'] ?? null)) {
            $this->error('CurrencyAPI.com did not return usable USD-based rates. Check the API key and account plan.');
            return 1;
        }

        $updatedAt = data_get($payload, 'meta.last_updated_at');
        try {
            $timestamp = is_string($updatedAt) && trim($updatedAt) !== ''
                ? \Carbon\Carbon::parse($updatedAt)
                : now();
        } catch (\Throwable $exception) {
            $timestamp = now();
        }
        $updated = [];
        $missing = [];
        foreach ($currencies as $currency) {
            $quote = data_get($payload, 'data.' . $currency . '.value');
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
                    ['usd_to_currency' => $rate, 'source' => 'currencyapi_com', 'fetched_at' => $timestamp]
                );
            }
        });

        $this->info('Updated ' . count($updated) . ' USD-based wallet rates.');
        if ($missing) {
            $this->warn('CurrencyAPI.com returned no rates for: ' . implode(', ', $missing) . '. Wallet conversion for those currencies remains unavailable.');
            return 1;
        }

        return 0;
    }
}
