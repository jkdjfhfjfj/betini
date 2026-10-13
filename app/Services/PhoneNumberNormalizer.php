<?php

namespace App\Services;

class PhoneNumberNormalizer
{
    public function dialDigits($dialCode)
    {
        return preg_replace('/\D+/', '', (string)$dialCode);
    }

    /**
     * Return a national number suitable for an input displayed beside a fixed
     * country-code prefix. Strip repeated international prefixes and the trunk 0.
     */
    public function localPart($number, $dialCode)
    {
        $digits = preg_replace('/\D+/', '', (string)$number);
        $prefix = $this->dialDigits($dialCode);
        if ($prefix === '') {
            return $digits;
        }

        while (strpos($digits, $prefix . $prefix) === 0) {
            $digits = substr($digits, strlen($prefix));
        }
        if (strpos($digits, $prefix) === 0) {
            $digits = substr($digits, strlen($prefix));
        }
        if (strpos($digits, '0') === 0) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Normalize a local or international number without adding a country code
     * more than once. Existing international numbers for another country remain
     * unchanged.
     */
    public function international($number, $dialCode)
    {
        $raw = trim((string)$number);
        if ($raw === '') {
            return '';
        }

        $isInternational = strpos($raw, '+') === 0 || strpos($raw, '00') === 0;
        $digits = preg_replace('/\D+/', '', $raw);
        if (strpos($digits, '00') === 0 && strpos($raw, '00') === 0) {
            $digits = substr($digits, 2);
        }
        $prefix = $this->dialDigits($dialCode);

        if ($prefix !== '') {
            while (strpos($digits, $prefix . $prefix) === 0) {
                $digits = substr($digits, strlen($prefix));
            }
            if ($isInternational && strpos($digits, $prefix) !== 0) {
                return '+' . $digits;
            }
            if (strpos($digits, $prefix) === 0) {
                $digits = substr($digits, strlen($prefix));
            }
        } elseif ($isInternational) {
            return '+' . $digits;
        }

        if (strpos($digits, '0') === 0) {
            $digits = substr($digits, 1);
        }

        return $prefix === '' ? $digits : '+' . $prefix . $digits;
    }

    public function kenyan($number)
    {
        $normalized = $this->international($number, '254');
        $digits = preg_replace('/\D+/', '', $normalized);
        if (!preg_match('/^254[17][0-9]{8}$/', $digits)) {
            throw new \InvalidArgumentException('Enter a valid Kenyan Safaricom number, such as 712345678 or 0712345678.');
        }

        return $digits;
    }
}
