# Feature 03 — Platform Upgrade (Laravel / PHP / Backpack)

Milestone **P0.2** in `_brand/new_design/PLAN.md` (§10 "Platform currency", §11, decision **Q21**).

## Summary

Move the app from **Laravel 8.29 / PHP 8.0 / Backpack 4.1** to versions that still receive security fixes:
a currently supported Laravel release, **PHP 8.3**, and the Backpack version that supports that Laravel.
The upgrade is done **one Laravel major at a time**, each step tested and shippable on its own.

**Gate (Q21):** `portal.mode = live` may not be set until this milestone is done. The beta at `/beta` can run
before it; promoting the new portal to all students cannot.

## Why

| Reason | Detail |
|---|---|
| Framework out of support | Laravel 8 stopped receiving security fixes in **January 2023**. We are on 8.29, not even the last 8.x release (8.83). |
| PHP out of support | PHP 8.0 reached end of life in **November 2023**: no security patches for the runtime. |
| Known vulnerabilities | `composer audit` reports **76 advisories in 20 packages** in the current lock file. |
| Base image end of life | `php:8.0-apache` is on Debian bullseye; the Docker build broke when its package mirrors were archived. The Dockerfile now points at `archive.debian.org` as a stop-gap. |
| Locked dependencies | mPDF 8.0.10 (via `carlos-meneses/laravel-mpdf` 2.1.4) requires PHP ≤ 8.0, so the PHP version cannot move without moving PDF generation too. Other packages are stuck on old lines (Sanctum 2, webpush 5, phpword 0.18). |
| Bigger attack surface | The new portal exposes an API, cookie auth, uploads and push to every student and teacher. A framework flaw there affects everyone. |
| Cloudflare is not a fix | The WAF and rate limits (P0.1) reduce noise; they cannot patch a vulnerability in the framework or a package. |

## Decisions

| Topic | Decision |
|---|---|
| When | Before `portal.mode = live` (Q21). Can run in parallel with the beta (P2.8). |
| Path | One Laravel major per step: 8 → 9 → 10 → 11 → 12 → current. Each step merged and deployed (or at least fully tested) before the next. |
| Target PHP | **8.3** (meets the minimum of current Laravel, supported upstream for longer than 8.2). |
| App skeleton | **Keep the Laravel 8 structure** (`app/Http/Kernel.php`, `app/Console/Kernel.php`, service providers). Laravel 11+ still supports it; moving to the slim `bootstrap/app.php` skeleton is optional and out of scope. |
| Tests first | No step is merged unless the PHPUnit suite (102 tests today) and the Vitest suite pass. |
| Branch | `feature/platform-upgrade`, one commit (or PR) per step. |

> Version numbers below are what the plan expects at the time of writing. Check each package's upgrade guide and
> its supported Laravel/PHP range before starting a step.

## Current State

Runtime: Laravel **v8.29.0**, PHP **8.0** (container `php:8.0-apache`, Debian bullseye). Production: Hostinger
KVM 2 VPS, Ubuntu + Webmin (Q20) — the PHP version installed there must be checked.

Direct dependencies (`composer.lock`):

