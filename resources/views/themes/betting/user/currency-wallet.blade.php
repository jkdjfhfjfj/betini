@extends($theme.'layouts.user')

@section('title', trans('Wallet'))

@section('content')
    <div class="row g-3">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
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

        <div class="col-lg-4">
            <div class="card secbg br-4">
                <div class="card-body">
                    <p class="mb-1">@lang('Available balance')</p>
                    <h2>{{ $currencySymbol }} {{ number_format((float)$user->balance, 2) }} <small>{{ $currency }}</small></h2>
                    <p class="text-muted mb-0">USD is the conversion base. Existing balances remain USD until you change the wallet currency.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card secbg br-4">
                <div class="card-body">
                    <h4>@lang('Wallet currency')</h4>
                    <p class="text-muted">
                        New accounts default to the currency mapped to their country.
                        Suggested for your country: <strong>{{ $suggestedCurrency }}</strong>.
                        Changing currency converts your available balance and is blocked while bets or payments are pending.
                    </p>
                    <form method="post" action="{{ route('user.wallet.currency') }}" class="row align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label for="wallet_currency">@lang('Default wallet currency')</label>
                            <select id="wallet_currency" name="wallet_currency" class="form-control" required>
                                @foreach($currencies as $code => $details)
                                    <option value="{{ $code }}" {{ $currency === $code ? 'selected' : '' }}>
                                        {{ $details['name'] }} ({{ $code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0">
                            <button type="submit" class="btn-custom w-100">@lang('Update currency')</button>
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

        <div class="col-lg-6">
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
                        <div class="form-group mb-3">
                            <label for="deposit_amount">@lang('Amount') ({{ $currency }})</label>
                            <input id="deposit_amount" class="form-control" type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                        </div>
                        @if($currency === 'KES')
                            <div class="form-group mb-3">
                                <label for="deposit_phone">@lang('M-Pesa phone number')</label>
                                <input id="deposit_phone" class="form-control" type="tel" name="phone_number" value="{{ old('phone_number', $user->mobile) }}" placeholder="07xx or 2547xx" required>
                            </div>
                        @endif
                        <button class="btn-custom w-100" type="submit" @if(!$depositProvider) disabled @endif>
                            @if($depositProvider === 'paystack') Deposit by Card
                            @elseif($depositProvider === 'payhero') Send M-Pesa prompt
                            @else Continue to deposit
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card secbg br-4 h-100">
                <div class="card-body">
                    <h4>@lang('Withdraw')</h4>
                    <p class="text-muted">
                        Route: <strong>{{ $withdrawalProvider === 'makamesco' ? 'Makamesco B2C (M-Pesa)' : ($withdrawalProvider === 'payza' ? 'Payza Payouts' : 'Not configured') }}</strong>
                    </p>
                    <form method="post" action="{{ route('user.wallet.withdraw') }}">
                        @csrf
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
                            <input id="account_number" class="form-control" name="account_number" maxlength="80" value="{{ old('account_number', $withdrawalProvider === 'makamesco' ? $user->mobile : '') }}" required>
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

        <div class="col-12">
            <div class="card secbg br-4">
                <div class="card-body">
                    <h4>@lang('Recent wallet activity')</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>@lang('Reference')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Provider')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Date')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->reference }}</td>
                                    <td>{{ ucfirst($transaction->direction) }}</td>
                                    <td>{{ $transaction->provider === 'paystack' ? 'Card (Paystack)' : ucfirst($transaction->provider) }}</td>
                                    <td>{{ data_get($currencies, $transaction->currency_code . '.symbol', $transaction->currency_code) }} {{ number_format((float)$transaction->amount, 2) }} {{ $transaction->currency_code }}</td>
                                    <td>
                                        @if($transaction->status === 'succeeded')
                                            <span class="badge bg-success">Successful</span>
                                        @elseif($transaction->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                            @if($transaction->failure_reason)<small class="d-block">{{ $transaction->failure_reason }}</small>@endif
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($transaction->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($transaction->created_at)->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">@lang('No wallet activity yet.')</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
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
