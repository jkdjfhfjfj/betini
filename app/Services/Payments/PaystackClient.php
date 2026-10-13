<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackClient
{
    private $baseUrl = 'https://api.paystack.co';

    private function request(array $settings)
    {
        if (empty($settings['secret_key'])) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        return Http::acceptJson()
            ->asJson()
            ->timeout(25)
            ->withToken($settings['secret_key']);
    }

    private function checkedJson($response)
    {
        if (!$response->successful()) {
            if ($response->serverError()) {
                throw new ProviderOutcomeUnknownException('Paystack did not return a definite result; the payment will stay pending until status is confirmed.');
            }
            throw new RuntimeException('Paystack request failed with HTTP ' . $response->status() . '. Check the Paystack account and supported currencies.');
        }

        $data = $response->json();
        if (!is_array($data) || empty($data['status'])) {
            throw new RuntimeException('Paystack returned an unsuccessful or invalid response.');
        }

        return $data;
    }

    public function initialize(array $settings, array $payment)
    {
        return $this->checkedJson($this->request($settings)->post($this->baseUrl . '/transaction/initialize', [
            'email' => $payment['email'],
            'amount' => (int)round($payment['amount'] * 100),
            'currency' => 'USD',
            'reference' => $payment['reference'],
            'callback_url' => $payment['callback_url'],
            'metadata' => $payment['metadata'],
        ]));
    }

    public function verify(array $settings, $reference)
    {
        return $this->checkedJson($this->request($settings)->get($this->baseUrl . '/transaction/verify/' . rawurlencode($reference)));
    }
}
