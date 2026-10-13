<?php

namespace App\Services;

use App\Models\PaymentProviderSetting;
use App\Models\WalletProviderTransaction;
use App\Services\Payments\PayHeroClient;
use App\Services\WalletProviderService;
use RuntimeException;

class WalletDepositReconciler
{
    /**
     * Reconcile a KES STK deposit using PayHero's provider-issued reference.
     * PayHero documents QUEUED, SUCCESS, and FAILED as transaction statuses.
     */
    public function syncPayHero(WalletProviderTransaction $entry)
    {
        if ($entry->provider !== 'payhero' || $entry->direction !== 'deposit') {
            throw new RuntimeException('Only PayHero deposits can be reconciled by this service.');
        }
        if (!in_array($entry->status, ['pending', 'processing'], true)) {
            return $entry;
        }

        $reference = trim((string)$entry->provider_reference);
        if ($reference === '') {
            throw new RuntimeException('PayHero has not returned a status reference for this deposit yet.');
        }

        $settings = PaymentProviderSetting::forProvider('payhero')->settings;
        $response = app(PayHeroClient::class)->transactionStatus($settings, $reference);
        $state = strtoupper(trim((string)(
            data_get($response, 'status')
            ?: data_get($response, 'data.status')
            ?: data_get($response, 'response.Status')
        )));
        $amount = data_get($response, 'amount')
            ?? data_get($response, 'data.amount')
            ?? data_get($response, 'response.Amount');
        $currency = strtoupper((string)(
            data_get($response, 'currency')
            ?: data_get($response, 'data.currency')
            ?: data_get($response, 'response.Currency')
        ));

        $metadata = (array)($entry->metadata ?: []);
        $metadata['payhero_status'] = $state ?: 'UNKNOWN';
        $metadata['payhero_status_checked_at'] = now()->toIso8601String();
        $entry->metadata = $metadata;
        $entry->last_status_checked_at = now();
        $entry->save();

        if ($state === 'SUCCESS') {
            if ((is_numeric($amount) && abs((float)$amount - (float)$entry->amount) > 0.02)
                || ($currency !== '' && $currency !== strtoupper($entry->currency_code))) {
                $metadata['manual_review_required'] = true;
                $metadata['manual_review_reason'] = 'Provider-confirmed amount or currency does not match the requested deposit.';
                $entry->metadata = $metadata;
                $entry->status = 'processing';
                $entry->failure_reason = 'Payment was confirmed but needs support review before wallet credit.';
                $entry->save();
                return $entry->fresh();
            }

            return app(WalletProviderService::class)->completeDeposit($entry->reference);
        }

        if ($state === 'FAILED') {
            $reason = data_get($response, 'ResultDesc')
                ?: data_get($response, 'data.ResultDesc')
                ?: 'PayHero reported a failed payment.';

            return app(WalletProviderService::class)->failDeposit($entry->reference, $reason);
        }

        // QUEUED and unknown/intermediate responses remain pending. Never credit
        // from an unauthenticated callback or from an ambiguous provider response.
        return $entry->fresh();
    }
}