| Package | Version | Upgrade note |
|---|---|---|
| laravel/framework | v8.29.0 | 8 → 9 → 10 → 11 → 12 → current |
| backpack/crud | 4.1.36 | 4.1 → 5 → 6 (each has an upgrade guide; admin UI changes) |
| backpack/permissionmanager | 6.0.5 | Move with Backpack (wraps spatie/laravel-permission) |
| backpack/settings | 3.0.8 | Move with Backpack; holds `portal.mode` |
| backpack/backupmanager | v3.0.2 | Move with Backpack (spatie/laravel-backup underneath) |
| backpack/filemanager | 1.1.4 | Move with Backpack (elFinder) |
| backpack/logmanager | v4.0.2 | Move with Backpack |
| backpack/newscrud | v4.0.4 | Move with Backpack |
| backpack/pagemanager | 3.0.7 | Move with Backpack |
| carlos-meneses/laravel-mpdf | 2.1.4 (mpdf 8.0.10) | Upgrade to a release on mPDF 8.1+/8.2 for PHP 8.1+ |
| laravel/sanctum | v2.15.1 | 2 → 3 (Laravel 9/10) → 4 (Laravel 11+) |
| laravel-notification-channels/webpush | 5.1.1 | Newer line (minishlink/web-push 8+) for PHP 8.1+ |
| phpoffice/phpword | 0.18.3 | → 1.x (PHP 8.1+ support) |
| fideloper/proxy | 4.4.1 | **Remove** in Laravel 9: use `Illuminate\Http\Middleware\TrustProxies` |
| fruitcake/laravel-cors | v2.0.3 | **Remove** in Laravel 9: CORS is built in (`Illuminate\Http\Middleware\HandleCors`) |
| laravelcollective/html | v6.2.1 | Abandoned. Only used in `app/Helpers/FormBuilderHelper.php` (no Blade view uses `Form::`). Replace with plain HTML / Blade, or `spatie/laravel-html` |
| guzzlehttp/guzzle | 7.2.0 | Minor update |
| laravel/tinker | v2.6.0 | Minor update |
| Dev: facade/ignition | 2.5.13 | **Replace** with `spatie/laravel-ignition` (Laravel 9+) |
| Dev: nunomaduro/collision | v5.3.0 | Move with Laravel (6 → 7 → 8) |
| Dev: phpunit/phpunit | 9.5.2 | 9 → 10 → 11 (metadata in doc comments is deprecated; move to attributes) |
| Dev: laravel/sail, laravel/ui, fakerphp/faker, mockery | various | Move with Laravel |
| Dev: backpack/generators, laracasts/generators, laravel-shift/blueprint | various | Upgrade with Backpack, or remove if no longer used |

Also in `composer.json`: an unused VCS repository (`anasbakro/dompdf`) that forces GitHub auth on every install —
remove it.

Code written for the portal that touches the upgrade:

| File | What to check |
|---|---|
| `app/Http/Middleware/TrustProxies.php` | Extends `Fideloper\Proxy\TrustProxies`; switch the base class to `Illuminate\Http\Middleware\TrustProxies`, keep the `TRUSTED_PROXIES` / `cloudflare` keyword logic. `tests/Feature/TrustProxiesTest.php` covers it. |
| `app/Http/Kernel.php` | Custom `$middlewarePriority` (`PortalMode` before auth), aliases `portal.mode`, `portal.role`; CORS middleware class name changes in 9. |
| `config/sanctum.php` | `guard => ['student', 'web']`; compare with the Sanctum 3/4 published config. |
| `app/Providers/RouteServiceProvider.php` | Rate limiters (`api`, `legacy-student-login`, `student-photo`, `portal`, `student-login`) with `Limit::response`. |
| `config/webpush.php` + migrations | Compare with the newer package's config and migration. |
| `Dockerfile` | `FROM php:8.0-apache` + `archive.debian.org` rewrite; GD built with JPEG/WebP/FreeType. |

## Plan

### Step 0 — Preparation
- Full database backup and a tested restore (also needed for P2.9).
- Branch `feature/platform-upgrade` from the current main.
- Baseline: run PHPUnit + Vitest, record results. Fix or delete the stock `tests/Feature/ExampleTest` (fails today).
- Capture regression baselines (see "Regression checks"): `student_attend_list` HTML for all classrooms (the method
  used in P2.5), a sample of generated PDFs (reports, certificates), and a list of admin CRUD pages that load.
- Remove the unused `anasbakro/dompdf` VCS repository from `composer.json`.
- Update to the last Laravel 8 release (8.83) and run the tests. Small, low-risk first step.

### Step 1 — PHP 8.1 and PDF generation
- Container to `php:8.1-apache` (Debian bookworm image; drop the `archive.debian.org` rewrite).
- Upgrade `carlos-meneses/laravel-mpdf` / mPDF to a release that runs on PHP 8.1+.
- Compare generated PDFs with the baseline (Arabic text shaping, fonts, page breaks).
- `phpoffice/phpword` → 1.x if a Word export breaks.

### Step 2 — Laravel 9
- Follow the official 8 → 9 upgrade guide.
- Remove `fideloper/proxy` (rebase `TrustProxies` on the framework class) and `fruitcake/laravel-cors`
  (use the built-in `HandleCors`).
