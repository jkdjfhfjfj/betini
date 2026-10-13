<?php

namespace App\Services;

use App\Models\BetInvest;
use App\Models\CurrencyRate;
use App\Models\Fund;
use App\Models\PaymentProviderSetting;
use App\Models\PayoutLog;
use App\Models\User;
use App\Models\WalletProviderTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Illuminate\Support\Str;

class CurrencyService
{
    public function supportedCurrencies()
    {
        return config('currencies.supported', []);
    }

    public function currencyForCountry($countryCode)
    {
        $countryCode = strtoupper((string)$countryCode);
        return config('currencies.country_defaults.' . $countryCode, 'USD');
    }

    public function symbol($currencyCode)
    {
        return data_get($this->supportedCurrencies(), strtoupper((string)$currencyCode) . '.symbol', strtoupper((string)$currencyCode));
    }

    /**
     * Returns units of the requested currency per one USD.
     */
    public function rateFor($currencyCode)
    {
        $currencyCode = strtoupper((string)$currencyCode);
        if (!array_key_exists($currencyCode, $this->supportedCurrencies())) {
            throw new RuntimeException('Unsupported currency: ' . $currencyCode);
        }

        if ($currencyCode === 'USD') {
            return 1.0;
        }

        $rate = CurrencyRate::where('currency_code', $currencyCode)->first();
        if (!$rate || (float)$rate->usd_to_currency <= 0) {
            throw new RuntimeException('No USD exchange rate is available for ' . $currencyCode . '. Configure the CurrencyLayer access key and update rates before converting balances.');
        }
        if ($rate->source !== 'currencylayer'
            || !$rate->fetched_at
            || \Carbon\Carbon::parse($rate->fetched_at)->lt(now()->subHours(48))) {
            throw new RuntimeException('The USD exchange rate for ' . $currencyCode . ' is missing or stale. Configure CurrencyLayer and update rates before converting balances.');
        }

        return (float)$rate->usd_to_currency;
    }

    public function convert($amount, $fromCurrency, $toCurrency, $precision = 2)
    {
        $fromCurrency = strtoupper((string)$fromCurrency);
        $toCurrency = strtoupper((string)$toCurrency);
        if ($fromCurrency === $toCurrency) {
            return round((float)$amount, $precision);
        }

        $usdValue = (float)$amount / $this->rateFor($fromCurrency);
        return round($usdValue * $this->rateFor($toCurrency), $precision);
    }

    public function initializeUser(User $user)
    {
        if (!$user->wallet_currency) {
            $user->wallet_currency = $this->currencyForCountry($user->country_code);
            $user->save();
        }

        return $user;
    }

    public function conversionFeePercent()
    {
        $settings = PaymentProviderSetting::forProvider('currencylayer')->settings;
        if (!array_key_exists('conversion_fee_percent', $settings)) {
            $legacySettings = PaymentProviderSetting::forProvider('currencyapi')->settings;
            $settings['conversion_fee_percent'] = $legacySettings['conversion_fee_percent'] ?? 0;
        }
        $fee = $settings['conversion_fee_percent'] ?? 0;
        if (!is_numeric($fee) || (float)$fee < 0 || (float)$fee > 100) {
            throw new RuntimeException('The wallet conversion fee setting must be between 0 and 100 percent.');
        }

        return (float)$fee;
    }

