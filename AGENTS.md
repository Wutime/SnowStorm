# AGENTS.md

## Scope
These instructions apply to the XenForo addon at:
`src/addons/Wutime/SnowStorm`

## Compatibility Contract (Non-Negotiable)
All changes must remain:
- MySQL 5.6 friendly
- PHP 7.4+ friendly
- XenForo 2.2 and 2.3 friendly

If there is any tradeoff, prefer compatibility with the oldest target first:
1. MySQL 5.6
2. PHP 7.4
3. XF 2.2

## XF 2.2 Test Environment
- XF 2.2 instance URL: `http://xf22.localhost/`
- XF 2.2 filesystem path: `/opt/homebrew/var/www/xf22`
- Primary dev/build workspace remains XF 2.2
- For compatibility validation, build release artifacts from XF 2.3 and install/upgrade them on XF 2.2 for runtime testing.
- For template-modification review, it is acceptable to compare XF 2.2 and XF 2.3 templates directly without doing a full install cycle when only inspecting differences.

## MySQL 5.6 Rules
- Do not use SQL features introduced after MySQL 5.6 (CTEs, window functions, JSON column type/functions, generated columns).
- Keep indexes safe for `utf8mb4` on older InnoDB limits (use 191-length indexes where applicable).
- Avoid schema patterns with weak 5.6 support (e.g. relying on CHECK constraints or advanced ALTER behavior).
- Prefer simple, portable query-builder patterns over raw SQL that depends on newer server behavior.
- Keep migrations idempotent and conservative; assume shared-host MySQL configs.

## PHP 7.4+ Rules
- Do not use PHP 8+ only syntax/features.
- Disallowed examples: constructor property promotion, union/intersection types, attributes, `match`, named arguments, nullsafe operator, enums, readonly properties/classes.
- Avoid PHP 8-only library functions unless explicitly polyfilled.
- Keep code compatible with strict error reporting on PHP 7.4.

## XenForo 2.2 + 2.3 Rules
- Do not require XF 2.3-only APIs without a compatibility guard.
- When behavior differs by version, gate logic with safe checks (e.g. `\XF::$versionId` and/or `method_exists`).
- Keep template/code event usage compatible with both branches.
- Prefer stable, long-standing XF extension points over newly added abstractions.

## Addon-Specific Workflow
- `php cmd.php` is approved and preferred for XenForo addon requirements/tasks in this project.
- Edit source JS files in `_files/js/...` and regenerate `.min.js` via the existing build process; do not hand-edit minified files.
- Keep `addon.json`, install/upgrade logic, and option/template changes synchronized.
- When changing install/upgrade schema, review for both fresh install and upgrade path safety.
- Keep XenForo DB state aligned with project files at all times. If phrases, templates, template modifications, or class extensions are edited directly, immediately run the matching `php cmd.php xf-dev:*` import/export workflow so DB and files do not drift.
- Treat DB/file drift as a release blocker; never ship with unresolved differences between XenForo DB state and addon files.

## Repository Policy
- Keep `_data/` gitignored (best-practice local/dev artifact handling for this addon workflow).

## Validation Checklist (Before Release)
- Syntax-check changed PHP files on PHP 7.4.
- Rebuild addon artifacts and confirm no unexpected diffs.
- Smoke test guest warning/block flow, registration conversion path, and any touched integrations (including XFMG integration if affected).
- Verify behavior on XF 2.2 and XF 2.3 test instances before tagging a release.

## PR / Change Notes Expectations
When submitting changes, include:
- What changed
- Why it is compatible with MySQL 5.6, PHP 7.4+, XF 2.2/2.3
- Any manual verification performed

## Phrases and I18n (Required)
- Do not hardcode English UI text in templates, controllers, services, repositories, JS, or admin/public output.
- Use XenForo phrases for all user/admin-facing text.
- Add/update phrase definitions in `_output/phrases/*` and reference them via `phrase()`/template phrase syntax.
- Treat hardcoded English as a release blocker unless the string is an internal-only debug token not shown to users/admins.

## Version ID Synchronization (Required)
- Treat `addon.json` `version_id` as the single source of truth for addon asset/version metadata.
- When manually creating or editing phrases, templates, template modifications, options, permissions, routes, style properties, or class extensions, ensure their `version_id`/`version_string` metadata matches the current `addon.json` values.
- Keep GUI-created assets, file metadata, and XenForo DB records aligned to the same current addon version before release.
- Do not ship mixed or stale `version_id` values; version mismatches are a release blocker because installer/upgrader asset update detection depends on them.
