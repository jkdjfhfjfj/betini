---
name: PayzaAPI integration contract
description: Hosted checkout, webhook authentication, and payout-status behavior for this wallet.
---

PayzaAPI payment creation uses `POST /api/v1/pay` and returns `data.payment_url`; use that hosted checkout URL for browser payment. When PayzaAPI is chosen for KES checkout, send `stk_push: false` to avoid starting an M-Pesa prompt alongside hosted checkout.

Authenticate API calls with `X-Public-Key` and `X-Secret-Key`. Authenticate webhooks with the separate account Webhook Signing Secret: HMAC-SHA256 over the exact raw request body, hex encoded in `X-Payza-Signature`. Acknowledge quickly; use the signed payment event's status, amount, and currency for immediate processing. Keep `GET /api/v1/verify/{reference}` for return-page or polling reconciliation.

The PayzaAPI docs checked on 2026-10-13 list payment webhook events but do not document payout webhook events. Reconcile payouts using the documented `GET /api/v1/payouts?reference=...` status endpoint unless Payza confirms a payout-webhook contract.

**Why:** The user specified PayzaAPI for wallet payments and highlighted its hosted checkout and signed webhook contract. The published docs also require quick webhook acknowledgements and do not define payout webhook event names.

**How to apply:** Check the current PayzaAPI docs before changing payment endpoints, request/response fields, signature handling, or payout reconciliation; do not assume undocumented payout events are authoritative.
