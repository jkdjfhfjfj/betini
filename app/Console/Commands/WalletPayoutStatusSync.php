<?php

namespace App\Console\Commands;

use App\Models\PaymentProviderSetting;
use App\Models\WalletProviderTransaction;
use App\Services\Payments\MakamescoClient;
use App\Services\Payments\PayzaClient;
use App\Services\WalletProviderService;
use Illuminate\Console\Command;

class WalletPayoutStatusSync extends Command
{
    protected $signature = 'wallet:payouts:sync';

    protected $description = 'Check pending Payza and Makamesco wallet withdrawals.';

    public function handle()
    {
        $entries = WalletProviderTransaction::where('direction', 'withdrawal')
            ->whereIn('status', ['pending', 'processing'])
            ->whereIn('provider', ['payza', 'makamesco'])
            ->orderBy('id')
            ->limit(100)
            ->get();

        foreach ($entries as $entry) {
            try {
                $settings = PaymentProviderSetting::forProvider($entry->provider)->settings;
                if ($entry->provider === 'payza') {
                    $response = app(PayzaClient::class)->payoutStatus($settings, $entry->provider_reference ?: $entry->reference);
                    $payout = data_get($response, 'data.payout') ?: data_get($response, 'data') ?: $response;
                    if (is_array($payout) && array_is_list($payout)) {
                        $payout = $payout[0] ?? [];
                    }
                    $status = strtolower((string)(data_get($payout, 'status') ?: data_get($response, 'status')));
                    if (in_array($status, ['completed', 'success', 'succeeded'], true)) {
                        app(WalletProviderService::class)->completeWithdrawal($entry->reference);
                    } elseif (in_array($status, ['rejected', 'failed'], true)) {
                        app(WalletProviderService::class)->failWithdrawal($entry->reference, 'Payza reported a ' . $status . ' payout.');
                    }
                } else {
                    $response = app(MakamescoClient::class)->b2cStatus($settings, $entry->provider_reference ?: $entry->reference);
                    $status = strtolower((string)data_get($response, 'status'));
                    if ($status === 'completed') {
                        app(WalletProviderService::class)->completeWithdrawal($entry->reference);
                    } elseif ($status === 'failed') {
                        app(WalletProviderService::class)->failWithdrawal($entry->reference, 'Makamesco reported a failed B2C payout.');
                    }
                }
            } catch (\Throwable $exception) {
                report($exception);
                $this->warn('A pending wallet payout could not be checked. It remains pending for the next sync.');
            }
        }

        $this->info('Checked ' . $entries->count() . ' pending wallet payout(s).');
        return 0;
    }
}
