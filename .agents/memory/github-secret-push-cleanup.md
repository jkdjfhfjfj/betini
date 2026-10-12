---
name: GitHub secret-push cleanup
description: Handling GitHub secret-scanning blocks when detected values exist in committed history.
---

GitHub can reject a push after the current files are clean because older commits being pushed still contain detected values.

**Why:** Imported SDK examples and SQL seed data can include provider test credentials, and history rewrites change commit IDs. Local cleanup and remote updates are separate operations.

**How to apply:** Replace detected values with environment-based configuration or placeholders, scan all local refs and commit trees, and only rewrite history after explicit user approval. Remove backup refs and prune unreachable objects only when the approved scope requires complete local removal. Never push or force-push without separate authorization.
