<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayzaClient
{
    private $baseUrl = 'https://payzaapi.co.ke/api/v1';

    private function request(array $settings)
    {
        if (empty($settings['public_key']) || empty($settings['secret_key'])) {
            throw new RuntimeException('Payza API keys are not configured.');
        }

        return Http::acceptJson()
            ->asJson()
            ->timeout(25)
            ->withHeaders([
                'X-Public-Key' => $settings['public_key'],
                'X-Secret-Key' => $settings['secret_key'],
            ]);
    }

    private function checkedJson($response)
    {
        if (!$response->successful()) {
            if ($response->serverError()) {
                throw new ProviderOutcomeUnknownException('Payza did not return a definite result; the payment will stay pending until status is confirmed.');
            }
            throw new RuntimeException('Payza request failed with HTTP ' . $response->status() . '. Check the currency plan, API keys, and payout IP allowlist.');
        }

        $data = $response->json();
        if (!is_array($data)) {
            throw new RuntimeException('Payza returned an invalid response.');
        }

        if (array_key_exists('success', $data) && !$data['success']) {
            $message = is_string($data['message'] ?? null) ? $data['message'] : 'Provider rejected the request.';
            throw new RuntimeException('Payza: ' . $message);
        }

        return $data;
    }

    public function initiatePayment(array $settings, array $payment)
    {
        $body = [
            'amount' => $payment['amount'],
            'currency' => $payment['currency'],
            'customer' => array_filter([
                'email' => $payment['email'],
                'name' => $payment['name'],
                'phone' => $payment['phone'],
            ], function ($value) {
                return $value !== null && $value !== '';
            }),
            'callback_url' => $payment['callback_url'],
            'redirect_url' => $payment['redirect_url'],
            'cancel_url' => $payment['cancel_url'],
            'reference' => $payment['reference'],
            'metadata' => $payment['metadata'],
        ];

        if (strtoupper((string)$payment['currency']) === 'KES') {
            $body['stk_push'] = false;
        }

        return $this->checkedJson($this->request($settings)->post($this->baseUrl . '/pay', $body));
    }

    public function verifyPayment(array $settings, $reference)
    {
        return $this->checkedJson($this->request($settings)->get($this->baseUrl . '/verify/' . rawurlencode($reference)));
    }

    public function payoutMethods(array $settings, $currency)
    {
        return $this->checkedJson($this->request($settings)->get($this->baseUrl . '/payout-methods', [
            'currency' => $currency,
        ]));
    }

    public function initiatePayout(array $settings, array $payout)
    {
        $body = [
            'amount' => $payout['amount'],
            'currency' => $payout['currency'],
            'method' => $payout['method'],
            'account_number' => $payout['account_number'],
            'account_name' => $payout['account_name'],
        ];
        foreach (['bank_code', 'bank_name'] as $field) {
            if (!empty($payout[$field])) {
                $body[$field] = $payout[$field];
            }
        }
        return $this->checkedJson($this->request($settings)->post($this->baseUrl . '/payout', $body));
    }

    public function payoutStatus(array $settings, $reference)
    {
        return $this->checkedJson($this->request($settings)->get($this->baseUrl . '/payouts', [
            'reference' => $reference,
        ]));
    }
}
