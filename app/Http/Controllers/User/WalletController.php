<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\Upload;
use App\Models\Gateway;
use App\Models\PaymentProviderSetting;
use App\Models\WalletProviderTransaction;
use App\Services\CurrencyService;
use App\Services\PhoneNumberNormalizer;
use App\Services\Payments\MakamescoClient;
use App\Services\Payments\PayHeroClient;
use App\Services\Payments\PaystackClient;
use App\Services\Payments\PayzaClient;
use App\Services\Payments\ProviderOutcomeUnknownException;
use App\Services\WalletProviderService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;
use Stevebauman\Purify\Facades\Purify;

class WalletController extends Controller
{
    use Upload;

    private $currencies;

    public function __construct(CurrencyService $currencies)
    {
        $this->currencies = $currencies;
    }

    public function index(Request $request)
    {
        $user = $this->currencies->initializeUser(auth()->user());
        $currency = strtoupper($user->wallet_currency ?: 'USD');
        $supportedCurrencies = $this->currencies->supportedCurrencies();
        $targetCurrency = strtoupper(trim((string)$request->query('currency', $currency)));
        if (!array_key_exists($targetCurrency, $supportedCurrencies)) {
            $targetCurrency = $currency;
        }
        $phoneNormalizer = app(PhoneNumberNormalizer::class);
        $phoneCode = $phoneNormalizer->dialDigits($user->phone_code);
        if ($phoneCode === '' && $currency === 'KES') {
            $phoneCode = '254';
        }
        $profilePhone = $user->phone ?: $user->mobile;
        $oldPhoneInput = old('phone_number');
        $payheroPhoneInput = $phoneNormalizer->localPart(
            is_string($oldPhoneInput) ? $oldPhoneInput : $profilePhone,
            '254'
        );
        $payzaPhoneInput = $phoneNormalizer->international(
            is_string($oldPhoneInput) ? $oldPhoneInput : $profilePhone,
            $phoneCode
        );
        $routes = $this->currencies->providerRoutes();
        $transactions = WalletProviderTransaction::where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
        $manualDepositGateways = Gateway::manual()
            ->where('status', 1)
            ->whereRaw('UPPER(currency) = ?', [$currency])
            ->orderBy('sort_by')
            ->get();
        $manualDepositGateways->each(function ($gateway) {
            $note = trim((string)$gateway->note);
            $safeNote = Purify::clean($note);
            if ($note && filter_var($note, FILTER_VALIDATE_URL)
                && in_array(strtolower((string)parse_url($note, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                $safeNote = '<a href="' . e($note) . '" target="_blank" rel="noopener noreferrer">' . e($note) . '</a>';
            }
            $gateway->setAttribute('manual_instruction_html', $safeNote);
        });

        try {
            $rateAvailable = true;
            $this->currencies->rateFor($currency);
        } catch (\Throwable $exception) {
            $rateAvailable = false;
        }

        return view(template() . 'user.currency-wallet', [
            'user' => $user,
            'currencies' => $supportedCurrencies,
            'currency' => $currency,
            'targetCurrency' => $targetCurrency,
            'currencySymbol' => $this->currencies->symbol($currency),
            'phonePrefix' => $phoneCode !== '' ? '+' . $phoneCode : '',
            'phoneCode' => $phoneCode,
            'nationalPhone' => $phoneNormalizer->localPart($profilePhone, $phoneCode),
            'payheroPhoneInput' => $payheroPhoneInput,
            'payzaPhoneInput' => $payzaPhoneInput,
            'suggestedCurrency' => $this->currencies->currencyForCountry($user->country_code),
            'depositProvider' => $routes[$currency]['deposit'] ?? null,
            'withdrawalProvider' => $routes[$currency]['withdrawal'] ?? null,
            'manualDepositGateways' => $manualDepositGateways,
            'transactions' => $transactions,
            'rateAvailable' => $rateAvailable,
        ]);
    }

    public function updateCurrency(Request $request)
    {
        $request->merge([
            'wallet_currency' => strtoupper(trim((string)$request->input('wallet_currency', ''))),
        ]);

        $supportedCurrencies = array_keys($this->currencies->supportedCurrencies());
        $validator = Validator::make($request->all(), [
            'wallet_currency' => ['required', 'string', 'size:3', 'in:' . implode(',', $supportedCurrencies)],
            'quote_token' => 'nullable|string|max:10000',
        ], [
            'wallet_currency.required' => 'Choose a wallet currency.',
            'wallet_currency.size' => 'Choose a valid three-letter currency code.',
            'wallet_currency.in' => 'That wallet currency is not supported.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('user.wallet')
                ->withErrors($validator)
                ->withInput();
        }

        $currentCurrency = strtoupper(auth()->user()->wallet_currency ?: 'USD');
        if ($currentCurrency === $request->wallet_currency) {
            return redirect()->route('user.wallet')->with('success', 'Your wallet already uses ' . $currentCurrency . '.');
        }
        if (!$request->filled('quote_token')) {
            return redirect()->route('user.wallet')
                ->withErrors(['wallet_currency' => 'Preview and confirm the conversion before changing your wallet currency.']);
        }

        try {
            $this->currencies->switchWalletCurrency(auth()->user(), $request->wallet_currency, $request->input('quote_token'));
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()->route('user.wallet')
                ->withErrors($exception->errors())
                ->withInput();
        } catch (\Throwable $exception) {
            return redirect()->route('user.wallet')
                ->withErrors(['wallet_currency' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('user.wallet')->with('success', 'Wallet currency updated. The conversion fee and final balance were included in your confirmed quote.');
    }

    public function currencyQuote(Request $request)
    {
        $supportedCurrencies = array_keys($this->currencies->supportedCurrencies());
        $validator = Validator::make([
            'wallet_currency' => strtoupper(trim((string)$request->input('wallet_currency', ''))),
        ], [
            'wallet_currency' => ['required', 'string', 'size:3', 'in:' . implode(',', $supportedCurrencies)],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $quote = $this->currencies->walletCurrencyQuote(
                $this->currencies->initializeUser(auth()->user()),
                $validator->validated()['wallet_currency']
            );
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json(['errors' => $exception->errors()], 422);
        } catch (\Throwable $exception) {
            return response()->json(['errors' => ['wallet_currency' => [$exception->getMessage()]]], 422);
        }

        return response()->json([
            'quote' => $quote,
            'from_symbol' => $this->currencies->symbol($quote['from_currency']),
            'to_symbol' => $this->currencies->symbol($quote['to_currency']),
        ]);
    }

    public function deposit(Request $request)
    {
        $user = $this->currencies->initializeUser(auth()->user());
        $currency = strtoupper($user->wallet_currency ?: 'USD');
        $routeMap = $this->currencies->providerRoutes();
        $provider = $routeMap[$currency]['deposit'] ?? null;

        $rules = [
            'amount' => $provider === 'payhero'
                ? 'required|integer|min:1|max:10000000'
                : 'required|numeric|gt:0|max:10000000',
            'phone_number' => 'nullable|string|max:32',
        ];
        if ($currency === 'KES') {
            $rules['phone_number'] = 'required|string|max:32';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!$provider) {
            return back()->with('error', 'No deposit provider is configured for ' . $currency . '.');
        }

        try {
            $phoneNormalizer = app(PhoneNumberNormalizer::class);
            if ($provider === 'payhero') {
                $request->merge([
                    'phone_number' => $phoneNormalizer->kenyan($request->input('phone_number') ?: $user->mobile),
                ]);
            } elseif ($provider === 'payza') {
                $phone = $request->input('phone_number') ?: $user->mobile;
                if (trim((string)$phone) !== '') {
                    $request->merge([
                        'phone_number' => $phoneNormalizer->international($phone, $user->phone_code),
                    ]);
                }
            }
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['phone_number' => $exception->getMessage()])->withInput();
        }

        try {
            $rate = $this->optionalRateSnapshot($currency);
            $this->assertProviderConfigured($provider);
            $minimum = $provider === 'paystack' ? 0.01 : $this->payzaMinimum($currency);
            if ((float)$request->amount < $minimum) {
                return back()->withErrors(['amount' => 'The minimum deposit for ' . $currency . ' is ' . $minimum . '.'])->withInput();
            }

            $reference = 'WDP' . strtoupper(Str::random(18));
            $entry = WalletProviderTransaction::create([
                'user_id' => $user->id,
                'reference' => $reference,
                'provider' => $provider,
                'direction' => 'deposit',
                'currency_code' => $currency,
                'amount' => round((float)$request->amount, 2),
                'fee' => 0,
                'net_amount' => round((float)$request->amount, 2),
                'usd_rate' => $rate,
                'status' => 'pending',
                'metadata' => [],
            ]);

            try {
                $response = $this->startDeposit($provider, $user, $entry, $request);
            } catch (ProviderOutcomeUnknownException $exception) {
                $entry->status = 'processing';
                $entry->failure_reason = 'Provider response was inconclusive; waiting for an authenticated status confirmation.';
                $entry->save();
                return redirect()->route('user.wallet', ['reference' => $reference])
                    ->with('success', 'The provider response is taking longer. The deposit remains pending and will be credited only after confirmation.');
            } catch (ConnectionException $exception) {
                $entry->status = 'processing';
                $entry->failure_reason = 'Provider connection timed out; waiting for an authenticated status confirmation.';
                $entry->save();
                return redirect()->route('user.wallet', ['reference' => $reference])
                    ->with('success', 'The provider response is taking longer. The deposit remains pending and will be credited only after confirmation.');
            } catch (\Throwable $exception) {
                app(WalletProviderService::class)->failDeposit($reference, $exception->getMessage());
                throw $exception;
            }

            $providerReference = data_get($response, 'data.reference')
                ?: data_get($response, 'reference')
                ?: data_get($response, 'data.CheckoutRequestID')
                ?: data_get($response, 'CheckoutRequestID');
            $entry->provider_reference = $providerReference ?: $reference;
            $entry->metadata = $this->safeProviderMetadata($response);
            $entry->save();

            $paymentUrl = data_get($response, 'data.payment_url') ?: data_get($response, 'payment_url')
                ?: data_get($response, 'data.authorization_url') ?: data_get($response, 'authorization_url');
            if ($paymentUrl) {
                $allowedDomains = $provider === 'paystack' ? ['paystack.com'] : ['payzaapi.co.ke'];
                if (!$this->isTrustedProviderUrl($paymentUrl, $allowedDomains)) {
                    app(WalletProviderService::class)->failDeposit($reference, 'Provider returned an unsafe checkout URL.');
                    return back()->with('error', 'The payment provider returned an invalid checkout link.');
                }

                return redirect()->away($paymentUrl);
            }

            return redirect()->route('user.wallet', ['reference' => $reference])
                ->with('success', 'Payment prompt sent. Your wallet will be credited automatically after the provider confirms success.');
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function manualDeposit(Request $request)
    {
        $request->validate([
            'gateway_id' => 'required|integer',
            'amount' => 'required|numeric|gt:0|max:10000000',
            'payment_fields' => 'sometimes|array',
        ]);

        $user = $this->currencies->initializeUser(auth()->user());
        $currency = strtoupper($user->wallet_currency ?: 'USD');
        $gateway = Gateway::manual()
            ->where('status', 1)
            ->findOrFail($request->input('gateway_id'));

        if (strtoupper((string)$gateway->currency) !== $currency) {
            return back()->withInput()->with('error', 'That manual deposit method is not available for your current wallet currency.');
        }

        $amount = round((float)$request->input('amount'), 2);
        if ($amount <= 0) {
            return back()->withInput()->withErrors(['amount' => 'Enter an amount of at least 0.01 ' . $currency . '.']);
        }
        $minimum = (float)$gateway->min_amount;
        $maximum = (float)$gateway->max_amount;
        if (($minimum > 0 && $amount < $minimum) || ($maximum > 0 && $amount > $maximum)) {
            $limits = $maximum > 0
                ? 'between ' . $minimum . ' and ' . $maximum
                : 'at least ' . $minimum;
            return back()->withInput()->withErrors([
                'amount' => 'The deposit amount must be ' . $limits . ' ' . $currency . '.',
            ]);
        }

        $parameters = (array)($gateway->parameters ?: []);
        $rules = [];
        $fieldIndex = 0;
        foreach ($parameters as $field) {
            $type = (string)($field->type ?? '');
            if (in_array($type, ['text', 'textarea', 'file'], true)) {
                $fieldRules = [($field->validation ?? '') === 'required' ? 'required' : 'nullable'];
                if ($type === 'file') {
                    $fieldRules = array_merge($fieldRules, ['image', 'mimes:jpeg,jpg,png', 'max:2048']);
                } else {
                    $fieldRules = array_merge($fieldRules, ['string', $type === 'textarea' ? 'max:300' : 'max:191']);
                }
                $rules['payment_fields.' . $fieldIndex] = $fieldRules;
            }
            $fieldIndex++;
        }
        if ($rules) {
            Validator::make($request->all(), $rules)->validate();
        }

        $submittedFields = [];
        $fieldIndex = 0;
        $uploadPath = config('location.deposit.path') . date('Y') . '/' . date('m') . '/' . date('d');
        foreach ($parameters as $key => $field) {
            $type = (string)($field->type ?? '');
            if (in_array($type, ['text', 'textarea', 'file'], true)) {
                $value = null;
                if ($type === 'file') {
                    $file = $request->file('payment_fields.' . $fieldIndex);
                    if ($file) {
                        $value = $this->uploadImage($file, $uploadPath);
                    }
                } else {
                    $value = trim((string)$request->input('payment_fields.' . $fieldIndex, ''));
                }
                if ($value !== null && $value !== '') {
                    $submittedFields[] = [
                        'label' => (string)($field->field_level ?? $key),
                        'type' => $type,
                        'value' => $value,
                    ];
                }
            }
            $fieldIndex++;
        }

        $fixedCharge = (float)$gateway->fixed_charge;
        $percentageCharge = (float)$gateway->percentage_charge;
        if ($fixedCharge < 0 || $percentageCharge < 0) {
            return back()->withInput()->with('error', 'This manual deposit method has invalid fee settings. Please contact support.');
        }
        $fee = round($fixedCharge + ($amount * $percentageCharge / 100), 2);
        $expectedPayment = round($amount + $fee, 2);
        $rate = $this->optionalRateSnapshot($currency);
        $reference = 'WMD' . strtoupper(Str::random(18));

        try {
            $entry = DB::transaction(function () use ($user, $currency, $gateway, $amount, $fee, $expectedPayment, $rate, $reference, $submittedFields) {
                $lockedUser = \App\Models\User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                if (strtoupper($lockedUser->wallet_currency ?: 'USD') !== $currency) {
                    throw new RuntimeException('Your wallet currency changed. Refresh and submit again.');
                }

                return WalletProviderTransaction::create([
                    'user_id' => $lockedUser->id,
                    'reference' => $reference,
                    'provider' => 'manual',
                    'direction' => 'deposit',
                    'currency_code' => $currency,
                    'amount' => $amount,
                    'fee' => $fee,
                    'net_amount' => $amount,
                    'usd_rate' => $rate,
                    'status' => 'pending',
                    'metadata' => [
                        'gateway_id' => $gateway->id,
                        'gateway_name' => $gateway->name,
                        'expected_payment' => $expectedPayment,
                        'submitted_fields' => $submittedFields,
                    ],
                ]);
            });
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('user.wallet', ['reference' => $entry->reference])
            ->with('success', 'Manual deposit submitted. Your wallet will be credited after an administrator verifies the payment.');
    }

    public function withdraw(Request $request)
    {
        $user = $this->currencies->initializeUser(auth()->user());
        $currency = strtoupper($user->wallet_currency ?: 'USD');
        $routes = $this->currencies->providerRoutes();
        $provider = $routes[$currency]['withdrawal'] ?? null;

        $request->validate([
            'amount' => 'required|numeric|gt:0|max:10000000',
            'account_name' => 'required|string|max:120',
            'account_number' => 'required|string|max:80',
            'method' => 'nullable|string|max:40',
            'bank_code' => 'nullable|string|max:40',
            'bank_name' => 'nullable|string|max:120',
        ]);

        if (!$provider) {
            return back()->with('error', 'No withdrawal provider is configured for ' . $currency . '.');
        }

        if ($provider === 'makamesco') {
            try {
                $request->merge([
                    'account_number' => $this->normalizeKenyanPhone($request->input('account_number')),
                ]);
            } catch (RuntimeException $exception) {
                return back()->withErrors(['account_number' => $exception->getMessage()])->withInput();
            }
        }

        try {
            $rate = $this->optionalRateSnapshot($currency);
            $settings = $this->providerSettings($provider);
            $this->assertProviderConfigured($provider, $settings);
            if ($provider === 'payza') {
                $request->validate(['method' => 'required|string|max:40']);
                $availableMethods = app(PayzaClient::class)->payoutMethods($settings, $currency);
                $methodList = data_get($availableMethods, 'data.methods')
                    ?: data_get($availableMethods, 'methods')
                    ?: data_get($availableMethods, 'data')
                    ?: [];
                $allowedMethods = collect($this->normalizeMethods($methodList))->pluck('code');
                if (!$allowedMethods->contains((string)$request->method)) {
                    return back()->withErrors(['method' => 'Choose a payout method currently supported for ' . $currency . '.'])->withInput();
                }
                $selectedMethod = collect($this->normalizeMethods($methodList))->firstWhere('code', (string)$request->method);
                $methodLabel = strtolower((string)$request->method . ' ' . data_get($selectedMethod, 'name', ''));
                if (preg_match('/mobile|m-?pesa|mtn|airtel|momo|wallet|phone/', $methodLabel)) {
                    $mobileAccount = app(PhoneNumberNormalizer::class)->international($request->account_number, $user->phone_code);
                    if (!preg_match('/^\+[1-9][0-9]{6,14}$/', $mobileAccount)) {
                        return back()->withErrors([
                            'account_number' => 'Enter a valid international mobile number, or add your country calling code to your profile before using this payout method.',
                        ])->withInput();
                    }
                    $request->merge(['account_number' => $mobileAccount]);
                }
            }
            $reference = 'WWD' . strtoupper(Str::random(18));
            $amount = round((float)$request->amount, 2);

            $entry = DB::transaction(function () use ($user, $provider, $currency, $reference, $amount, $rate, $request) {
                $lockedUser = \App\Models\User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                if (strtoupper($lockedUser->wallet_currency ?: 'USD') !== $currency) {
                    throw new RuntimeException('Your wallet currency changed. Refresh and submit again.');
                }
                if ((float)$lockedUser->balance < $amount) {
                    throw new RuntimeException('Insufficient available balance.');
                }

                $lockedUser->balance = round((float)$lockedUser->balance - $amount, 2);
                $lockedUser->save();

                $transaction = WalletProviderTransaction::create([
                    'user_id' => $lockedUser->id,
                    'reference' => $reference,
                    'provider' => $provider,
                    'direction' => 'withdrawal',
                    'currency_code' => $currency,
                    'amount' => $amount,
                    'fee' => 0,
                    'net_amount' => $amount,
                    'usd_rate' => $rate,
                    'status' => 'pending',
                    'metadata' => ['method' => $request->method],
                ]);

                app(\App\Services\BasicService::class)->makeTransaction(
                    $lockedUser,
                    $amount,
                    0,
                    '-',
                    $reference,
                    'Withdrawal request via ' . ucfirst($provider)
                );

                return $transaction;
            });

            try {
                $response = $this->startWithdrawal($provider, $settings, $entry, $request);
            } catch (ProviderOutcomeUnknownException $exception) {
                $entry->status = 'processing';
                $entry->failure_reason = 'Provider response was inconclusive; waiting for an authenticated status confirmation.';
                $entry->save();
                return redirect()->route('user.wallet', ['reference' => $reference])
                    ->with('success', 'The payout response is taking longer. The amount stays reserved until the provider confirms success or failure.');
            } catch (ConnectionException $exception) {
                $entry->status = 'processing';
                $entry->failure_reason = 'Provider connection timed out; waiting for an authenticated status confirmation.';
                $entry->save();
                return redirect()->route('user.wallet', ['reference' => $reference])
                    ->with('success', 'The payout response is taking longer. The amount stays reserved until the provider confirms success or failure.');
            } catch (\Throwable $exception) {
                app(WalletProviderService::class)->failWithdrawal($reference, $exception->getMessage());
                throw $exception;
            }

            $providerReference = data_get($response, 'data.conversationId')
                ?: data_get($response, 'conversationId')
                ?: data_get($response, 'data.reference')
                ?: data_get($response, 'reference')
                ?: $reference;
            $entry->provider_reference = (string)$providerReference;
            $entry->metadata = array_merge($entry->metadata ?: [], [
                'provider_status' => data_get($response, 'data.status') ?: data_get($response, 'status'),
            ]);
            $entry->status = 'processing';
            $entry->save();

            return redirect()->route('user.wallet', ['reference' => $reference])
                ->with('success', 'Withdrawal request submitted. It will remain pending until the provider confirms delivery.');
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function manualWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0|max:10000000',
            'payout_method' => 'required|string|max:100',
            'account_name' => 'required|string|max:120',
            'account_number' => 'required|string|max:120',
            'bank_name' => 'nullable|string|max:120',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        $user = $this->currencies->initializeUser(auth()->user());
        $currency = strtoupper($user->wallet_currency ?: 'USD');
        $amount = round((float)$request->input('amount'), 2);
        if ($amount <= 0) {
            return back()->withInput()->withErrors(['amount' => 'Enter an amount of at least 0.01 ' . $currency . '.']);
        }
        $rate = $this->optionalRateSnapshot($currency);
        $reference = 'WMW' . strtoupper(Str::random(18));

        try {
            $entry = DB::transaction(function () use ($user, $currency, $amount, $rate, $reference, $request) {
                $lockedUser = \App\Models\User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                if (strtoupper($lockedUser->wallet_currency ?: 'USD') !== $currency) {
                    throw new RuntimeException('Your wallet currency changed. Refresh and submit again.');
                }
                if ((float)$lockedUser->balance < $amount) {
                    throw new RuntimeException('Insufficient available balance.');
                }

                $lockedUser->balance = round((float)$lockedUser->balance - $amount, 2);
                $lockedUser->save();

                $transaction = WalletProviderTransaction::create([
                    'user_id' => $lockedUser->id,
                    'reference' => $reference,
                    'provider' => 'manual',
                    'direction' => 'withdrawal',
                    'currency_code' => $currency,
                    'amount' => $amount,
                    'fee' => 0,
                    'net_amount' => $amount,
                    'usd_rate' => $rate,
                    'status' => 'pending',
                    'metadata' => [
                        'payout_method' => trim((string)$request->input('payout_method')),
                        'account_name' => trim((string)$request->input('account_name')),
                        'account_number' => trim((string)$request->input('account_number')),
                        'bank_name' => trim((string)$request->input('bank_name', '')),
                        'payment_notes' => trim((string)$request->input('payment_notes', '')),
                    ],
                ]);

                app(\App\Services\BasicService::class)->makeTransaction(
                    $lockedUser,
                    $amount,
                    0,
                    '-',
                    $reference,
                    'Manual withdrawal request for administrator review'
                );

                return $transaction;
            });
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('user.wallet', ['reference' => $entry->reference])
            ->with('success', 'Manual withdrawal submitted. The amount is reserved until an administrator approves or rejects the request.');
    }

    public function payoutMethods()
    {
        $currency = strtoupper(auth()->user()->wallet_currency ?: 'USD');
        $routes = $this->currencies->providerRoutes();
        $provider = $routes[$currency]['withdrawal'] ?? null;
        if ($provider !== 'payza') {
            return response()->json(['provider' => $provider, 'methods' => []]);
        }

        try {
            $settings = $this->providerSettings('payza');
            $response = app(PayzaClient::class)->payoutMethods($settings, $currency);
            $methods = data_get($response, 'data.methods') ?: data_get($response, 'methods') ?: data_get($response, 'data') ?: [];
            return response()->json(['provider' => 'payza', 'methods' => $this->normalizeMethods($methods)]);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function status($reference)
    {
        $entry = WalletProviderTransaction::where('user_id', auth()->id())
            ->where('reference', $reference)
            ->firstOrFail();

        if ($entry->direction === 'deposit' && in_array($entry->status, ['pending', 'processing'], true)) {
            try {
                if ($entry->provider === 'payhero') {
                    $this->syncPayHeroDeposit($entry);
                } elseif ($entry->provider === 'payza') {
                    $this->syncPayzaDeposit($entry);
                } elseif ($entry->provider === 'paystack') {
                    $this->syncPaystackDeposit($entry);
                }
                $entry->refresh();
            } catch (\Throwable $exception) {
                // A transient provider status error does not change the pending payment.
            }
        }

        return response()->json([
            'status' => $entry->status,
            'direction' => $entry->direction,
            'currency' => $entry->currency_code,
            'message' => $entry->status === 'succeeded'
                ? ($entry->direction === 'deposit' ? 'Deposit successful; your wallet is credited.' : 'Withdrawal completed.')
                : ($entry->status === 'failed' ? 'Transaction failed: ' . ($entry->failure_reason ?: 'Please try again.') : 'Transaction is still pending.'),
        ]);
    }

    private function startDeposit($provider, $user, $entry, Request $request)
    {
        if ($provider === 'payhero') {
            $settings = $this->providerSettings('payhero');
            if (empty($settings['channel_id'])) {
                throw new RuntimeException('PayHero channel ID is not configured. An administrator must register or select a payment channel first.');
            }

            return app(PayHeroClient::class)->initiateStk($settings, [
                'amount' => (float)$entry->amount,
                'phone_number' => $this->normalizeKenyanPhone($request->phone_number ?: $user->mobile),
                'reference' => $entry->reference,
                'customer_name' => $user->fullname,
                'callback_url' => $this->httpsRoute('wallet.payhero.webhook'),
            ]);
        }

        if ($provider === 'payza') {
            $settings = $this->providerSettings('payza');
            $phone = $request->phone_number ?: $user->mobile;
            return app(PayzaClient::class)->initiatePayment($settings, [
                'amount' => (float)$entry->amount,
                'currency' => $entry->currency_code,
                'email' => $user->email,
                'name' => $user->fullname,
                'phone' => trim((string)$phone) !== ''
                    ? app(PhoneNumberNormalizer::class)->international($phone, $user->phone_code)
                    : null,
                'callback_url' => $this->httpsRoute('wallet.payza.webhook'),
                'redirect_url' => route('wallet.payza.return', ['reference' => $entry->reference], true),
                'cancel_url' => route('user.wallet', ['reference' => $entry->reference, 'cancelled' => 1], true),
                'reference' => $entry->reference,
                'metadata' => ['wallet_reference' => $entry->reference, 'user_id' => $user->id],
            ]);
        }

        if ($provider === 'paystack') {
            return app(PaystackClient::class)->initialize($this->providerSettings('paystack'), [
                'email' => $user->email,
                'amount' => (float)$entry->amount,
                'reference' => $entry->reference,
                'callback_url' => route('wallet.paystack.return', [], true),
                'metadata' => ['wallet_reference' => $entry->reference, 'user_id' => $user->id],
            ]);
        }

        throw new RuntimeException('Unsupported deposit provider.');
    }

    private function startWithdrawal($provider, array $settings, WalletProviderTransaction $entry, Request $request)
    {
        if ($provider === 'makamesco') {
            return app(MakamescoClient::class)->initiateB2c($settings, [
                'phone_number' => $this->normalizeKenyanPhone($request->account_number),
                'amount' => (float)$entry->net_amount,
                'remarks' => 'Wallet withdrawal ' . $entry->reference,
            ]);
        }

        if ($provider === 'payza') {
            return app(PayzaClient::class)->initiatePayout($settings, [
                'amount' => (float)$entry->net_amount,
                'currency' => $entry->currency_code,
                'method' => $request->method,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_code' => $request->bank_code,
                'bank_name' => $request->bank_name,
                'reference' => $entry->reference,
            ]);
        }

        throw new RuntimeException('Unsupported withdrawal provider.');
    }

    private function providerSettings($provider)
    {
        return PaymentProviderSetting::forProvider($provider)->settings;
    }

    private function assertProviderConfigured($provider, ?array $settings = null)
    {
        $settings = $settings ?: $this->providerSettings($provider);
        $required = [
            'payhero' => ['username', 'password', 'channel_id'],
            'payza' => ['public_key', 'secret_key', 'webhook_secret'],
            'paystack' => ['secret_key'],
            'makamesco' => ['api_key'],
        ];

        foreach ($required[$provider] ?? [] as $field) {
            if (empty($settings[$field])) {
                throw new RuntimeException(ucfirst($provider) . ' is not fully configured. Ask an administrator to finish the provider setup.');
            }
        }
    }

    private function payzaMinimum($currency)
    {
        // Provider-specific minimums can vary by currency and account; let Payza return its current validation.
        return 0.01;
    }

    private function optionalRateSnapshot($currency)
    {
        try {
            return $this->currencies->rateSnapshot($currency);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function syncPayHeroDeposit(WalletProviderTransaction $entry)
    {
        app(\App\Services\WalletDepositReconciler::class)->syncPayHero($entry);
    }

    private function syncPayzaDeposit(WalletProviderTransaction $entry)
    {
        $verified = app(PayzaClient::class)->verifyPayment($this->providerSettings('payza'), $entry->reference);
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
            if ($currency !== strtoupper($entry->currency_code)
                || !is_numeric($amount)
                || abs((float)$amount - (float)$entry->amount) > 0.02) {
                app(WalletProviderService::class)->failDeposit($entry->reference, 'The confirmed payment amount or currency did not match this deposit.');
                return;
            }
            app(WalletProviderService::class)->completeDeposit($entry->reference);
        } elseif (in_array($status, ['failed', 'cancelled', 'canceled', 'rejected', 'expired'], true)) {
            app(WalletProviderService::class)->failDeposit($entry->reference, 'Payza reported a failed or cancelled payment.');
        }
    }

    private function syncPaystackDeposit(WalletProviderTransaction $entry)
    {
        $verified = app(PaystackClient::class)->verify($this->providerSettings('paystack'), $entry->reference);
        $status = strtolower((string)data_get($verified, 'data.status'));
        $currency = strtoupper((string)data_get($verified, 'data.currency'));
        $minorAmount = data_get($verified, 'data.amount');
        if ($status === 'success') {
            if ($currency !== 'USD'
                || !is_numeric($minorAmount)
                || abs(((float)$minorAmount / 100) - (float)$entry->amount) > 0.01) {
                app(WalletProviderService::class)->failDeposit($entry->reference, 'The confirmed card payment amount or currency did not match this deposit.');
                return;
            }
            app(WalletProviderService::class)->completeDeposit($entry->reference);
        } elseif (in_array($status, ['failed', 'abandoned', 'cancelled', 'canceled'], true)) {
            app(WalletProviderService::class)->failDeposit($entry->reference, 'Paystack reported a failed card payment.');
        }
    }

    private function normalizeMethods($methods)
    {
        if (!is_array($methods)) {
            return [];
        }

        $normalized = [];
        foreach ($methods as $key => $method) {
            if (is_string($method)) {
                $normalized[] = ['code' => $method, 'name' => ucfirst(str_replace('_', ' ', $method))];
                continue;
            }
            if (!is_array($method)) {
                continue;
            }
            $code = $method['code'] ?? $method['method'] ?? $method['id'] ?? (is_string($key) ? $key : null);
            if ($code) {
                $normalized[] = [
                    'code' => (string)$code,
                    'name' => (string)($method['name'] ?? $method['label'] ?? ucfirst(str_replace('_', ' ', $code))),
                    'fee' => $method['fee'] ?? $method['fees'] ?? null,
                ];
            }
        }

        return $normalized;
    }

    private function safeProviderMetadata(array $response)
    {
        return [
            'provider_status' => data_get($response, 'data.status') ?: data_get($response, 'status'),
            'checkout_request_id' => data_get($response, 'data.CheckoutRequestID') ?: data_get($response, 'CheckoutRequestID'),
        ];
    }

    private function isTrustedProviderUrl($url, array $domains)
    {
        if (!is_string($url) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return false;
        }

        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        foreach ($domains as $domain) {
            if ($host === $domain || Str::endsWith($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeKenyanPhone($phone)
    {
        return app(PhoneNumberNormalizer::class)->kenyan($phone);
    }

    private function httpsRoute($routeName)
    {
        $url = route($routeName, [], true);
        if (!Str::startsWith(strtolower($url), 'https://')) {
            throw new RuntimeException('Provider callbacks require a public HTTPS application URL. Configure the deployed APP_URL before enabling live payments.');
        }

        return $url;
    }
}
