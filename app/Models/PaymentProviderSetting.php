<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class PaymentProviderSetting extends Model
{
    protected $table = 'payment_provider_settings';

    protected $fillable = ['provider', 'settings'];

    protected $hidden = ['settings'];

    public function getSettingsAttribute($value)
    {
        if (!$value) {
            return [];
        }

        try {
            $settings = json_decode(Crypt::decryptString($value), true);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Payment settings cannot be decrypted. Check that the application encryption key has not changed.');
        }

        return is_array($settings) ? $settings : [];
    }

    public function setSettingsAttribute($value)
    {
        if (!getenv('APP_KEY')) {
            throw new RuntimeException('Set a unique APP_KEY in the server environment before saving payment credentials.');
        }

        $this->attributes['settings'] = Crypt::encryptString(json_encode($value ?: []));
    }

    public static function forProvider($provider)
    {
        return static::firstOrNew(['provider' => $provider]);
    }
}
