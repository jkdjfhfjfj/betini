<?php

namespace Tests\Feature;

use App\Http\Controllers\WalletWebhookController;
use App\Http\Controllers\Admin\WalletManualController;
use App\Http\Controllers\Admin\WalletProviderController;
use App\Http\Controllers\User\WalletController;
use App\Models\PaymentProviderSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletProviderTransaction;
use App\Services\CurrencyService;
use App\Services\Payments\PayzaClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WalletProviderSafetyTest extends TestCase
{
    private $testAppKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testAppKey = 'base64:' . base64_encode(str_repeat('T', 32));
        putenv('APP_KEY=' . $this->testAppKey);
        $_ENV['APP_KEY'] = $this->testAppKey;
        $_SERVER['APP_KEY'] = $this->testAppKey;

        config([
            'app.key' => $this->testAppKey,
            'app.cipher' => 'AES-256-CBC',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        $this->createIsolatedSchema();
    }

    public function test_payhero_credits_only_a_provider_confirmed_deposit_and_duplicate_callbacks_do_not_credit_twice()
    {
        $user = $this->createUser(5, 'KES');
        $entry = $this->createProviderTransaction($user, [
            'reference' => 'payhero-deposit-1',
            'provider' => 'payhero',
            'currency_code' => 'KES',
            'amount' => 12.50,
            'net_amount' => 12.50,
        ]);
        // The deposit initiation flow stores the provider reference, falling
        // back to the merchant reference sent as PayHero's external_reference.
        $entry->provider_reference = $entry->reference;
        $entry->save();
        $this->saveProviderSettings('payhero', [
            'username' => 'test-user',
            'password' => 'test-password',
            'channel_id' => 'test-channel',
        ]);

        Http::fake([
            'https://backend.payhero.co.ke/api/v2/transaction-status*' => Http::response([
                'status' => 'SUCCESS',
                'amount' => 12.50,
                'currency' => 'KES',
            ], 200),
        ]);

        $controller = app(WalletWebhookController::class);
        $firstResponse = $controller->payhero($this->jsonRequest([
            'response' => ['ExternalReference' => $entry->reference],
        ]));
        $secondResponse = $controller->payhero($this->jsonRequest([
            'response' => ['ExternalReference' => $entry->reference],
        ]));

        $this->assertSame(200, $firstResponse->getStatusCode());
        $this->assertSame(200, $secondResponse->getStatusCode());
        $this->assertEquals(17.5, (float)$user->fresh()->balance);
        $this->assertSame('succeeded', $entry->fresh()->status);
        $this->assertSame(1, Transaction::where('trx_id', $entry->reference)->count());
        Http::assertSentCount(1);
    }

    public function test_payza_signed_success_webhook_credits_once_without_a_second_provider_request()
    {
        $user = $this->createUser(3, 'KES');
        $entry = $this->createProviderTransaction($user, [
            'reference' => 'payza-deposit-1',
            'provider' => 'payza',
            'currency_code' => 'KES',
            'amount' => 25,
            'net_amount' => 25,
        ]);
        $signingSecret = 'payza-webhook-test-secret';
        $this->saveProviderSettings('payza', [
            'webhook_secret' => $signingSecret,
        ]);

        Http::fake();
        $payload = [
            'event' => 'payment.success',
            'reference' => $entry->reference,
            'status' => 'success',
            'amount' => 25,
            'currency' => 'KES',
        ];
        $body = json_encode($payload);
        $controller = app(WalletWebhookController::class);

        $invalidResponse = $controller->payza($this->jsonRequest($payload, [
            'X-Payza-Signature' => 'invalid-signature',
        ], $body));
        $this->assertSame(401, $invalidResponse->getStatusCode());
        $this->assertEquals(3, (float)$user->fresh()->balance);

        $signature = hash_hmac('sha256', $body, $signingSecret);
        $firstResponse = $controller->payza($this->jsonRequest($payload, [
            'X-Payza-Signature' => $signature,
        ], $body));
        $secondResponse = $controller->payza($this->jsonRequest($payload, [
            'X-Payza-Signature' => $signature,
        ], $body));

        $this->assertSame(200, $firstResponse->getStatusCode());
        $this->assertSame(200, $secondResponse->getStatusCode());
        $this->assertEquals(28, (float)$user->fresh()->balance);
        $this->assertSame('succeeded', $entry->fresh()->status);
        $this->assertSame(1, Transaction::where('trx_id', $entry->reference)->count());
        Http::assertNothingSent();
    }

    public function test_payza_does_not_credit_a_signed_success_event_when_amount_or_currency_does_not_match()
    {
        $user = $this->createUser(10, 'KES');
        $entry = $this->createProviderTransaction($user, [
            'reference' => 'payza-mismatch-1',
            'provider' => 'payza',
            'currency_code' => 'KES',
            'amount' => 25,
            'net_amount' => 25,
        ]);
        $signingSecret = 'payza-webhook-test-secret';
        $this->saveProviderSettings('payza', [
            'webhook_secret' => $signingSecret,
        ]);

        Http::fake();
        $payload = [
            'event' => 'payment.success',
            'reference' => $entry->reference,
            'status' => 'success',
            'amount' => 24,
            'currency' => 'KES',
        ];
        $body = json_encode($payload);
        $response = app(WalletWebhookController::class)->payza($this->jsonRequest(
            $payload,
            ['X-Payza-Signature' => hash_hmac('sha256', $body, $signingSecret)],
            $body
        ));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals(10, (float)$user->fresh()->balance);
        $this->assertSame('failed', $entry->fresh()->status);
        $this->assertSame(0, Transaction::count());
        Http::assertNothingSent();
    }

    public function test_payza_kes_payment_uses_hosted_checkout_without_sending_an_stk_push()
    {
        Http::fake([
            'https://payzaapi.co.ke/api/v1/pay' => Http::response([
                'success' => true,
                'data' => ['payment_url' => 'https://payzaapi.co.ke/payment/checkout?ref=wallet-ref'],
            ], 200),
        ]);

        $client = app(PayzaClient::class);
        $settings = ['public_key' => 'test-public', 'secret_key' => 'test-secret'];
        $payment = [
            'amount' => 1500,
            'currency' => 'KES',
            'email' => 'player@example.test',
            'name' => 'Test Player',
            'phone' => '254712345678',
            'callback_url' => 'https://example.test/wallet/callback',
            'redirect_url' => 'https://example.test/wallet/return',
            'cancel_url' => 'https://example.test/wallet',
            'reference' => 'wallet-ref',
            'metadata' => ['wallet_reference' => 'wallet-ref'],
        ];

        $response = $client->initiatePayment($settings, $payment);

        $this->assertSame(
            'https://payzaapi.co.ke/payment/checkout?ref=wallet-ref',
            data_get($response, 'data.payment_url')
        );
        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://payzaapi.co.ke/api/v1/pay'
                && ($data['stk_push'] ?? null) === false
                && ($data['customer']['email'] ?? null) === 'player@example.test';
        });
    }

    public function test_undocumented_payza_payout_webhook_does_not_change_withdrawal_state()
    {
        $user = $this->createUser(60, 'USD');
        $entry = $this->createProviderTransaction($user, [
            'reference' => 'payza-payout-webhook-1',
            'provider' => 'payza',
            'direction' => 'withdrawal',
            'currency_code' => 'USD',
            'amount' => 40,
            'net_amount' => 40,
            'status' => 'processing',
        ]);
        $signingSecret = 'payza-webhook-test-secret';
        $this->saveProviderSettings('payza', ['webhook_secret' => $signingSecret]);
        Http::fake();

        $payload = [
            'event' => 'payout.completed',
            'reference' => $entry->reference,
            'status' => 'completed',
            'amount' => 40,
            'currency' => 'USD',
        ];
        $body = json_encode($payload);
        $response = app(WalletWebhookController::class)->payza($this->jsonRequest(
            $payload,
            ['X-Payza-Signature' => hash_hmac('sha256', $body, $signingSecret)],
            $body
        ));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('processing', $entry->fresh()->status);
        $this->assertEquals(60, (float)$user->fresh()->balance);
        $this->assertSame(0, Transaction::count());
        Http::assertNothingSent();
    }

    public function test_kes_routing_stays_on_payhero_and_makamesco_even_if_saved_routes_are_stale()
    {
        $routing = PaymentProviderSetting::forProvider('routing');
        $routing->settings = [
            'KES' => ['deposit' => 'payza', 'withdrawal' => 'payza'],
            'GHS' => ['deposit' => 'payza', 'withdrawal' => 'payza'],
        ];
        $routing->save();

        $routes = app(CurrencyService::class)->providerRoutes();
        $this->assertSame('payhero', $routes['KES']['deposit']);
        $this->assertSame('makamesco', $routes['KES']['withdrawal']);
        $this->assertSame('payza', $routes['GHS']['deposit']);

        $view = app(WalletProviderController::class)->index();
        $routeOptions = $view->getData()['routeOptions'];
        $this->assertSame(['payhero'], $routeOptions['KES']['deposit']);
        $this->assertSame(['makamesco'], $routeOptions['KES']['withdrawal']);
    }

    public function test_invalid_wallet_currency_shows_a_specific_field_error()
    {
        $user = $this->createUser(10, 'USD');
        $this->actingAs($user);

        $response = app(WalletController::class)->updateCurrency(
            Request::create('/user/wallet/currency', 'POST', ['wallet_currency' => 'ZZZ'])
        );

        $this->assertSame(route('user.wallet'), $response->getTargetUrl());
        $errors = $response->getSession()->get('errors');
        $this->assertSame('That wallet currency is not supported.', $errors->first('wallet_currency'));
    }

    public function test_kes_withdrawal_is_sent_to_makamesco_b2c_and_kept_pending_for_confirmation()
    {
        $user = $this->createUser(100, 'KES');
        $this->actingAs($user);
        $this->saveProviderSettings('makamesco', ['api_key' => 'makamesco-test-key']);

        Http::fake([
            'https://makamescopay.com/api/payments/b2c' => Http::response([
                'status' => 'pending',
                'conversationId' => 'makamesco-b2c-001',
                'feeAmount' => 8,
                'totalDeducted' => 58,
            ], 200),
        ]);

        $request = Request::create('/user/wallet/withdraw', 'POST', [
            'amount' => 50,
            'account_name' => 'Test Wallet User',
            'account_number' => '0712345678',
            'method' => 'mpesa',
        ]);
        $response = app(WalletController::class)->withdraw($request);

        $entry = WalletProviderTransaction::where('user_id', $user->id)
            ->where('direction', 'withdrawal')
            ->firstOrFail();

        $this->assertSame('makamesco', $entry->provider);
        $this->assertSame('KES', $entry->currency_code);
        $this->assertSame('processing', $entry->status);
        $this->assertSame('makamesco-b2c-001', $entry->provider_reference);
        $this->assertEquals(50, (float)$user->fresh()->balance);
        $this->assertSame(route('user.wallet', ['reference' => $entry->reference]), $response->getTargetUrl());

        Http::assertSent(function ($sent) {
            $data = $sent->data();
            return $sent->method() === 'POST'
                && $sent->url() === 'https://makamescopay.com/api/payments/b2c'
                && in_array('makamesco-test-key', $sent->header('X-API-Key'), true)
                && ($data['phoneNumber'] ?? null) === '254712345678'
                && (float)($data['amount'] ?? 0) === 50.0
                && ($data['commandId'] ?? null) === 'BusinessPayment';
        });
        Http::assertSentCount(1);
    }

    public function test_paystack_webhook_requires_a_valid_signature_and_credits_a_verified_charge_only_once()
    {
        $user = $this->createUser(7, 'USD');
        $entry = $this->createProviderTransaction($user, [
            'reference' => 'paystack-deposit-1',
            'provider' => 'paystack',
            'currency_code' => 'USD',
            'amount' => 31,
            'net_amount' => 31,
        ]);
        $secretKey = 'paystack-test-secret';
        $this->saveProviderSettings('paystack', ['secret_key' => $secretKey]);

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'success', 'amount' => 3100, 'currency' => 'USD'],
            ], 200),
        ]);

        $payload = ['event' => 'charge.success', 'data' => ['reference' => $entry->reference]];
        $body = json_encode($payload);
        $controller = app(WalletWebhookController::class);
        $invalid = $controller->paystack($this->jsonRequest($payload, [
            'x-paystack-signature' => 'invalid-signature',
        ], $body));

        $this->assertSame(401, $invalid->getStatusCode());
        $this->assertEquals(7, (float)$user->fresh()->balance);
        Http::assertNothingSent();

        $signature = hash_hmac('sha512', $body, $secretKey);
        $firstResponse = $controller->paystack($this->jsonRequest($payload, [
            'x-paystack-signature' => $signature,
        ], $body));
        $secondResponse = $controller->paystack($this->jsonRequest($payload, [
            'x-paystack-signature' => $signature,
        ], $body));

        $this->assertSame(200, $firstResponse->getStatusCode());
        $this->assertSame(200, $secondResponse->getStatusCode());
        $this->assertEquals(38, (float)$user->fresh()->balance);
        $this->assertSame('succeeded', $entry->fresh()->status);
        $this->assertSame(1, Transaction::where('trx_id', $entry->reference)->count());
        Http::assertSentCount(2);
    }

    public function test_pending_payza_payout_stays_reserved_then_completes_when_a_later_status_check_confirms_it()
    {
        $user = $this->createUser(60, 'USD');
        $entry = $this->createProviderTransaction($user, [
            'reference' => 'payza-payout-1',
            'provider' => 'payza',
            'direction' => 'withdrawal',
            'currency_code' => 'USD',
            'amount' => 40,
            'net_amount' => 40,
            'provider_reference' => 'provider-payout-1',
            'status' => 'processing',
        ]);
        $this->saveProviderSettings('payza', [
            'public_key' => 'payza-test-public',
            'secret_key' => 'payza-test-secret',
            'webhook_secret' => 'payza-webhook-test-secret',
        ]);

        Http::fake([
            'https://payzaapi.co.ke/api/v1/payouts*' => Http::sequence()
                ->push(['success' => false, 'message' => 'temporarily unavailable'], 503)
                ->push(['success' => true, 'data' => ['payout' => ['status' => 'pending']]], 200)
                ->push(['success' => true, 'data' => ['payout' => ['status' => 'completed']]], 200),
        ]);

        $this->artisan('wallet:payouts:sync')->assertExitCode(0);
        $this->assertSame('processing', $entry->fresh()->status);
        $this->assertEquals(60, (float)$user->fresh()->balance);
        $this->assertSame(0, Transaction::count());

        $this->artisan('wallet:payouts:sync')->assertExitCode(0);
        $this->assertSame('processing', $entry->fresh()->status);
        $this->assertEquals(60, (float)$user->fresh()->balance);

        $this->artisan('wallet:payouts:sync')->assertExitCode(0);

        $this->assertSame('succeeded', $entry->fresh()->status);
        $this->assertEquals(60, (float)$user->fresh()->balance);
        $this->assertSame(0, Transaction::count());
        Http::assertSentCount(3);
    }

    public function test_failed_makamesco_payout_refunds_once_and_is_not_retried_after_failure()
    {
        $user = $this->createUser(60, 'KES');
        $entry = $this->createProviderTransaction($user, [
            'reference' => 'makamesco-payout-1',
            'provider' => 'makamesco',
            'direction' => 'withdrawal',
            'currency_code' => 'KES',
            'amount' => 40,
            'net_amount' => 40,
            'provider_reference' => 'conversation-1',
            'status' => 'processing',
        ]);
        $this->saveProviderSettings('makamesco', ['api_key' => 'makamesco-test-key']);

        Http::fake([
            'https://makamescopay.com/api/payments/b2c/status/conversation-1' => Http::response([
                'status' => 'failed',
            ], 200),
        ]);

        $this->artisan('wallet:payouts:sync')->assertExitCode(0);
        $this->artisan('wallet:payouts:sync')->assertExitCode(0);

        $this->assertSame('failed', $entry->fresh()->status);
        $this->assertNotNull($entry->fresh()->refunded_at);
        $this->assertEquals(100, (float)$user->fresh()->balance);
        $this->assertSame(1, Transaction::where('trx_id', $entry->reference . '-refund')->count());
        Http::assertSentCount(1);
    }

    public function test_payout_status_checks_rotate_beyond_the_first_hundred_pending_records()
    {
        $user = $this->createUser(500, 'USD');
        $this->saveProviderSettings('payza', [
            'public_key' => 'payza-test-public',
            'secret_key' => 'payza-test-secret',
            'webhook_secret' => 'payza-webhook-test-secret',
        ]);

        for ($index = 1; $index <= 101; $index++) {
            $this->createProviderTransaction($user, [
                'reference' => 'payza-payout-queue-' . $index,
                'provider' => 'payza',
                'direction' => 'withdrawal',
                'currency_code' => 'USD',
                'amount' => 1,
                'net_amount' => 1,
                'provider_reference' => 'queue-' . $index,
                'status' => 'processing',
            ]);
        }

        Http::fake([
            'https://payzaapi.co.ke/api/v1/payouts*' => Http::response([
                'success' => true,
                'data' => ['payout' => ['status' => 'pending']],
            ], 200),
        ]);

        $this->artisan('wallet:payouts:sync')->assertExitCode(0);
        Http::assertSentCount(100);

        $this->artisan('wallet:payouts:sync')->assertExitCode(0);
        Http::assertSentCount(200);
        Http::assertSent(function ($request) {
            return strpos($request->url(), 'reference=queue-101') !== false;
        });
    }

    public function test_manual_deposit_is_credited_once_after_admin_approval()
    {
        $user = $this->createUser(10, 'KES');
        DB::table('gateways')->insert([
            'id' => 1000,
            'code' => 'manual-test',
            'name' => 'Manual KES transfer',
            'status' => 1,
            'parameters' => json_encode([
                'receipt' => [
                    'field_level' => 'Receipt reference',
                    'type' => 'text',
                    'validation' => 'required',
                ],
            ]),
            'currency' => 'KES',
            'symbol' => 'KSh',
            'min_amount' => 1,
            'max_amount' => 1000,
            'fixed_charge' => 1,
            'percentage_charge' => 2,
            'convention_rate' => 1,
            'sort_by' => 1,
            'note' => 'Use the approved transfer instructions.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);
        $submit = Request::create('/user/wallet/deposit/manual', 'POST', [
            'gateway_id' => 1000,
            'amount' => 50,
            'payment_fields' => [0 => 'receipt-123'],
        ]);
        app(WalletController::class)->manualDeposit($submit);

        $entry = WalletProviderTransaction::where('provider', 'manual')->firstOrFail();
        $this->assertSame('pending', $entry->status);
        $this->assertEquals(52, (float)data_get($entry->metadata, 'expected_payment'));
        $this->assertEquals(10, (float)$user->fresh()->balance);

        $approve = Request::create('/admin/wallet-manual/' . $entry->id . '/review', 'POST', [
            'action' => 'approve',
            'feedback' => 'Transfer verified',
            'provider_reference' => 'verified-receipt-123',
        ]);
        app(WalletManualController::class)->review($approve, $entry->id);
        app(WalletManualController::class)->review($approve, $entry->id);

        $this->assertSame('succeeded', $entry->fresh()->status);
        $this->assertEquals(60, (float)$user->fresh()->balance);
        $this->assertSame(1, Transaction::where('trx_id', $entry->reference)->count());
    }

    public function test_rejected_manual_withdrawal_refunds_reserved_balance_only_once()
    {
        $user = $this->createUser(80, 'KES');
        $this->actingAs($user);
        $submit = Request::create('/user/wallet/withdraw/manual', 'POST', [
            'amount' => 30,
            'payout_method' => 'Bank transfer',
            'account_name' => 'Test Player',
            'account_number' => '001234567',
            'bank_name' => 'Test Bank',
            'payment_notes' => 'Test payout',
        ]);
        app(WalletController::class)->manualWithdraw($submit);

        $entry = WalletProviderTransaction::where('provider', 'manual')
            ->where('direction', 'withdrawal')
            ->firstOrFail();
        $this->assertSame('pending', $entry->status);
        $this->assertEquals(50, (float)$user->fresh()->balance);

        $reject = Request::create('/admin/wallet-manual/' . $entry->id . '/review', 'POST', [
            'action' => 'reject',
            'feedback' => 'Account details could not be verified.',
        ]);
        app(WalletManualController::class)->review($reject, $entry->id);
        app(WalletManualController::class)->review($reject, $entry->id);

        $this->assertSame('failed', $entry->fresh()->status);
        $this->assertNotNull($entry->fresh()->refunded_at);
        $this->assertEquals(80, (float)$user->fresh()->balance);
        $this->assertSame(2, Transaction::count());
    }

    private function createIsolatedSchema()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('wallet_currency', 3)->default('USD');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 20, 4);
            $table->decimal('charge', 20, 4)->default(0);
            $table->string('trx_type', 4)->nullable();
            $table->decimal('final_balance', 20, 4)->default(0);
            $table->string('trx_id', 191)->unique();
            $table->text('remarks')->nullable();
            $table->string('wallet_currency', 3)->default('USD');
            $table->decimal('usd_rate', 20, 10)->nullable();
            $table->timestamps();
        });

        Schema::create('wallet_provider_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('reference', 80)->unique();
            $table->string('provider', 32);
            $table->string('direction', 16);
            $table->string('currency_code', 3);
            $table->decimal('amount', 20, 4);
            $table->decimal('fee', 20, 4)->default(0);
            $table->decimal('net_amount', 20, 4);
            $table->decimal('usd_rate', 20, 10)->nullable();
            $table->string('status', 16)->default('pending');
            $table->string('provider_reference', 191)->nullable();
            $table->text('metadata')->nullable();
            $table->string('failure_reason', 500)->nullable();
            $table->timestamp('last_status_checked_at')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_provider_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->unique();
            $table->text('settings');
            $table->timestamps();
        });

        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3)->unique();
            $table->decimal('usd_to_currency', 20, 10);
            $table->string('source', 40)->default('manual');
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });

        Schema::create('gateways', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
            $table->text('parameters')->nullable();
            $table->text('currencies')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('symbol')->nullable();
            $table->decimal('min_amount', 20, 4)->default(0);
            $table->decimal('max_amount', 20, 4)->default(0);
            $table->decimal('percentage_charge', 20, 4)->default(0);
            $table->decimal('fixed_charge', 20, 4)->default(0);
            $table->decimal('convention_rate', 20, 10)->default(1);
            $table->integer('sort_by')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        DB::table('currency_rates')->insert([
            'currency_code' => 'KES',
            'usd_to_currency' => 130,
            'source' => 'test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser($balance, $currency)
    {
        return User::create([
            'balance' => $balance,
            'wallet_currency' => $currency,
        ]);
    }

    private function createProviderTransaction(User $user, array $overrides = [])
    {
        return WalletProviderTransaction::create(array_merge([
            'user_id' => $user->id,
            'reference' => 'wallet-ref-' . uniqid(),
            'provider' => 'payhero',
            'direction' => 'deposit',
            'currency_code' => $user->wallet_currency,
            'amount' => 10,
            'fee' => 0,
            'net_amount' => 10,
            'usd_rate' => 1,
            'status' => 'pending',
        ], $overrides));
    }

    private function saveProviderSettings($provider, array $settings)
    {
        return PaymentProviderSetting::create([
            'provider' => $provider,
            'settings' => $settings,
        ]);
    }

    private function jsonRequest(array $payload, array $headers = [], ?string $body = null)
    {
        $body = $body ?: json_encode($payload);
        $request = Request::create(
            '/api/wallet/callback',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        );
        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }
}
