<form method="POST" action="{{ route('user.wallet.currency') }}"
      class="wallet-currency-switch d-inline-flex align-items-center mr-2">
    @csrf
    <label class="sr-only" for="header-wallet-currency">@lang('Wallet currency')</label>
    <select id="header-wallet-currency"
            name="wallet_currency"
            class="form-control form-control-sm"
            aria-label="@lang('Switch wallet currency')"
            title="@lang('Switch wallet currency')"
            style="width: auto; min-width: 76px;"
            onchange="this.form.submit()">
        @foreach(config('currencies.supported', []) as $code => $currency)
            <option value="{{ $code }}"
                {{ strtoupper(auth()->user()->wallet_currency ?: 'USD') === $code ? 'selected' : '' }}>
                {{ $code }}
            </option>
        @endforeach
    </select>
    <noscript>
        <button type="submit" class="btn btn-sm btn-custom ml-1">@lang('Apply')</button>
    </noscript>
</form>
