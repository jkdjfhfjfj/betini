---
name: GitHub secret-push cleanup
description: Handling GitHub secret-scanning blocks when detected values exist in committed history.
---

GitHub can reject a push after the current files are clean because older commits being pushed still contain detected values.

**Why:** Imported SDK examples and SQL seed data can include provider test credentials, and history rewrites change commit IDs. Local cleanup and remote updates are separate operations.

**How to apply:** Replace detected values with environment-based configuration or placeholders, scan all local refs and commit trees, and only rewrite history after explicit user approval. Remove backup refs and prune unreachable objects only when the approved scope requires complete local removal. Never push or force-push without separate authorization.

For a Git push rejected with “Invalid username or token,” use Replit’s GitHub connection flow rather than putting a token in chat or storing it in the Git remote URL. Replit’s GitHub connection is managed separately from the initial repository import; its documentation directs users to disconnect and reconnect GitHub under account **Connected Services** when authentication stops working.

**Why:** The connected GitHub connector and the workspace Git credential can be out of sync, so a successful connector setup or a `GIT_URL` secret does not guarantee that shell `git push` will authenticate.

**How to apply:** Reconnect GitHub through Replit **Connected Services**, then retry only a normal fast-forward push. Do not use force-push to work around authentication failures.
