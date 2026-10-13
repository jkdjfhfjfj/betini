<?php

namespace App\Http\Controllers;

use App\Models\PaymentProviderSetting;
use App\Models\WalletProviderTransaction;
use App\Services\Payments\PaystackClient;
use App\Services\Payments\PayzaClient;
use App\Services\WalletDepositReconciler;
use App\Services\WalletProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use RuntimeException;

class WalletWebhookController extends Controller
{
    public function payhero(Request $request)
    {
        $payload = $request->all();
        $reference = data_get($payload, 'response.ExternalReference')
            ?: data_get($payload, 'ExternalReference')
            ?: data_get($payload, 'external_reference')
            ?: data_get($payload, 'reference');

        if (!$reference) {
            return response()->json(['error' => 'Missing reference.'], 400);
        }

        $entry = WalletProviderTransaction::where('reference', $reference)
            ->where('provider', 'payhero')
            ->where('direction', 'deposit')
            ->first();
        if (!$entry) {
            return response()->json(['error' => 'Unknown payment reference.'], 404);
        }

        $callback = data_get($payload, 'response');
        if (!is_array($callback)) {
            $callback = $payload;
        }
        $metadata = (array)($entry->metadata ?: []);
        $metadata['payhero_callback'] = [
            'status' => data_get($callback, 'Status') ?: data_get($callback, 'status'),
            'result_code' => data_get($callback, 'ResultCode'),
            'result_description' => mb_substr((string)(data_get($callback, 'ResultDesc') ?: ''), 0, 250),
            'checkout_request_id' => data_get($callback, 'CheckoutRequestID'),
            'receipt_number' => data_get($callback, 'MpesaReceiptNumber'),
            'received_at' => now()->toIso8601String(),
        ];
        $entry->metadata = $metadata;
        $entry->save();

        try {
            app(WalletDepositReconciler::class)->syncPayHero($entry);
            return response()->json(['received' => true]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['error' => 'Payment status could not be verified.'], 503);
        }
    }

    public function payza(Request $request)
    {
        $settings = PaymentProviderSetting::forProvider('payza')->settings;
        $signingSecret = $settings['webhook_secret'] ?? null;
        if (!$signingSecret) {
            return response()->json(['error' => 'Webhook verification is not configured.'], 503);
        }

        $signature = (string)$request->header('X-Payza-Signature', '');
        $signature = preg_replace('/^sha256=/i', '', $signature);
        $expected = hash_hmac('sha256', $request->getContent(), $signingSecret);
        if (!$signature || !hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }

        $payload = $request->json()->all();
        $reference = $payload['reference'] ?? data_get($payload, 'metadata.wallet_reference');
        if (!$reference) {
            return response()->json(['error' => 'Missing reference.'], 400);
        }

        $entry = WalletProviderTransaction::where('provider', 'payza')
            ->where(function ($query) use ($reference) {
                $query->where('reference', $reference)->orWhere('provider_reference', $reference);
            })
            ->first();
        if (!$entry) {
            return response()->json(['received' => true]);
        }

        $event = strtolower((string)($payload['event'] ?? ''));
        if ($entry->direction === 'deposit' && in_array($event, ['payment.success', 'payment.failed', 'payment.cancelled'], true)) {
            if ($event === 'payment.success') {
                $status = strtolower((string)($payload['status'] ?? ''));
                if ($status === 'success') {
                    $amount = $payload['amount'] ?? null;
                    $currency = strtoupper((string)($payload['currency'] ?? ''));
                    if ($currency !== strtoupper($entry->currency_code)
                        || !is_numeric($amount)
                        || abs((float)$amount - (float)$entry->amount) > 0.02) {
                        app(WalletProviderService::class)->failDeposit(
                            $entry->reference,
                            'The signed payment amount or currency did not match this deposit.'
                        );
                        return response()->json(['received' => true]);
                    }
                    app(WalletProviderService::class)->completeDeposit($entry->reference);
                }
            } else {
                app(WalletProviderService::class)->failDeposit(
                    $entry->reference,
                    'Payza reported a ' . str_replace('payment.', '', $event) . ' payment.'
                );
            }
        }

        return response()->json(['received' => true]);
    }

