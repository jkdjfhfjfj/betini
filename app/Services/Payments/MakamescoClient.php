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
            $reason = $this->safeProviderReason($response->json());
            if ($response->status() === 403) {
                throw new RuntimeException(
                    'Makamesco denied the B2C request (HTTP 403). ' .
                    ($reason ?: 'Confirm that B2C payouts are enabled for this API key and the provider account is active.')
                );
            }
            throw new RuntimeException(
                'Makamesco request failed with HTTP ' . $response->status() .
                ($reason ? ': ' . $reason : '. Check the provider account configuration.')
            );
        }

        $data = $response->json();
        if (!is_array($data) || (array_key_exists('success', $data) && !$data['success'])) {
            $reason = $this->safeProviderReason($data);
            throw new RuntimeException(
                'Makamesco returned an unsuccessful or invalid response.' .
                ($reason ? ' ' . $reason : '')
            );
        }

        return $data;
    }

    private function safeProviderReason($payload)
    {
        if (!is_array($payload)) {
            return null;
        }

        foreach (['message', 'error_description', 'error', 'detail'] as $field) {
            $reason = data_get($payload, $field);
            if (!is_string($reason) || trim($reason) === '') {
                continue;
            }

            $reason = trim(strip_tags($reason));
            $reason = preg_replace(
                '/(api[\s_-]?key|secret|token)(\s*[:=]\s*)[^\s,;]+/i',
                '$1$2[redacted]',
                $reason
            );
            $reason = preg_replace('/\bsk_(?:live|test)_[A-Za-z0-9_-]+\b/i', '[redacted API key]', $reason);

            return substr(preg_replace('/\s+/', ' ', $reason), 0, 220);
        }

        return null;
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
