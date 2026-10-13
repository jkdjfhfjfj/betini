---
name: GitHub secret-push cleanup
description: Handling GitHub secret-scanning blocks when detected values exist in committed history.
---

GitHub can reject a push after the current files are clean because older commits being pushed still contain detected values.

**Why:** Imported SDK examples and SQL seed data can include provider test credentials, and history rewrites change commit IDs. Local cleanup and remote updates are separate operations.

**How to apply:** Replace detected values with environment-based configuration or placeholders, scan all local refs and commit trees, and only rewrite history after explicit user approval. Remove backup refs and prune unreachable objects only when the approved scope requires complete local removal. Never push or force-push without separate authorization.

For a shell Git push rejected with “Invalid username or token,” use Replit’s Git provider settings rather than putting a token in chat or storing it in the Git remote URL. A healthy/added GitHub connector does not necessarily repair the workspace shell credential. Replit’s documentation directs users to disconnect and reconnect GitHub under account settings → **Git Providers**.

**Why:** GitHub API connector authorization and the workspace shell Git credential are separate; an attached, healthy connector or a `GIT_URL` secret does not guarantee that shell `git push` will authenticate.

**How to apply:** Disconnect and reconnect GitHub under Replit account settings → **Git Providers**, then retry only a normal fast-forward push. Do not use force-push to work around authentication failures.
