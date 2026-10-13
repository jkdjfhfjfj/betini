---
name: Tracked Blade view caches
description: Avoid stale storefront templates when working with this repository's committed compiled views.
---

Compiled Blade files are tracked in this repository. Restoring them after `php artisan view:clear` gives them fresh timestamps, which can make Laravel treat an old compiled view as current and render outdated markup.

**Why:** During UI verification, the updated auth layout compiled correctly, but a request still showed the old header until the Blade source timestamp was newer than the restored compiled file.

**How to apply:** Compile Blade views to check syntax. If restoring committed files under `storage/framework/views`, make changed Blade sources newer than those restored files before previewing. Keep generated compiled views out of feature commits.
