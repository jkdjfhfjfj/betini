<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentProviderSetting;
use App\Services\CurrencyService;
use App\Services\Payments\PayHeroClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletProviderController extends Controller
{
    private $currencies;

    public function __construct(CurrencyService $currencies)
    {
        $this->currencies = $currencies;
    }

    public function index()
    {
        $settings = [];
        $configurationWarning = null;
        foreach (['payhero', 'payza', 'paystack', 'makamesco'] as $provider) {
            try {
                $settings[$provider] = PaymentProviderSetting::forProvider($provider)->settings;
            } catch (\Throwable $exception) {
                $settings[$provider] = [];
                $configurationWarning = 'Saved credentials cannot be decrypted. Confirm the server APP_KEY is unchanged.';
            }
        }

        try {
            $routes = $this->currencies->providerRoutes();
        } catch (\Throwable $exception) {
            $routes = [];
            $configurationWarning = 'Saved routing settings cannot be read. Confirm the server APP_KEY is unchanged.';
        }

        $routeOptions = [];
        foreach (array_keys($this->currencies->supportedCurrencies()) as $currency) {
            $routeOptions[$currency] = [
                'deposit' => $currency === 'KES'
                    ? ['payhero', 'payza']
                    : ($currency === 'USD' ? ['paystack'] : ['payza']),
                'withdrawal' => $currency === 'KES' ? ['makamesco', 'payza'] : ['payza'],
            ];
        }

        return view('admin.wallet-providers', [
            'title' => 'Wallet Providers & Currency Routing',
            'settings' => $settings,
            'routes' => $routes,
            'routeOptions' => $routeOptions,
            'currencies' => $this->currencies->supportedCurrencies(),
            'configurationWarning' => $configurationWarning,
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'payhero.username' => 'nullable|string|max:190',
            'payhero.password' => 'nullable|string|max:500',
            'payhero.channel_id' => 'nullable|string|max:80',
            'payza.public_key' => 'nullable|string|max:500',
            'payza.secret_key' => 'nullable|string|max:500',
            'payza.webhook_secret' => 'nullable|string|max:500',
            'paystack.secret_key' => 'nullable|string|max:500',
            'makamesco.api_key' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ([
                    'payhero' => ['username', 'password', 'channel_id'],
                    'payza' => ['public_key', 'secret_key', 'webhook_secret'],
                    'paystack' => ['secret_key'],
                    'makamesco' => ['api_key'],
                ] as $provider => $fields) {
                    $record = PaymentProviderSetting::forProvider($provider);
                    $values = $record->settings;
                    foreach ($fields as $field) {
                        $value = $request->input($provider . '.' . $field);
                        if (is_string($value) && trim($value) !== '') {
                            $values[$field] = trim($value);
                        }
                    }
                    $record->settings = $values;
                    $record->save();
                }

                $routes = [];
                foreach (array_keys($this->currencies->supportedCurrencies()) as $currency) {
                    $depositOptions = $currency === 'KES'
                        ? ['payhero', 'payza']
                        : ($currency === 'USD' ? ['paystack'] : ['payza']);
                    $withdrawalOptions = $currency === 'KES' ? ['makamesco', 'payza'] : ['payza'];
                    $deposit = $request->input('routes.' . $currency . '.deposit');
                    $withdrawal = $request->input('routes.' . $currency . '.withdrawal');

                    if (!in_array($deposit, $depositOptions, true) || !in_array($withdrawal, $withdrawalOptions, true)) {
                        throw new RuntimeException('Choose a supported deposit and withdrawal route for ' . $currency . '.');
                    }

                    $routes[$currency] = ['deposit' => $deposit, 'withdrawal' => $withdrawal];
                }

                $routeRecord = PaymentProviderSetting::forProvider('routing');
                $routeRecord->settings = $routes;
                $routeRecord->save();
            });
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.wallet-providers')->with('success', 'Provider settings and currency routes saved.');
    }

    public function registerPayHeroChannel(Request $request)
    {
        $request->validate([
            'channel_type' => 'required|in:paybill,till,bank',
            'account_id' => 'required|string|max:80',
            'short_code' => 'required|string|max:80',
            'account_number' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:190',
        ]);

        try {
            $settingsRecord = PaymentProviderSetting::forProvider('payhero');
            $settings = $settingsRecord->settings;
            if (empty($settings['username']) || empty($settings['password'])) {
                throw new RuntimeException('Save the PayHero username and password before registering a channel.');
            }

            $result = app(PayHeroClient::class)->registerChannel($settings, $request->only([
                'channel_type',
                'account_id',
                'short_code',
                'account_number',
                'description',
            ]));

            $channelId = data_get($result, 'data.id')
                ?: data_get($result, 'id')
                ?: data_get($result, 'data.channel_id')
                ?: data_get($result, 'data.channel.id')
                ?: data_get($result, 'channel_id');
            if (!$channelId) {
                throw new RuntimeException('PayHero accepted the request but returned no channel ID. Check the PayHero dashboard and save the channel ID manually.');
            }

            $settings['channel_id'] = (string)$channelId;
            $settingsRecord->settings = $settings;
            $settingsRecord->save();
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.wallet-providers')->with('success', 'PayHero channel registered and its channel ID saved.');
    }
}
