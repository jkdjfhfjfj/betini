<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletProviderTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletProviderService
{
    public function completeDeposit($reference)
    {
        return DB::transaction(function () use ($reference) {
            $entry = WalletProviderTransaction::where('reference', $reference)->lockForUpdate()->firstOrFail();
            if ($entry->direction !== 'deposit') {
                throw new RuntimeException('The provider reference is not a deposit.');
            }
            if ($entry->status === 'succeeded') {
                return $entry;
            }
            if (!in_array($entry->status, ['pending', 'processing'], true)) {
                return $entry;
            }

            $user = User::whereKey($entry->user_id)->lockForUpdate()->firstOrFail();
            if (strtoupper($user->wallet_currency ?: 'USD') !== strtoupper($entry->currency_code)) {
                throw new RuntimeException('The wallet currency changed while this deposit was pending; credit was stopped for review.');
            }

            $user->balance = round((float)$user->balance + (float)$entry->net_amount, 2);
            $user->save();

            $entry->status = 'succeeded';
            $entry->credited_at = now();
            $entry->failure_reason = null;
            $entry->save();

            app(BasicService::class)->makeTransaction(
                $user,
                $entry->net_amount,
                $entry->fee,
                '+',
                $entry->reference,
                'Deposit via ' . ucfirst($entry->provider)
            );

            return $entry;
        });
    }

    public function failDeposit($reference, $reason)
    {
        return DB::transaction(function () use ($reference, $reason) {
            $entry = WalletProviderTransaction::where('reference', $reference)->lockForUpdate()->firstOrFail();
            if ($entry->direction !== 'deposit' || $entry->status === 'succeeded') {
                return $entry;
            }
            if (in_array($entry->status, ['pending', 'processing'], true)) {
                $entry->status = 'failed';
                $entry->failure_reason = mb_substr((string)$reason, 0, 500);
                $entry->save();
            }

            return $entry;
        });
    }

    public function completeWithdrawal($reference)
    {
        return DB::transaction(function () use ($reference) {
            $entry = WalletProviderTransaction::where('reference', $reference)->lockForUpdate()->firstOrFail();
            if ($entry->direction !== 'withdrawal') {
                throw new RuntimeException('The provider reference is not a withdrawal.');
            }
            if (in_array($entry->status, ['pending', 'processing'], true)) {
                $entry->status = 'succeeded';
                $entry->failure_reason = null;
                $entry->save();
            }

            return $entry;
        });
    }

    public function failWithdrawal($reference, $reason)
    {
        return DB::transaction(function () use ($reference, $reason) {
            $entry = WalletProviderTransaction::where('reference', $reference)->lockForUpdate()->firstOrFail();
            if ($entry->direction !== 'withdrawal' || $entry->status === 'succeeded') {
                return $entry;
            }
            if (!in_array($entry->status, ['pending', 'processing'], true)) {
                return $entry;
            }

            $user = User::whereKey($entry->user_id)->lockForUpdate()->firstOrFail();
            if (!$entry->refunded_at) {
                $user->balance = round((float)$user->balance + (float)$entry->amount, 2);
                $user->save();
                $entry->refunded_at = now();
                app(BasicService::class)->makeTransaction(
                    $user,
                    $entry->amount,
                    $entry->fee,
                    '+',
                    $entry->reference . '-refund',
                    'Failed withdrawal via ' . ucfirst($entry->provider) . ' refunded'
                );
            }

            $entry->status = 'failed';
            $entry->failure_reason = mb_substr((string)$reason, 0, 500);
            $entry->save();

            return $entry;
        });
    }
}