    public function walletCurrencyQuote(User $user, $targetCurrency)
    {
        $targetCurrency = strtoupper((string)$targetCurrency);
        if (!array_key_exists($targetCurrency, $this->supportedCurrencies())) {
            throw ValidationException::withMessages(['wallet_currency' => 'Choose a supported wallet currency.']);
        }

        $currentCurrency = strtoupper($user->wallet_currency ?: 'USD');
        if ($currentCurrency === $targetCurrency) {
            return [
                'from_currency' => $currentCurrency,
                'to_currency' => $targetCurrency,
                'current_balance' => round((float)$user->balance, 2),
                'gross_amount' => round((float)$user->balance, 2),
                'fee_percent' => 0,
                'fee_amount' => 0,
                'net_amount' => round((float)$user->balance, 2),
                'token' => null,
            ];
        }

        $this->assertWalletCurrencyCanChange($user);
        $currentBalance = round((float)$user->balance, 2);
        $fromRate = $this->rateFor($currentCurrency);
        $toRate = $this->rateFor($targetCurrency);
        $grossAmount = round(($currentBalance / $fromRate) * $toRate, 2);
        $feePercent = $this->conversionFeePercent();
        $feeAmount = round($grossAmount * ($feePercent / 100), 2);
        $quote = [
            'user_id' => (int)$user->id,
            'from_currency' => $currentCurrency,
            'to_currency' => $targetCurrency,
            'current_balance' => $currentBalance,
            'from_rate' => $fromRate,
            'to_rate' => $toRate,
            'gross_amount' => $grossAmount,
            'fee_percent' => $feePercent,
            'fee_amount' => $feeAmount,
            'net_amount' => round($grossAmount - $feeAmount, 2),
            'issued_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ];
        $quote['token'] = Crypt::encryptString(json_encode($quote));

        return $quote;
    }

    public function switchWalletCurrency(User $user, $targetCurrency, $quoteToken)
    {
        $targetCurrency = strtoupper((string)$targetCurrency);
        if (!array_key_exists($targetCurrency, $this->supportedCurrencies())) {
            throw ValidationException::withMessages(['wallet_currency' => 'Choose a supported wallet currency.']);
        }
        if (!is_string($quoteToken) || trim($quoteToken) === '') {
            throw ValidationException::withMessages(['wallet_currency' => 'Preview and confirm the conversion before changing your wallet currency.']);
        }

        try {
            $quote = json_decode(Crypt::decryptString($quoteToken), true);
        } catch (\Throwable $exception) {
            $quote = null;
        }
        if (!is_array($quote)
            || (int)($quote['user_id'] ?? 0) !== (int)$user->id
            || strtoupper((string)($quote['to_currency'] ?? '')) !== $targetCurrency
            || !is_numeric($quote['expires_at'] ?? null)
            || (int)$quote['expires_at'] < now()->timestamp
            || !is_numeric($quote['issued_at'] ?? null)
            || (int)$quote['issued_at'] > now()->addSeconds(10)->timestamp) {
            throw ValidationException::withMessages(['wallet_currency' => 'This conversion quote has expired or is invalid. Preview the conversion again.']);
        }

        $quoteUseKey = 'wallet-currency-quote-used:' . hash('sha256', $quoteToken);

        return DB::transaction(function () use ($user, $targetCurrency, $quote, $quoteUseKey) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $currentCurrency = strtoupper($lockedUser->wallet_currency ?: 'USD');

            if ($currentCurrency === $targetCurrency) {
                return $lockedUser;
            }

            if ($currentCurrency !== strtoupper((string)($quote['from_currency'] ?? ''))
                || round((float)$lockedUser->balance, 2) !== round((float)($quote['current_balance'] ?? -1), 2)
                || abs($this->conversionFeePercent() - (float)($quote['fee_percent'] ?? -1)) > 0.000001) {
                throw ValidationException::withMessages(['wallet_currency' => 'Your balance or conversion fee changed after this quote. Preview the conversion again.']);
            }
            $this->assertWalletCurrencyCanChange($lockedUser);

            $oldBalance = round((float)$lockedUser->balance, 2);
            $oldRate = (float)$quote['from_rate'];
            $newRate = (float)$quote['to_rate'];
            if ($oldRate <= 0 || $newRate <= 0) {
                throw ValidationException::withMessages(['wallet_currency' => 'The exchange-rate quote is invalid. Preview the conversion again.']);
            }
            $grossAmount = round(($oldBalance / $oldRate) * $newRate, 2);
            $feeAmount = round($grossAmount * ((float)$quote['fee_percent'] / 100), 2);
            $newBalance = round($grossAmount - $feeAmount, 2);
            if (abs($grossAmount - (float)$quote['gross_amount']) > 0.01
                || abs($feeAmount - (float)$quote['fee_amount']) > 0.01
                || abs($newBalance - (float)$quote['net_amount']) > 0.01) {
                throw ValidationException::withMessages(['wallet_currency' => 'The conversion quote could not be verified. Preview the conversion again.']);
            }
            if (!Cache::add($quoteUseKey, true, now()->addMinutes(10))) {
                throw ValidationException::withMessages(['wallet_currency' => 'This conversion quote has already been used. Preview the conversion again.']);
            }

            $lockedUser->balance = $newBalance;
            $lockedUser->wallet_currency = $targetCurrency;
            $lockedUser->save();

            WalletProviderTransaction::create([
                'user_id' => $lockedUser->id,
                'reference' => 'WCV' . strtoupper(Str::random(18)),
                'provider' => 'currencylayer',
                'direction' => 'conversion',
                'currency_code' => $targetCurrency,
                'amount' => $grossAmount,
                'fee' => $feeAmount,
                'net_amount' => $newBalance,
                'usd_rate' => $newRate,
                'status' => 'succeeded',
                'metadata' => [
                    'from_currency' => $currentCurrency,
                    'from_amount' => $oldBalance,
                    'from_usd_rate' => $oldRate,
                    'to_usd_rate' => $newRate,
                    'fee_percent' => (float)$quote['fee_percent'],
                ],
                'credited_at' => now(),
            ]);

            return $lockedUser;
        });
    }

