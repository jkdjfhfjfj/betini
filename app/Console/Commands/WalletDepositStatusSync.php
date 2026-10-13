<?php

namespace App\Console\Commands;

use App\Models\WalletProviderTransaction;
use App\Services\WalletDepositReconciler;
use Illuminate\Console\Command;

class WalletDepositStatusSync extends Command
{
    protected $signature = 'wallet:deposits:sync';

    protected $description = 'Reconcile pending PayHero wallet deposits against provider status.';

    public function handle()
    {
        $cutoff = now()->subMinutes(5);
        $entries = WalletProviderTransaction::where('provider', 'payhero')
            ->where('direction', 'deposit')
            ->whereIn('status', ['pending', 'processing'])
            ->whereNotNull('provider_reference')
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('last_status_checked_at')
                    ->orWhere('last_status_checked_at', '<=', $cutoff);
            })
            ->orderByRaw('CASE WHEN last_status_checked_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('last_status_checked_at')
            ->orderBy('id')
            ->limit(50)
            ->get();

        foreach ($entries as $entry) {
            try {
                app(WalletDepositReconciler::class)->syncPayHero($entry);
            } catch (\Throwable $exception) {
                report($exception);
                $this->warn('Deposit ' . $entry->reference . ' could not be confirmed; it remains pending for the next check.');
            }

            $entry->refresh();
            $entry->last_status_checked_at = now();
            $entry->save();
        }

        $this->info('Checked ' . $entries->count() . ' pending PayHero deposit(s).');
        return 0;
    }
}
