---
name: FTP asset parity
description: Diagnose UI differences between the workspace and an FTP-hosted storefront.
---

The FTP site can serve newer page markup alongside an older CSS asset, so icons may exist in the HTML while their layout and responsive rules differ from the workspace.

**Why:** A live page contained the current header icon markup, but its stylesheet lacked the workspace's mobile header rules.

**How to apply:** Compare the deployed CSS URL/content with the workspace file before duplicating or rewriting UI styles; deploy the asset when the source is already correct.