    private function assertWalletCurrencyCanChange(User $user)
    {
        $openBet = BetInvest::where('user_id', $user->id)->where('status', 0)->first();
        if ($openBet) {
            throw ValidationException::withMessages(['wallet_currency' => 'An open bet is still unsettled. Review your bet history before changing wallet currency.']);
        }

        $walletTransaction = WalletProviderTransaction::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('id')
            ->first();
        if ($walletTransaction) {
            throw ValidationException::withMessages([
                'wallet_currency' => 'Wallet transaction ' . $walletTransaction->reference . ' is still ' . $walletTransaction->status . '. Wait for its final status before changing currency.',
            ]);
        }

        $legacyDeposit = Fund::where('user_id', $user->id)->whereIn('status', [0, 2])->orderBy('id')->first();
        if ($legacyDeposit) {
            throw ValidationException::withMessages([
                'wallet_currency' => 'A legacy deposit request is still pending. Check your deposit history or ask support to review it.',
            ]);
        }

        // Legacy payout status 0 is only the unconfirmed preview step. Status 1
        // is the submitted payout that has actually been debited and must settle.
        $legacyPayout = PayoutLog::where('user_id', $user->id)->where('status', 1)->orderBy('id')->first();
        if ($legacyPayout) {
            throw ValidationException::withMessages([
                'wallet_currency' => 'A submitted withdrawal is still processing. Wait for payout ' . $legacyPayout->trx_id . ' to settle before changing currency.',
            ]);
        }
    }

    public function rateSnapshot($currencyCode)
    {
        return $this->rateFor($currencyCode);
    }

    public function providerRoutes()
    {
        $settings = PaymentProviderSetting::forProvider('routing')->settings;
        $defaults = [];
        foreach (array_keys($this->supportedCurrencies()) as $currency) {
            $defaults[$currency] = [
                'deposit' => $currency === 'KES' ? 'payhero' : ($currency === 'USD' ? 'paystack' : 'payza'),
                'withdrawal' => $currency === 'KES' ? 'makamesco' : 'payza',
            ];
        }

        $routes = array_replace_recursive($defaults, $settings);
        $routes['KES'] = [
            'deposit' => 'payhero',
            'withdrawal' => 'makamesco',
        ];

        return $routes;
    }
}
