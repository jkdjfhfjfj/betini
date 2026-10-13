<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayHeroClient
{
    private $baseUrl = 'https://backend.payhero.co.ke/api/v2';

    private function request($settings)
    {
        if (empty($settings['username']) || empty($settings['password'])) {
            throw new RuntimeException('PayHero username and password are not configured.');
        }

        return Http::acceptJson()
            ->asJson()
            ->timeout(20)
            ->withBasicAuth($settings['username'], $settings['password']);
    }

    private function checkedJson($response)
    {
        if (!$response->successful()) {
            if ($response->serverError()) {
                throw new ProviderOutcomeUnknownException('PayHero did not return a definite result; the payment will stay pending until status is confirmed.');
            }
            throw new RuntimeException('PayHero request failed with HTTP ' . $response->status() . '. Check the PayHero settings and account.');
        }

        $data = $response->json();
        if (!is_array($data)) {
            throw new RuntimeException('PayHero returned an invalid response.');
        }
        if (array_key_exists('success', $data) && !$data['success']) {
            throw new RuntimeException('PayHero rejected the request. Check the provider account and channel configuration.');
        }

        return $data;
    }

    public function registerChannel(array $settings, array $channel)
    {
        return $this->checkedJson($this->request($settings)->post($this->baseUrl . '/payment_channels', [
            'channel_type' => $channel['channel_type'],
            'account_id' => $channel['account_id'],
            'short_code' => $channel['short_code'],
            'account_number' => $channel['account_number'] ?? '',
            'description' => $channel['description'] ?? '',
        ]));
    }

    public function initiateStk(array $settings, array $payment)
    {
        return $this->checkedJson($this->request($settings)->post($this->baseUrl . '/payments', [
            'amount' => $payment['amount'],
            'phone_number' => $payment['phone_number'],
            'channel_id' => $settings['channel_id'],
            'provider' => 'm-pesa',
            'external_reference' => $payment['reference'],
            'customer_name' => $payment['customer_name'],
            'callback_url' => $payment['callback_url'],
        ]));
    }

    public function transactionStatus(array $settings, $reference)
    {
        return $this->checkedJson($this->request($settings)->get($this->baseUrl . '/transaction-status', [
            'reference' => $reference,
        ]));
    }
}
