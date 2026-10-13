<?php

namespace App\Console\Commands;

use App\Models\Configure;
use App\Models\CurrencyRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WalletCurrencyRateUpdate extends Command
{
    protected $signature = 'wallet:rates:update';

    protected $description = 'Update native wallet exchange rates using USD as the base currency.';

    public function handle()
    {
        $configuration = Configure::first();
        $accessKey = $configuration ? $configuration->currency_layer_access_key : null;
        if (!$accessKey) {
            $this->error('CurrencyLayer access key is not configured. Add it under Admin → Exchange API.');
            return 1;
        }

        $currencies = array_diff(array_keys(config('currencies.supported', [])), ['USD']);
        try {
            $response = Http::acceptJson()->timeout(20)->get('https://api.currencylayer.com/live', [
                'access_key' => $accessKey,
                'source' => 'USD',
                'currencies' => implode(',', $currencies),
            ]);
        } catch (\Throwable $exception) {
            $this->error('Could not reach CurrencyLayer over HTTPS. Check connectivity and the account plan.');
            return 1;
        }

        if (!$response->successful()) {
            $this->error('CurrencyLayer request failed with HTTP ' . $response->status() . '. Check that the account plan supports HTTPS.');
            return 1;
        }

        $payload = $response->json();
        if (!is_array($payload) || empty($payload['success']) || !is_array($payload['quotes'] ?? null)) {
            $this->error('CurrencyLayer did not return usable USD-based rates. Check the API key and plan.');
            return 1;
        }

        $timestamp = isset($payload['timestamp']) ? \Carbon\Carbon::createFromTimestamp($payload['timestamp']) : now();
        $updated = [];
        $missing = [];
        foreach ($currencies as $currency) {
            $quote = $payload['quotes']['USD' . $currency] ?? null;
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
            $this->warn('No rates were returned for: ' . implode(', ', $missing) . '. Wallet conversion for those currencies remains unavailable.');
            return 1;
        }

        return 0;
    }
}
