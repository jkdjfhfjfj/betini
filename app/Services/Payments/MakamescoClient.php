<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MakamescoClient
{
    private $baseUrl = 'https://makamescopay.com';

    private function request(array $settings)
    {
        if (empty($settings['api_key'])) {
            throw new RuntimeException('Makamesco API key is not configured.');
        }

        return Http::acceptJson()
            ->asJson()
            ->timeout(25)
            ->withHeaders(['X-API-Key' => $settings['api_key']]);
    }

    private function checkedJson($response)
    {
        if (!$response->successful()) {
            if ($response->serverError()) {
                throw new ProviderOutcomeUnknownException('Makamesco did not return a definite result; the payout will stay pending until status is confirmed.');
            }
            throw new RuntimeException('Makamesco request failed with HTTP ' . $response->status() . '. Check the API key and B2C wallet.');
        }

        $data = $response->json();
        if (!is_array($data) || (array_key_exists('success', $data) && !$data['success'])) {
            throw new RuntimeException('Makamesco returned an unsuccessful or invalid response.');
        }

        return $data;
    }

    public function initiateB2c(array $settings, array $payout)
    {
        return $this->checkedJson($this->request($settings)->post($this->baseUrl . '/api/payments/b2c', [
            'phoneNumber' => $payout['phone_number'],
            'amount' => $payout['amount'],
            'remarks' => $payout['remarks'],
            'commandId' => 'BusinessPayment',
        ]));
    }

    public function b2cStatus(array $settings, $conversationId)
    {
        return $this->checkedJson($this->request($settings)->get(
            $this->baseUrl . '/api/payments/b2c/status/' . rawurlencode($conversationId)
        ));
    }
}
