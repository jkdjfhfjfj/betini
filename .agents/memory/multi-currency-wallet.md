---
name: Native-currency wallet invariants
description: Product rules for wallet currency, conversion, legacy balances, and currency switching.
---

Each user has one active native-currency wallet. Registration defaults that currency from the user's country, and the user can change it later. Betting, available-balance display, deposits, and withdrawals should use the active wallet currency.

USD is the conversion base. Preserve balances that existed before this feature as USD; never silently reinterpret them as the user's local currency. A wallet currency change converts the active balance using current USD-based rates and is blocked while bets or payment transactions are unsettled.

For this wallet, KES deposits must use PayHero and KES withdrawals must use Makamesco B2C. Admin configuration must not override these routes.

**Why:** the user chose native-currency wallets with USD as the base, and specified PayHero for KES deposits and Makamesco B2C for KES withdrawals.

**How to apply:** preserve these rules in wallet schema, balance conversion, bet amounts, payment routing defaults, persisted-route normalization, admin controls, history displays, and migration behavior.
