<form method="GET" action="{{ route('user.wallet') }}"
      class="wallet-currency-switch d-inline-flex align-items-center mr-2">
    <label class="sr-only" for="header-wallet-currency">@lang('Wallet currency')</label>
    <select id="header-wallet-currency"
            name="currency"
            class="form-control form-control-sm"
            aria-label="@lang('Choose a currency to review')"
            title="@lang('Choose a currency to review')"
            style="width: auto; min-width: 76px;"
            onchange="this.form.submit()">
        @foreach(config('currencies.supported', []) as $code => $currency)
            <option value="{{ $code }}"
                {{ strtoupper(auth()->user()->wallet_currency ?: 'USD') === $code ? 'selected' : '' }}>
                {{ $code }}
            </option>
        @endforeach
    </select>
</form>
