---
name: Isolated Laravel database checks
description: Keep local Laravel checks from reaching a configured external database.
---

When forcing Laravel to use a temporary SQLite database, set `DATABASE_URL` to an empty value as well as setting `DB_CONNECTION=sqlite` and a temporary `DB_DATABASE` path. Laravel's SQLite connection can still use `DATABASE_URL` when it is present.

**Why:** an external database URL can override the SQLite connection choice and cause local migration commands to modify the configured database.

**How to apply:** use an explicitly isolated database URL for every local migration or web-preview command; never run migrations until the effective connection has been verified.
