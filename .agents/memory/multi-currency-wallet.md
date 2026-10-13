---
name: Native-currency wallet invariants
description: Product rules for wallet currency, conversion, legacy balances, and currency switching.
---

Each user has one active native-currency wallet. Registration defaults that currency from the user's country, and the user can change it later. Betting, available-balance display, deposits, and withdrawals should use the active wallet currency.

USD is the conversion base. Preserve balances that existed before this feature as USD; never silently reinterpret them as the user's local currency. CurrencyLayer supplies current USD-based rates; admins configure its access key and conversion fee in wallet settings. A wallet currency change converts the active balance using a confirmed quote and is blocked while bets or payment transactions are unsettled.

For this wallet, PayHero remains the automatic KES deposit route and Makamesco B2C remains the automatic KES withdrawal route. Admins may explicitly enable manual KES deposit and withdrawal alternatives; these do not replace the automatic routes.

**Why:** the user chose native-currency wallets with USD as the base, most recently directed that wallet exchange rates use CurrencyLayer rather than CurrencyAPI.com, specified PayHero and Makamesco B2C for automatic KES payments, and approved manual KES alternatives alongside them.

**How to apply:** preserve USD-based conversion, native-currency balances, fresh CurrencyLayer rates and confirmed fee quotes, automatic KES provider defaults, explicit admin-controlled manual alternatives, and currency/status checks across routing, ledger updates, admin decisions, history, and migrations.
