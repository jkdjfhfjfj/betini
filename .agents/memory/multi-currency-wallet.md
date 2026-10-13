---
name: Native-currency wallet invariants
description: Product rules for wallet currency, conversion, legacy balances, and currency switching.
---

Each user has one active native-currency wallet. Registration defaults that currency from the user's country, and the user can change it later. Betting, available-balance display, deposits, and withdrawals should use the active wallet currency.

USD is the conversion base. Preserve balances that existed before this feature as USD; never silently reinterpret them as the user's local currency. A wallet currency change converts the active balance using current USD-based rates and is blocked while bets or payment transactions are unsettled.

**Why:** the user chose separate native-currency wallets while keeping existing balances intact as USD.

**How to apply:** follow these rules when changing wallet schema, balance conversion, bet amounts, payment routing, history displays, or migration behavior.