- `facade/ignition` → `spatie/laravel-ignition`; collision 6.
- Sanctum 2 → 3.
- Replace `laravelcollective/html` in `FormBuilderHelper.php`.
- **Backpack 4.1 → 5** in the same step (Backpack 5 supports Laravel 8 and 9, so it can also be done just before).
  Follow its upgrade guide; add-ons move with it.
- Flysystem 1 → 3 comes with Laravel 9: check uploads (student photos, file manager, backups).

### Step 3 — Laravel 10 and PHP 8.2
- Container to PHP 8.2.
- Laravel 10 upgrade guide (native return types in the skeleton are optional).
- **Backpack 5 → 6** (supports Laravel 10+). This is the biggest admin change: re-test every CRUD, custom field
  and custom view.
- PHPUnit 10.

### Step 4 — Laravel 11, then 12
- Laravel 11 needs PHP 8.2+. Keep the existing skeleton.
- Sanctum 3 → 4; webpush package to its current line.
- Laravel 12 is a small release on top of 11.

### Step 5 — Current Laravel and PHP 8.3
- Container to PHP 8.3; upgrade to the current Laravel release if it is newer than 12.
- Final `composer audit`: **0 advisories** (or each remaining one written down with a reason).

### Step 6 — Production
- Install PHP 8.3 (+ extensions: gd with jpeg/webp/freetype, intl, mbstring, zip, pdo_mysql, …) on the VPS
  alongside the old version.
- Deploy in a maintenance window: backup → deploy → `migrate` → clear caches → switch the web server's PHP-FPM
  pool → smoke test → keep the old PHP version available for a quick rollback.
- Restart queue workers (push notifications) after the switch.

## Regression checks (every step)

- PHPUnit (API v1, attendance, portal mode, trust proxies) and Vitest pass.
- `student_attend_list` HTML identical to the baseline for every classroom.
- PDFs: attendance reports, certificates, other exports open and match the baseline visually (Arabic!).
- Admin: every Backpack CRUD list/create/edit page loads; permissions (roles) still restrict; settings page saves
  `portal.mode`; backup manager creates a backup; file manager opens.
- Old Blade portal: `/login`, `/dashboard` work for a student.
- New portal: `/beta` login (student + teacher), teacher roster save offline/online, messages, push subscribe.
- `php artisan route:list`, `config:cache`, `route:cache`, `view:cache` succeed.

## Effort

Rough estimate: **2–4 weeks** of developer time, most of it in the Backpack 4.1 → 6 move and the admin regression
pass. Laravel steps 9 → 12 are mostly mechanical once the tests are green.

## Risks

| Risk | Mitigation |
|---|---|
| Backpack custom fields/views break across 4.1 → 5 → 6 | Upgrade guides per version; click through every CRUD each step |
| mPDF upgrade changes Arabic PDF layout | Compare against baseline PDFs before merging Step 1 |
| Backpack licence: some features may need a paid licence in newer versions | Check which features the admin uses before Step 3 (open question) |
| Production PHP switch fails | Old PHP kept installed; FPM pool switch is reversible; backup taken before deploy |
| Unused dev generators block the upgrade | Remove them instead of upgrading |

## Open Questions

- Which PHP version is installed on the production VPS today, and can several versions run side by side?
- Does the admin use any Backpack features that are PRO/paid in Backpack 5/6?
- Are `laracasts/generators`, `laravel-shift/blueprint` and `backpack/generators` still used? (If not, remove.)
- Deploy each Laravel step to production, or only the final result? (Recommended: at least after Step 2 and the
  final step, to keep each production change small.)

## Acceptance Criteria

- Laravel and PHP on versions that receive security fixes; Backpack on the version that supports them.
- `composer audit` clean (or every remaining advisory documented).
- All regression checks above pass on the final version.
- Dockerfile on a supported base image without the `archive.debian.org` workaround.
- Production running the new versions; rollback path tested.
- `PLAN.md` P0.2 marked done → `portal.mode = live` unblocked (together with P0.1 and the P2.9 gates).
