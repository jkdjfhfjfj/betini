---
name: Makamesco B2C contract
description: Documented authentication, payload, status route, and safe handling for Kenyan M-Pesa B2C withdrawals.
---

Makamesco's public API documentation uses the `X-API-Key` header. B2C withdrawals use `POST /api/payments/b2c` with `phoneNumber`, `amount`, `remarks`, and `commandId` (for example `BusinessPayment`). Delivery status is polled from `GET /api/payments/b2c/status/{conversationId}`. A 403 from this documented request should be diagnosed through the provider's returned reason and account-level B2C access, not by guessing a different header or endpoint.

**Why:** changing a correct auth contract without evidence can expose credentials or break valid provider requests. The provider must enable the B2C capability and its account must be active; provider rejection details are useful, but credentials must never be included in user-visible errors or logs.

**How to apply:** before changing Makamesco request authentication, compare the client path, header, and payload with the current official docs. On 4xx responses, surface only a short sanitized provider message and ensure a rejected withdrawal releases its reserved wallet amount exactly once.
