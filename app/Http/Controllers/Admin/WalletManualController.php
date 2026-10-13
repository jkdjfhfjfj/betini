<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Notify;
use App\Models\WalletProviderTransaction;
use App\Services\WalletProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class WalletManualController extends Controller
{
    use Notify;

    public function index(Request $request)
    {
        $direction = $request->input('direction', 'all');
        $status = $request->input('status', 'pending');

        $query = WalletProviderTransaction::with('user')
            ->where('provider', 'manual')
            ->orderByDesc('id');

        if (in_array($direction, ['deposit', 'withdrawal'], true)) {
            $query->where('direction', $direction);
        } else {
            $direction = 'all';
        }

        if (in_array($status, ['pending', 'processing', 'succeeded', 'failed'], true)) {
            $query->where('status', $status);
        } else {
            $status = 'all';
        }

        $transactions = $query->paginate(25)->withQueryString();

        return view('admin.wallet-manual.index', [
            'page_title' => 'Manual Wallet Requests',
            'transactions' => $transactions,
            'direction' => $direction,
            'status' => $status,
        ]);
    }

    public function review(Request $request, $id)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'feedback' => 'nullable|string|max:500',
            'provider_reference' => 'nullable|string|max:191',
        ]);

        $feedback = trim((string)($data['feedback'] ?? ''));
        if ($data['action'] === 'reject' && $feedback === '') {
            return back()->withErrors(['feedback' => 'Add a reason before rejecting this request.']);
        }

        try {
            $entry = DB::transaction(function () use ($id, $data, $feedback) {
                $transaction = WalletProviderTransaction::whereKey($id)
                    ->where('provider', 'manual')
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array($transaction->status, ['pending', 'processing'], true)) {
                    throw new RuntimeException('This manual wallet request has already been reviewed.');
                }
                if (!in_array($transaction->direction, ['deposit', 'withdrawal'], true)) {
                    throw new RuntimeException('This manual wallet request has an unsupported direction.');
                }

                $metadata = (array)($transaction->metadata ?: []);
                $metadata['admin_review'] = [
                    'admin_id' => auth('admin')->id(),
                    'action' => $data['action'],
                    'feedback' => $feedback,
                    'reviewed_at' => now()->toIso8601String(),
                ];
                $transaction->metadata = $metadata;
                if (!empty($data['provider_reference'])) {
                    $transaction->provider_reference = trim($data['provider_reference']);
                }
                $transaction->save();

                $walletService = app(WalletProviderService::class);
                if ($data['action'] === 'approve') {
                    if ($transaction->direction === 'deposit') {
                        $walletService->completeDeposit($transaction->reference);
                    } else {
                        $walletService->completeWithdrawal($transaction->reference);
                    }
                } elseif ($transaction->direction === 'deposit') {
                    $walletService->failDeposit($transaction->reference, $feedback);
                } else {
                    $walletService->failWithdrawal($transaction->reference, $feedback);
                }

                return $transaction;
            });
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $this->notifyUser($entry, $data['action'], $feedback);

        $actionMessage = $data['action'] === 'approve'
            ? ($entry->direction === 'withdrawal' ? 'Manual withdrawal marked as paid.' : 'Manual deposit approved and wallet credited.')
            : ($entry->direction === 'withdrawal' ? 'Manual withdrawal rejected and reserved funds returned.' : 'Manual deposit rejected.');

        return back()->with('success', $actionMessage);
    }

    private function notifyUser(WalletProviderTransaction $entry, $action, $feedback)
    {
        $user = $entry->user;
        if (!$user) {
            return;
        }

        $amount = getAmount($entry->amount);
        $currency = $entry->currency_code;
        $isDeposit = $entry->direction === 'deposit';
        $approved = $action === 'approve';
        $type = $isDeposit
            ? ($approved ? 'PAYMENT_APPROVED' : 'DEPOSIT_REJECTED')
            : ($approved ? 'PAYOUT_APPROVE' : 'PAYOUT_REJECTED');
        $method = $isDeposit
            ? data_get($entry->metadata, 'gateway_name', 'Manual deposit')
            : data_get($entry->metadata, 'payout_method', 'Manual payout');

        $message = [
            'amount' => $amount,
            'currency' => $currency,
            'transaction' => $entry->reference,
            'feedback' => $feedback,
            'method' => $method,
            'gateway_name' => $method,
            'charge' => getAmount($entry->fee),
        ];
        $pushType = $isDeposit && !$approved ? 'PAYMENT_REJECTED' : $type;
        $pushMessage = [
            'amount' => $amount,
            'currency' => $currency,
            'transaction' => $entry->reference,
            'feedback' => $feedback,
            'method' => $method,
        ];
        $actionLink = [
            'link' => '#',
            'icon' => 'fas fa-wallet text-white',
        ];

        try {
            $this->sendMailSms($user, $type, $message);
            $this->userPushNotification($user, $pushType, $pushMessage, $actionLink);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
