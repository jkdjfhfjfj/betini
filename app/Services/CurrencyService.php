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
use Illuminate\Validation\ValidationException;
use RuntimeException;

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

        $rate = CurrencyRate::where('currency_code', $currencyCode)->value('usd_to_currency');
        if (!$rate || (float)$rate <= 0) {
            throw new RuntimeException('No USD exchange rate is available for ' . $currencyCode . '. Configure the CurrencyLayer key and update rates before converting balances.');
        }

        return (float)$rate;
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

    public function switchWalletCurrency(User $user, $targetCurrency)
    {
        $targetCurrency = strtoupper((string)$targetCurrency);
        if (!array_key_exists($targetCurrency, $this->supportedCurrencies())) {
            throw ValidationException::withMessages(['wallet_currency' => 'Choose a supported wallet currency.']);
        }

        return DB::transaction(function () use ($user, $targetCurrency) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $currentCurrency = strtoupper($lockedUser->wallet_currency ?: 'USD');

            if ($currentCurrency === $targetCurrency) {
                return $lockedUser;
            }

            $hasOpenBets = BetInvest::where('user_id', $lockedUser->id)->where('status', 0)->exists();
            $hasPendingWalletTransactions = WalletProviderTransaction::where('user_id', $lockedUser->id)
                ->whereIn('status', ['pending', 'processing'])
                ->exists();
            $hasPendingLegacyDeposit = Fund::where('user_id', $lockedUser->id)->whereIn('status', [0, 2])->exists();
            $hasPendingLegacyWithdrawal = PayoutLog::where('user_id', $lockedUser->id)->whereIn('status', [0, 1])->exists();
            if ($hasOpenBets || $hasPendingWalletTransactions || $hasPendingLegacyDeposit || $hasPendingLegacyWithdrawal) {
                throw ValidationException::withMessages([
                    'wallet_currency' => 'Wallet currency cannot be changed while a bet or payment is still pending.',
                ]);
            }

            $oldBalance = (float)$lockedUser->balance;
            $oldRate = $this->rateFor($currentCurrency);
            $newRate = $this->rateFor($targetCurrency);
            $newBalance = round(($oldBalance / $oldRate) * $newRate, 2);

            $lockedUser->balance = $newBalance;
            $lockedUser->wallet_currency = $targetCurrency;
            $lockedUser->save();

            app(BasicService::class)->makeTransaction(
                $lockedUser,
                0,
                0,
                '+',
                strRandom(),
                'Wallet balance converted from ' . $currentCurrency . ' ' . number_format($oldBalance, 2, '.', '') .
                ' to ' . $targetCurrency . ' ' . number_format($newBalance, 2, '.', '') .
                ' using USD as the conversion base.'
            );

            return $lockedUser;
        });
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

        return array_replace_recursive($defaults, $settings);
    }
}
