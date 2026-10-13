@extends('admin.layouts.app')

@section('title', __($title))

@section('content')
    <div class="row">
        <div class="col-12">
            @if($configurationWarning)
                <div class="alert alert-warning">{{ $configurationWarning }}</div>
            @endif
            <div class="alert alert-info">
                Existing user balances stay USD until a user switches their wallet currency. USD is the conversion base.
                Save provider credentials here; they are encrypted in the database and are never written to source files or `.env`.
            </div>
        </div>

        <div class="col-12">
            <div class="card card-primary m-0 m-md-4 my-4 shadow">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold">@lang('Automatic provider routing')</h5>
                    @if(adminAccessRoute(config('role.payment_gateway.access.edit')))
                    <form method="post" action="{{ route('admin.wallet-providers.save') }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>@lang('Currency')</th>
                                    <th>@lang('Deposit provider')</th>
                                    <th>@lang('Withdrawal provider')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($currencies as $code => $currencyInfo)
                                    <tr>
                                        <td>{{ $currencyInfo['name'] }} ({{ $code }})</td>
                                        <td>
                                            <select class="form-control" name="routes[{{ $code }}][deposit]" required>
                                                @foreach($routeOptions[$code]['deposit'] as $option)
                                                    <option value="{{ $option }}" {{ ($routes[$code]['deposit'] ?? '') === $option ? 'selected' : '' }}>
                                                        @if($option === 'payhero') PayHero M-Pesa STK
                                                        @elseif($option === 'payza') Payza
                                                        @else Card (Paystack)
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" name="routes[{{ $code }}][withdrawal]" required>
                                                @foreach($routeOptions[$code]['withdrawal'] as $option)
                                                    <option value="{{ $option }}" {{ ($routes[$code]['withdrawal'] ?? '') === $option ? 'selected' : '' }}>
                                                        {{ $option === 'makamesco' ? 'Makamesco B2C (M-Pesa)' : 'Payza Payouts' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h5 class="card-title font-weight-bold mt-4">@lang('Provider credentials')</h5>
                        <p class="text-muted">Leave a credential field empty to keep its saved value. Saved values are not shown again.</p>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="border rounded p-3 mb-3">
                                    <h6>PayHero M-Pesa</h6>
                                    <p class="text-muted">Required for KES STK deposits. Credentials are from your PayHero account; see <a href="https://docs.payhero.co.ke/docs" target="_blank" rel="noopener">PayHero API docs</a>. Register the linked collection account below to create and save its channel ID.</p>
                                    <p class="text-muted">PayHero callback: <code>{{ route('wallet.payhero.webhook', [], true) }}</code> (sent automatically with each STK request).</p>
                                    <label>PayHero username</label>
                                    <input class="form-control mb-2" name="payhero[username]" autocomplete="off">
                                    <label>PayHero password</label>
                                    <input class="form-control mb-2" type="password" name="payhero[password]" autocomplete="new-password">
                                    <div>Username: <strong>{{ !empty($settings['payhero']['username']) ? 'Saved' : 'Not set' }}</strong> · Password: <strong>{{ !empty($settings['payhero']['password']) ? 'Saved' : 'Not set' }}</strong></div>
                                    <div>Channel ID: <strong>{{ !empty($settings['payhero']['channel_id']) ? 'Configured' : 'Not set' }}</strong></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border rounded p-3 mb-3">
                                    <h6>Payza</h6>
                                    <p class="text-muted">Used for non-KES deposits and non-KES payouts. In the Payza dashboard, open API Keys and create a separate webhook signing secret. See <a href="https://payzaapi.co.ke/docs" target="_blank" rel="noopener">Payza API docs</a>.</p>
                                    <p class="text-muted">Webhook URL: <code>{{ route('wallet.payza.webhook', [], true) }}</code>. Live payout accounts may also require the server's public IP to be allowlisted in Payza.</p>
                                    <label>Public API key</label>
                                    <input class="form-control mb-2" name="payza[public_key]" autocomplete="off">
                                    <label>Secret API key</label>
                                    <input class="form-control mb-2" type="password" name="payza[secret_key]" autocomplete="new-password">
                                    <label>Webhook signing secret</label>
                                    <input class="form-control mb-2" type="password" name="payza[webhook_secret]" autocomplete="new-password">
                                    <div>API keys: <strong>{{ !empty($settings['payza']['public_key']) && !empty($settings['payza']['secret_key']) ? 'Saved' : 'Not fully configured' }}</strong> · Webhook secret: <strong>{{ !empty($settings['payza']['webhook_secret']) ? 'Saved' : 'Not set' }}</strong></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border rounded p-3 mb-3">
                                    <h6>Paystack — USD Card</h6>
                                    <p class="text-muted">Use the secret key from Paystack Dashboard → Settings → API Keys &amp; Webhooks, on an account enabled to accept USD card payments. Set the Paystack webhook URL to <code>{{ route('wallet.paystack.webhook', [], true) }}</code>.</p>
                                    <label>Paystack secret key</label>
                                    <input class="form-control mb-2" type="password" name="paystack[secret_key]" autocomplete="new-password">
                                    <div>Secret key: <strong>{{ !empty($settings['paystack']['secret_key']) ? 'Saved' : 'Not set' }}</strong></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border rounded p-3 mb-3">
                                    <h6>Makamesco B2C — KES withdrawals</h6>
                                    <p class="text-muted">Create an API key in the Makamesco dashboard and fund its B2C wallet; the published fee is charged to that wallet. See <a href="https://makamescopay.com/docs" target="_blank" rel="noopener">Makamesco API docs</a>.</p>
                                    <label>Makamesco API key</label>
                                    <input class="form-control mb-2" type="password" name="makamesco[api_key]" autocomplete="new-password">
                                    <div>API key: <strong>{{ !empty($settings['makamesco']['api_key']) ? 'Saved' : 'Not set' }}</strong></div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">@lang('Save provider settings')</button>
                    </form>
                    @else
                        <p class="text-warning">You can view routes, but do not have permission to change provider settings.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-primary m-0 m-md-4 my-4 shadow">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold">@lang('Register a PayHero payment channel')</h5>
                    <p class="text-muted">Enter the PayHero account and M-Pesa collection details linked to your business. The API response channel ID is saved automatically.</p>
                    @if(adminAccessRoute(config('role.payment_gateway.access.add')))
                    <form method="post" action="{{ route('admin.wallet-providers.register-channel') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Channel type</label>
                                <select class="form-control" name="channel_type" required>
                                    <option value="paybill">PayBill</option>
                                    <option value="till">Till</option>
                                    <option value="bank">Bank</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>PayHero account ID</label>
                                <input class="form-control" name="account_id" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>PayBill / Till / bank short code</label>
                                <input class="form-control" name="short_code" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Account number (if required)</label>
                                <input class="form-control" name="account_number">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Description</label>
                                <input class="form-control" name="description" placeholder="Business or bank account name">
                            </div>
                        </div>
                        <button class="btn btn-outline-primary" type="submit">Register channel with PayHero</button>
                    </form>
                    @else
                        <p class="text-warning">You do not have permission to register payment channels.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="alert alert-secondary">
                Currency conversion uses CurrencyLayer. Get an API key at
                <a href="https://currencylayer.com/product" target="_blank" rel="noopener">currencylayer.com/product</a>,
                then add it under <a href="{{ route('admin.currency.exchange.api.config') }}">Admin → Exchange API</a>.
                The wallet rate updater uses HTTPS; the selected CurrencyLayer plan must support HTTPS.
                The server's public <code>APP_URL</code> must also use HTTPS so payment callbacks can be registered securely.
                The server must run <code>php artisan schedule:run</code> every minute to refresh rates and check pending payouts.
            </div>
        </div>
    </div>
@endsection
