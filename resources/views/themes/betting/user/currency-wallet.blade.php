@extends($theme.'layouts.user')

@section('title', trans('Wallet'))

@section('content')
    @php
        $activeWalletOperation = old('wallet_operation') === 'withdrawal' ? 'withdrawal' : 'deposit';
    @endphp
    <div class="row g-3 wallet-page">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('notice'))
                <div class="alert alert-info">{{ session('notice') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <div class="alert alert-info" id="wallet-payment-status" @if(!request()->query('reference')) style="display:none" @endif>
                @if(request()->query('cancelled'))
                    Checkout was cancelled. No deposit was credited.
                @elseif(request()->query('reference'))
                    Checking transaction status…
                @endif
            </div>
        </div>

        <div class="col-lg-4 wallet-balance-column">
            <div class="card secbg br-4">
                <div class="card-body">
                    <p class="mb-1">@lang('Available balance')</p>
                    <h2>{{ $currencySymbol }} {{ number_format((float)$user->balance, 2) }} <small>{{ $currency }}</small></h2>
                    <p class="text-muted mb-0">USD is the conversion base. Existing balances remain USD until you change the wallet currency.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-8 wallet-currency-column">
            <div class="card secbg br-4">
                <div class="card-body">
                    <h4>@lang('Wallet currency')</h4>
                    <p class="text-muted">
                        New accounts default to the currency mapped to their country.
                        Suggested for your country: <strong>{{ $suggestedCurrency }}</strong>.
                        Changing currency converts your available balance. A current exchange rate and the administrator's fee are shown before you confirm.
                    </p>
                    <form id="wallet-currency-form" method="post" action="{{ route('user.wallet.currency') }}" class="row align-items-end">
                        @csrf
                        <input id="wallet-currency-quote-token" type="hidden" name="quote_token" value="">
                        <div class="col-md-8">
                            <label for="wallet_currency">@lang('Default wallet currency')</label>
                            <select id="wallet_currency" name="wallet_currency" class="form-control" required>
                                @foreach($currencies as $code => $details)
                                    <option value="{{ $code }}" {{ $targetCurrency === $code ? 'selected' : '' }}>
                                        {{ $details['name'] }} ({{ $code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0">
                            <button type="submit" class="btn-custom w-100">Review conversion</button>
                        </div>
                        <div class="col-12">
                            <div id="wallet-currency-quote-result" class="mt-3" role="status" aria-live="polite" hidden></div>
                        </div>
                    </form>
                    @if(!$rateAvailable)
                        <div class="alert alert-warning mt-3 mb-0">
                            No current USD exchange rate is stored for {{ $currency }}. Native-currency deposits and withdrawals still work, but betting and balance conversion require an administrator to configure CurrencyLayer and update rates.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="wallet-operation-switcher" role="group" aria-label="Choose wallet action">
                <button type="button"
                        class="wallet-operation-choice"
                        data-wallet-operation-choice="deposit"
                        aria-pressed="{{ $activeWalletOperation === 'deposit' ? 'true' : 'false' }}">
                    <span class="wallet-operation-choice-title">Deposit funds</span>
                    <span class="wallet-operation-choice-description">Automatic and manual deposit options</span>
                </button>
                <button type="button"
                        class="wallet-operation-choice"
                        data-wallet-operation-choice="withdrawal"
                        aria-pressed="{{ $activeWalletOperation === 'withdrawal' ? 'true' : 'false' }}">
                    <span class="wallet-operation-choice-title">Withdraw funds</span>
                    <span class="wallet-operation-choice-description">Provider and admin-reviewed withdrawals</span>
                </button>
            </div>
            <p class="wallet-operation-help" id="wallet-operation-help" aria-live="polite">
                @if($activeWalletOperation === 'withdrawal')
                    Choose an automatic provider or submit a manual withdrawal request for review.
                @else
                    Choose an automatic provider or an admin-managed manual deposit option.
                @endif
            </p>
        </div>

        <div class="col-lg-6 wallet-flow-panel wallet-deposit-panel" id="wallet-deposit-automatic" data-wallet-operation-panel="deposit">
            <div class="card secbg br-4 h-100">
                <div class="card-body">
                    <h4>@lang('Deposit')</h4>
                    <p class="text-muted">
                        Route: <strong>
                            @if($depositProvider === 'payhero') PayHero M-Pesa STK
                            @elseif($depositProvider === 'paystack') Card (Paystack)
                            @elseif($depositProvider === 'payza') Payza
                            @else Not configured
                            @endif
                        </strong>
                    </p>
                    <form method="post" action="{{ route('user.wallet.deposit') }}">
                        @csrf
                        <input type="hidden" name="wallet_operation" value="deposit">
                        <div class="form-group mb-3">
                            <label for="deposit_amount">@lang('Amount') ({{ $currency }})</label>
                            <input id="deposit_amount" class="form-control" type="number" name="amount" min="{{ $depositProvider === 'payhero' ? '1' : '0.01' }}" step="{{ $depositProvider === 'payhero' ? '1' : '0.01' }}" value="{{ old('amount') }}" required>
                        </div>
                        @if(in_array($depositProvider, ['payhero', 'payza'], true))
                            <div class="form-group mb-3">
                                <label for="deposit_phone">{{ $depositProvider === 'payhero' ? 'M-Pesa phone number' : 'Phone number (optional)' }}</label>
                                <div class="input-group">
                                    @if($depositProvider === 'payhero')
                                        <span class="input-group-text">+254</span>
                                    @endif
                                    <input id="deposit_phone" class="form-control" type="tel" name="phone_number"
                                           value="{{ $depositProvider === 'payhero' ? $payheroPhoneInput : $payzaPhoneInput }}"
                                           placeholder="{{ $depositProvider === 'payhero' ? '712345678' : '+254712345678' }}"
                                           @if($depositProvider === 'payhero') required @endif
                                           aria-describedby="deposit-phone-help">
                                </div>
                                <small id="deposit-phone-help" class="form-text text-muted">
                                    @if($depositProvider === 'payhero')
                                        Enter a Kenyan number without +254; the country code is added once.
                                    @else
                                        Enter the full international number, including its country calling code. Duplicate saved prefixes are normalized.
                                    @endif
                                </small>
                            </div>
                        @endif
                        <button class="btn-custom w-100" type="submit" @if(!$depositProvider) disabled @endif>
                            @if($depositProvider === 'paystack') Deposit by Card
                            @elseif($depositProvider === 'payhero') Send M-Pesa prompt
                            @else Continue to deposit
                            @endif
                        </button>
                    </form>
                    @if(in_array($depositProvider, ['payhero', 'payza', 'paystack'], true))
                        <div class="wallet-support-actions border rounded p-3 mt-3">
                            <p class="mb-2"><strong>Deposit debited but not credited?</strong> Open a support ticket with the transaction reference, or view the support phone and email on our contact page.</p>
                            <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('user.ticket.create', ['subject' => 'Wallet deposit not credited']) }}">Open support ticket</a>
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('contact') }}">View support contacts</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 wallet-flow-panel wallet-withdraw-panel" id="wallet-withdraw-automatic" data-wallet-operation-panel="withdrawal">
            <div class="card secbg br-4 h-100">
                <div class="card-body">
                    <h4>@lang('Withdraw')</h4>
                    <p class="text-muted">
                        Route: <strong>{{ $withdrawalProvider === 'makamesco' ? 'Makamesco B2C (M-Pesa)' : ($withdrawalProvider === 'payza' ? 'Payza Payouts' : 'Not configured') }}</strong>
                    </p>
                    <form method="post" action="{{ route('user.wallet.withdraw') }}">
                        @csrf
                        <input type="hidden" name="wallet_operation" value="withdrawal">
                        <div class="form-group mb-3">
                            <label for="withdraw_amount">@lang('Amount') ({{ $currency }})</label>
                            <input id="withdraw_amount" class="form-control" type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="account_name">@lang('Account name')</label>
                            <input id="account_name" class="form-control" name="account_name" maxlength="120" value="{{ old('account_name', $user->fullname) }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="account_number">{{ $withdrawalProvider === 'makamesco' ? 'Safaricom M-Pesa number' : 'Account number / wallet address' }}</label>
                            @if($withdrawalProvider === 'makamesco')
                                <div class="input-group">
                                    <span class="input-group-text">+254</span>
                                    <input id="account_number" class="form-control" name="account_number" maxlength="32"
                                           type="tel" inputmode="tel" autocomplete="tel"
                                           value="{{ old('account_number', $phoneCode === '254' ? $nationalPhone : '') }}"
                                           placeholder="712345678" aria-describedby="mpesa-number-help" required>
                                </div>
                            @else
                                <input id="account_number" class="form-control" name="account_number" maxlength="80"
                                       value="{{ old('account_number') }}" required>
                            @endif
                            @if($withdrawalProvider === 'makamesco')
                                <small id="mpesa-number-help" class="form-text text-muted">Enter the local Kenyan number without +254; the country code is added once.</small>
                            @elseif($withdrawalProvider === 'payza')
                                <small class="form-text text-muted">For mobile-money methods, use the number's saved country code once. Bank account numbers are sent unchanged.</small>
                            @endif
                        </div>
                        @if($withdrawalProvider === 'payza')
                            <div class="form-group mb-3">
                                <label for="payout_method">@lang('Payout method')</label>
                                <select id="payout_method" class="form-control" name="method" required>
                                    <option value="">Loading available methods…</option>
                                </select>
                                <small class="text-muted" id="payout-method-note"></small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="bank_code">@lang('Bank code (bank transfer only)')</label>
                                <input id="bank_code" class="form-control" name="bank_code" maxlength="40" value="{{ old('bank_code') }}">
                            </div>
                            <div class="form-group mb-3">
                                <label for="bank_name">@lang('Bank name (optional)')</label>
                                <input id="bank_name" class="form-control" name="bank_name" maxlength="120" value="{{ old('bank_name') }}">
                            </div>
                        @else
                            <input type="hidden" name="method" value="mpesa">
                        @endif
                        <button class="btn-custom w-100" type="submit" @if(!$withdrawalProvider) disabled @endif>
                            @lang('Request withdrawal')
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if($manualDepositGateways->isNotEmpty())
            <div class="col-lg-6 wallet-flow-panel wallet-manual-deposit-panel" id="wallet-deposit-manual" data-wallet-operation-panel="deposit">
                <div class="card secbg br-4 h-100">
                    <div class="card-body">
                        <h4>@lang('Manual deposit options') ({{ $currency }})</h4>
                        <p class="text-muted">Follow the instructions, submit payment proof, and an administrator will review the deposit before crediting your wallet.</p>
                        @foreach($manualDepositGateways as $manualGateway)
                            <div class="manual-wallet-method border rounded p-3 mb-3">
                                <h5>{{ $manualGateway->name }}</h5>
                                <p class="text-muted mb-2">
                                    @lang('Limits'): {{ $currencySymbol }} {{ number_format((float)$manualGateway->min_amount, 2) }}
                                    – {{ $currencySymbol }} {{ number_format((float)$manualGateway->max_amount, 2) }} {{ $currency }}.
                                    @lang('Fee'): {{ $currencySymbol }} {{ number_format((float)$manualGateway->fixed_charge, 2) }}
                                    + {{ number_format((float)$manualGateway->percentage_charge, 2) }}%.
                                </p>
                                @if($manualGateway->manual_instruction_html)
                                    <div class="manual-wallet-instructions mb-3">{!! $manualGateway->manual_instruction_html !!}</div>
                                @endif

                                <form class="manual-wallet-deposit-form" method="post"
                                      action="{{ route('user.wallet.manual-deposit') }}"
                                      enctype="multipart/form-data"
                                      data-fixed-charge="{{ max(0, (float)$manualGateway->fixed_charge) }}"
                                      data-percentage-charge="{{ max(0, (float)$manualGateway->percentage_charge) }}">
                                    @csrf
                                    <input type="hidden" name="wallet_operation" value="deposit">
                                    <input type="hidden" name="gateway_id" value="{{ $manualGateway->id }}">
                                    <div class="form-group mb-3">
                                        <label for="manual_amount_{{ $manualGateway->id }}">@lang('Wallet credit amount') ({{ $currency }})</label>
                                        <input id="manual_amount_{{ $manualGateway->id }}" class="form-control manual-wallet-amount"
                                               type="number" name="amount" min="{{ max(0.01, (float)$manualGateway->min_amount) }}"
                                               @if((float)$manualGateway->max_amount > 0) max="{{ (float)$manualGateway->max_amount }}" @endif
                                               step="0.01" value="{{ old('amount') }}" required>
                                    </div>
                                    <p class="small text-muted">
                                        @lang('Estimated payment total'):
                                        <strong class="manual-wallet-total">{{ $currencySymbol }} {{ number_format((float)$manualGateway->min_amount + (float)$manualGateway->fixed_charge + ((float)$manualGateway->min_amount * (float)$manualGateway->percentage_charge / 100), 2) }} {{ $currency }}</strong>
                                    </p>

                                    @foreach(($manualGateway->parameters ?: []) as $fieldKey => $field)
                                        @if(in_array($field->type ?? '', ['text', 'textarea', 'file'], true))
                                            <div class="form-group mb-3">
                                                <label for="manual_{{ $manualGateway->id }}_field_{{ $loop->index }}">
                                                    {{ trans($field->field_level ?? 'Payment details') }}
                                                    @if(($field->validation ?? '') === 'required') <span class="text-danger">*</span> @endif
                                                </label>
                                                @if($field->type === 'textarea')
                                                    <textarea id="manual_{{ $manualGateway->id }}_field_{{ $loop->index }}"
                                                              class="form-control"
                                                              name="payment_fields[{{ $loop->index }}]"
                                                              rows="3"
                                                              @if(($field->validation ?? '') === 'required') required @endif></textarea>
                                                @elseif($field->type === 'file')
                                                    <input id="manual_{{ $manualGateway->id }}_field_{{ $loop->index }}"
                                                           class="form-control" type="file"
                                                           name="payment_fields[{{ $loop->index }}]"
                                                           accept="image/jpeg,image/png"
                                                           @if(($field->validation ?? '') === 'required') required @endif>
                                                    <small class="text-muted">JPEG or PNG, up to 2 MB.</small>
                                                @else
                                                    <input id="manual_{{ $manualGateway->id }}_field_{{ $loop->index }}"
                                                           class="form-control" type="text"
                                                           name="payment_fields[{{ $loop->index }}]"
                                                           maxlength="191"
                                                           @if(($field->validation ?? '') === 'required') required @endif>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach

                                    <button class="btn-custom w-100" type="submit">@lang('Submit deposit for review')</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="col-lg-6 wallet-flow-panel wallet-manual-withdraw-panel" id="wallet-withdraw-manual" data-wallet-operation-panel="withdrawal">
            <div class="card secbg br-4 h-100">
                <div class="card-body">
                    <h4>@lang('Manual withdrawal') ({{ $currency }})</h4>
                    <p class="text-muted">An administrator will review this payout request. The amount is reserved now; rejected requests are refunded to this wallet.</p>
                    <form method="post" action="{{ route('user.wallet.manual-withdraw') }}">
                        @csrf
                        <input type="hidden" name="wallet_operation" value="withdrawal">
                        <div class="form-group mb-3">
                            <label for="manual_withdraw_amount">@lang('Amount') ({{ $currency }})</label>
                            <input id="manual_withdraw_amount" class="form-control" type="number" name="amount"
                                   min="0.01" step="0.01" value="{{ old('amount') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_payout_method">@lang('Preferred payout method')</label>
                            <input id="manual_payout_method" class="form-control" name="payout_method" maxlength="100"
                                   value="{{ old('payout_method') }}" placeholder="M-Pesa, bank transfer, or wallet" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_account_name">@lang('Account name')</label>
                            <input id="manual_account_name" class="form-control" name="account_name" maxlength="120"
                                   value="{{ old('account_name', $user->fullname) }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_account_number">@lang('Payment account or mobile number')</label>
                            <input id="manual_account_number" class="form-control" name="account_number" maxlength="120"
                                   value="{{ old('account_number') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_bank_name">@lang('Bank name (optional)')</label>
                            <input id="manual_bank_name" class="form-control" name="bank_name" maxlength="120"
                                   value="{{ old('bank_name') }}">
                        </div>
                        <div class="form-group mb-3">
                            <label for="manual_payment_notes">@lang('Additional payout details (optional)')</label>
                            <textarea id="manual_payment_notes" class="form-control" name="payment_notes" rows="3" maxlength="500">{{ old('payment_notes') }}</textarea>
                        </div>
                        <button class="btn-custom w-100" type="submit">@lang('Request manual withdrawal')</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12" id="wallet-transactions">
            <div class="card secbg br-4">
                <div class="card-body">
                    <h4>@lang('Wallet activity')</h4>
                    <div class="table-responsive wallet-activity-table">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>@lang('Reference')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Provider')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Fee')</th>
                                <th>@lang('Wallet credit / net')</th>
                                <th>@lang('Provider reference')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Support')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td data-label="@lang('Reference')"><span class="wallet-reference">{{ $transaction->reference }}</span></td>
                                    <td data-label="@lang('Type')">{{ ucfirst($transaction->direction) }}</td>
                                    <td data-label="@lang('Provider')">{{ $transaction->provider === 'paystack' ? 'Card (Paystack)' : ($transaction->provider === 'manual' ? 'Manual (admin review)' : ($transaction->direction === 'conversion' ? 'Currency conversion' : ucfirst($transaction->provider))) }}</td>
                                    <td data-label="@lang('Amount')">{{ data_get($currencies, $transaction->currency_code . '.symbol', $transaction->currency_code) }} {{ number_format((float)$transaction->amount, 2) }} {{ $transaction->currency_code }}</td>
                                    <td data-label="@lang('Fee')">{{ data_get($currencies, $transaction->currency_code . '.symbol', $transaction->currency_code) }} {{ number_format((float)$transaction->fee, 2) }} {{ $transaction->currency_code }}</td>
                                    <td data-label="@lang('Wallet credit / net')">{{ data_get($currencies, $transaction->currency_code . '.symbol', $transaction->currency_code) }} {{ number_format((float)$transaction->net_amount, 2) }} {{ $transaction->currency_code }}</td>
                                    <td data-label="@lang('Provider reference')">{{ $transaction->provider_reference ?: '—' }}</td>
                                    <td data-label="@lang('Status')">
                                        @if($transaction->status === 'succeeded')
                                            <span class="badge bg-success">Successful</span>
                                        @elseif($transaction->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                            @if($transaction->failure_reason)<small class="d-block">{{ $transaction->failure_reason }}</small>@endif
                                        @elseif($transaction->status === 'processing')
                                            <span class="badge bg-info">Processing</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($transaction->status) }}</span>
                                        @endif
                                    </td>
                                    <td data-label="@lang('Date')">
                                        {{ optional($transaction->created_at)->format('d M Y H:i') }}
                                        @if($transaction->updated_at && $transaction->updated_at != $transaction->created_at)
                                            <small class="d-block text-muted">@lang('Updated') {{ optional($transaction->updated_at)->format('d M Y H:i') }}</small>
                                        @endif
                                    </td>
                                    <td data-label="@lang('Support')">
                                        @if($transaction->direction === 'deposit' && $transaction->provider !== 'manual')
                                            <a class="btn btn-outline-primary btn-sm"
                                               href="{{ route('user.ticket.create', [
                                                    'subject' => 'Wallet deposit not credited — ' . $transaction->reference,
                                                    'message' => 'Please investigate this wallet deposit.' . "\n\n"
                                                        . 'Provider: ' . $transaction->provider . "\n"
                                                        . 'Amount: ' . number_format((float)$transaction->amount, 2) . ' ' . $transaction->currency_code . "\n"
                                                        . 'App reference: ' . $transaction->reference . "\n"
                                                        . 'Provider reference: ' . ($transaction->provider_reference ?: 'not yet available') . "\n"
                                                        . 'Current status: ' . $transaction->status,
                                                ]) }}">Contact support</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center">@lang('No wallet activity yet.')</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $transactions->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function () {
            const form = document.getElementById('wallet-currency-form');
            const currencySelect = document.getElementById('wallet_currency');
            const result = document.getElementById('wallet-currency-quote-result');
            const tokenInput = document.getElementById('wallet-currency-quote-token');
            if (!form || !currencySelect || !result || !tokenInput) return;

            let submittingConfirmedQuote = false;
            const setMessage = function (message, style) {
                result.hidden = false;
                result.className = 'alert mt-3 ' + style;
                result.textContent = message;
            };

            currencySelect.addEventListener('change', function () {
                tokenInput.value = '';
                result.hidden = true;
                result.replaceChildren();
            });

            form.addEventListener('submit', async function (event) {
                if (submittingConfirmedQuote) return;
                event.preventDefault();
                tokenInput.value = '';
                result.hidden = false;
                result.className = 'alert alert-info mt-3';
                result.textContent = 'Getting the latest conversion quote…';

                try {
                    const params = new URLSearchParams({wallet_currency: currencySelect.value});
                    const response = await fetch(@json(route('user.wallet.currency-quote')) + '?' + params.toString(), {
                        headers: {'Accept': 'application/json'}
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        const errors = data.errors ? Object.values(data.errors).flat().join(' ') : 'Could not create a conversion quote.';
                        setMessage(errors, 'alert-danger');
                        return;
                    }

                    const quote = data.quote;
                    if (!quote.token) {
                        setMessage('Your wallet already uses ' + quote.to_currency + '. No conversion is needed.', 'alert-secondary');
                        return;
                    }

                    result.replaceChildren();
                    result.className = 'alert alert-info mt-3 wallet-conversion-quote';
                    result.hidden = false;
                    const title = document.createElement('strong');
                    title.textContent = 'Confirm wallet conversion';
                    result.appendChild(title);
                    const list = document.createElement('ul');
                    list.className = 'mb-3 mt-2';
                    const lines = [
                        'Current balance: ' + data.from_symbol + ' ' + Number(quote.current_balance).toFixed(2) + ' ' + quote.from_currency,
                        'Converted amount before fee: ' + data.to_symbol + ' ' + Number(quote.gross_amount).toFixed(2) + ' ' + quote.to_currency,
                        'Conversion fee (' + Number(quote.fee_percent).toFixed(2) + '%): ' + data.to_symbol + ' ' + Number(quote.fee_amount).toFixed(2) + ' ' + quote.to_currency,
                        'New available balance: ' + data.to_symbol + ' ' + Number(quote.net_amount).toFixed(2) + ' ' + quote.to_currency
                    ];
                    lines.forEach(function (line) {
                        const item = document.createElement('li');
                        item.textContent = line;
                        list.appendChild(item);
                    });
                    result.appendChild(list);

                    const confirm = document.createElement('button');
                    confirm.type = 'button';
                    confirm.className = 'btn btn-success btn-sm mr-2';
                    confirm.textContent = 'Confirm conversion';
                    confirm.addEventListener('click', function () {
                        tokenInput.value = quote.token;
                        submittingConfirmedQuote = true;
                        form.requestSubmit();
                    });
                    result.appendChild(confirm);

                    const cancel = document.createElement('button');
                    cancel.type = 'button';
                    cancel.className = 'btn btn-outline-secondary btn-sm';
                    cancel.textContent = 'Cancel';
                    cancel.addEventListener('click', function () {
                        tokenInput.value = '';
                        result.hidden = true;
                        result.replaceChildren();
                    });
                    result.appendChild(cancel);
                } catch (error) {
                    setMessage('Could not reach the rate service. Try again later or contact support.', 'alert-danger');
                }
            });
        })();

        const walletOperationButtons = document.querySelectorAll('[data-wallet-operation-choice]');
        const walletOperationPanels = document.querySelectorAll('[data-wallet-operation-panel]');
        const walletOperationHelp = document.getElementById('wallet-operation-help');
        const walletOperationDescriptions = {
            deposit: 'Choose an automatic provider or an admin-managed manual deposit option.',
            withdrawal: 'Choose an automatic provider or submit a manual withdrawal request for review.'
        };
        const setWalletOperation = function (operation) {
            walletOperationButtons.forEach(function (button) {
                button.setAttribute('aria-pressed', button.dataset.walletOperationChoice === operation ? 'true' : 'false');
            });
            walletOperationPanels.forEach(function (panel) {
                panel.hidden = panel.dataset.walletOperationPanel !== operation;
            });
            if (walletOperationHelp && walletOperationDescriptions[operation]) {
                walletOperationHelp.textContent = walletOperationDescriptions[operation];
            }
        };
        walletOperationButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setWalletOperation(button.dataset.walletOperationChoice);
            });
        });
        setWalletOperation(@json($activeWalletOperation));

        document.querySelectorAll('.manual-wallet-deposit-form').forEach(function (form) {
            const amountInput = form.querySelector('.manual-wallet-amount');
            const totalLabel = form.querySelector('.manual-wallet-total');
            const fixedCharge = parseFloat(form.dataset.fixedCharge || '0') || 0;
            const percentageCharge = parseFloat(form.dataset.percentageCharge || '0') || 0;
            const currencySymbol = @json($currencySymbol);
            const currency = @json($currency);
            const updateEstimatedTotal = function () {
                const amount = parseFloat(amountInput.value || '0') || 0;
                totalLabel.textContent = currencySymbol + ' ' + (amount + fixedCharge + amount * percentageCharge / 100).toFixed(2) + ' ' + currency;
            };
            amountInput.addEventListener('input', updateEstimatedTotal);
            updateEstimatedTotal();
        });
    </script>
    @if($withdrawalProvider === 'payza')
        <script>
            fetch(@json(route('user.wallet.payout-methods')))
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('payout_method');
                    const note = document.getElementById('payout-method-note');
                    select.innerHTML = '';
                    if (!data.methods || data.methods.length === 0) {
                        select.innerHTML = '<option value="">No methods available</option>';
                        note.textContent = data.message || 'Payza did not return payout methods for this currency.';
                        return;
                    }
                    data.methods.forEach(method => {
                        const option = document.createElement('option');
                        option.value = method.code;
                        option.textContent = method.name + (method.fee !== null && method.fee !== undefined ? ' — fee ' + method.fee : '');
                        select.appendChild(option);
                    });
                })
                .catch(() => {
                    document.getElementById('payout_method').innerHTML = '<option value="">Could not load methods</option>';
                    document.getElementById('payout-method-note').textContent = 'Check the Payza API settings and try again.';
                });
        </script>
    @endif
    @if(request()->query('reference'))
        <script>
            (function () {
                const statusUrl = @json(route('user.wallet.status', ['reference' => request()->query('reference')]));
                const statusBox = document.getElementById('wallet-payment-status');
                let attempts = 0;
                statusBox.style.display = '';

                const refreshStatus = () => {
                    fetch(statusUrl, {headers: {'Accept': 'application/json'}})
                        .then(response => response.ok ? response.json() : Promise.reject())
                        .then(data => {
                            statusBox.textContent = data.message;
                            statusBox.className = 'alert ' + (data.status === 'succeeded' ? 'alert-success' : (data.status === 'failed' ? 'alert-danger' : 'alert-info'));
                            if (data.status === 'succeeded' || data.status === 'failed') {
                                window.setTimeout(() => window.location.reload(), 1500);
                            } else if (++attempts < 12) {
                                window.setTimeout(refreshStatus, 5000);
                            }
                        })
                        .catch(() => {
                            if (++attempts < 12) window.setTimeout(refreshStatus, 5000);
                        });
                };

                refreshStatus();
            })();
        </script>
    @endif
@endpush
