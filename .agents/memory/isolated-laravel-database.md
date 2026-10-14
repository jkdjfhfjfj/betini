---
name: Isolated Laravel database checks
description: Keep local Laravel checks from reaching a configured external database.
---

When forcing Laravel to use a temporary SQLite database, set `DATABASE_URL` to an empty value as well as setting `DB_CONNECTION=sqlite` and a temporary `DB_DATABASE` path. Laravel's SQLite connection can still use `DATABASE_URL` when it is present.

**Why:** an external database URL can override the SQLite connection choice and cause local migration commands to modify the configured database.

**How to apply:** use an explicitly isolated database URL for every local migration or web-preview command; never run migrations until the effective connection has been verified.

A temporary SQLite database can report the checked-in historical migrations as applied while still lacking the application's pre-existing schema. The remaining legacy migration assumes tables such as `funds` already exist, and this checkout has no complete base-schema dump. Do not invent production tables from partial model data to make the preview load.

**Why:** completing guessed schemas can hide real deployment differences and make a test appear valid when it is not.

**How to apply:** inspect the SQLite tables and available schema files; if base tables are missing, keep testing routes that do not require them and request a sanitized schema export before attempting a full database-backed preview.

Remote fixture QA uses a separate staging-only MySQL connection; never replace the local preview's SQLite settings or the app's normal database settings. Generated dates and odds are synthetic QA data and must stay isolated from production.

**Why:** the user identified the remote MySQL target as isolated testing, while the app preview remains SQLite; scheduled match-status processing can expire short test windows.

**How to apply:** connect only through staging-specific secrets, verify the selected database before writing, batch fixture inserts transactionally, and give staged live events an end-time margin long enough for the host's timezone and status scheduler.