    public function payzaReturn(Request $request)
    {
        $reference = (string)$request->query('reference', '');
        $entry = WalletProviderTransaction::where('reference', $reference)
            ->where('provider', 'payza')
            ->where('direction', 'deposit')
            ->first();
        if (!$entry) {
            return redirect()->route('login')->with('error', 'The payment reference could not be found.');
        }

        try {
            $settings = PaymentProviderSetting::forProvider('payza')->settings;
            $verified = app(PayzaClient::class)->verifyPayment($settings, $reference);
            $status = strtolower((string)(
                data_get($verified, 'data.status')
                ?: data_get($verified, 'data.payment.status')
                ?: data_get($verified, 'data.transaction.status')
                ?: data_get($verified, 'status')
            ));
            $amount = data_get($verified, 'data.amount')
                ?? data_get($verified, 'data.payment.amount')
                ?? data_get($verified, 'data.transaction.amount')
                ?? data_get($verified, 'amount');
            $currency = strtoupper((string)(
                data_get($verified, 'data.currency')
                ?: data_get($verified, 'data.payment.currency')
                ?: data_get($verified, 'data.transaction.currency')
                ?: data_get($verified, 'currency')
            ));

            if (in_array($status, ['success', 'succeeded', 'paid', 'completed'], true)) {
                if ($currency === strtoupper($entry->currency_code)
                    && is_numeric($amount)
                    && abs((float)$amount - (float)$entry->amount) <= 0.02) {
                    app(WalletProviderService::class)->completeDeposit($entry->reference);
                } else {
                    app(WalletProviderService::class)->failDeposit($entry->reference, 'The confirmed payment amount or currency did not match this deposit.');
                }
            } elseif (in_array($status, ['failed', 'cancelled', 'canceled', 'rejected', 'expired'], true)) {
                app(WalletProviderService::class)->failDeposit($entry->reference, 'Payza reported a failed or cancelled payment.');
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('user.wallet', ['reference' => $reference]);
    }

    public function paystack(Request $request)
    {
        $settings = PaymentProviderSetting::forProvider('paystack')->settings;
        $secretKey = $settings['secret_key'] ?? null;
        if (!$secretKey) {
            return response()->json(['error' => 'Webhook verification is not configured.'], 503);
        }

        $signature = (string)$request->header('x-paystack-signature', '');
        $expected = hash_hmac('sha512', $request->getContent(), $secretKey);
        if (!$signature || !hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }

        $payload = $request->json()->all();
        $event = strtolower((string)($payload['event'] ?? ''));
        $reference = (string)data_get($payload, 'data.reference', '');
        if (!$reference) {
            return response()->json(['error' => 'Missing transaction reference.'], 400);
        }

        $entry = WalletProviderTransaction::where('reference', $reference)
            ->where('provider', 'paystack')
            ->where('direction', 'deposit')
            ->first();
        if (!$entry) {
            return response()->json(['received' => true]);
        }

        if ($event === 'charge.success') {
            try {
                $this->verifyPaystackDeposit($settings, $entry);
            } catch (\Throwable $exception) {
                report($exception);
                return response()->json(['error' => 'Payment could not be verified.'], 503);
            }
        } elseif ($event === 'charge.failed') {
            app(WalletProviderService::class)->failDeposit($entry->reference, 'Paystack reported a failed card payment.');
        }

        return response()->json(['received' => true]);
    }

    public function paystackReturn(Request $request)
    {
        $reference = (string)$request->query('reference', '');
        $entry = WalletProviderTransaction::where('reference', $reference)
            ->where('provider', 'paystack')
            ->where('direction', 'deposit')
            ->first();

        if (!$entry) {
            return redirect()->route('login')->with('error', 'The payment reference could not be found.');
        }

        try {
            $settings = PaymentProviderSetting::forProvider('paystack')->settings;
            $this->verifyPaystackDeposit($settings, $entry);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('user.wallet', ['reference' => $reference]);
    }

    private function verifyPaystackDeposit(array $settings, WalletProviderTransaction $entry)
    {
        $verified = app(PaystackClient::class)->verify($settings, $entry->reference);
        $status = strtolower((string)data_get($verified, 'data.status'));
        $currency = strtoupper((string)data_get($verified, 'data.currency'));
        $minorAmount = data_get($verified, 'data.amount');

        if ($status === 'success') {
            if ($currency === 'USD'
                && is_numeric($minorAmount)
                && abs(((float)$minorAmount / 100) - (float)$entry->amount) <= 0.01) {
                app(WalletProviderService::class)->completeDeposit($entry->reference);
            } else {
                app(WalletProviderService::class)->failDeposit($entry->reference, 'The confirmed card payment amount or currency did not match this deposit.');
            }
        } elseif (in_array($status, ['failed', 'abandoned', 'cancelled', 'canceled'], true)) {
            app(WalletProviderService::class)->failDeposit($entry->reference, 'Paystack reported a failed card payment.');
        }
    }
}
