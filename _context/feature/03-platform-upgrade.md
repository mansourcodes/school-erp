# Feature 03 — Platform & Infrastructure (deferred)

Three tracks taken out of the portal plan (`_brand/new_design/PLAN.md`) on **2026-10-13**:

| Part | Track | Was in PLAN.md |
|---|---|---|
| A | Laravel / PHP / Backpack upgrade | P0.2, §9.3 "Platform currency", Q21 |
| B | Cloudflare (DNS, TLS, WAF, edge rate limits, origin lock) + VPS hardening | P0.1, §6 "Edge", §9.3 "Admin panel", §10, Q19, Q20, Q22 |
| C | Production services: Redis, queue worker, scheduler cron, object storage | §9.2, §10.6, §5.11/§5.12 |

**Status: deferred.** None of these tracks gates `portal.mode = live` anymore. The portal plan continues without them.

## Decisions (2026-10-13)

| Topic | Decision |
|---|---|
| Gate for going live | **Removed.** Before, P0.1 (Cloudflare) and P0.2 (upgrade) had to be done before `portal.mode = live` (Q21). Now they are separate, later tracks. |
| Queue | **`QUEUE_CONNECTION=sync` in production.** No queue worker and no Redis for now (see Part C for what this means). |
| Scheduler cron | Not set up for now. Message pruning runs on the first admin visit to `/admin/dashboard` each day (see Part C). |
| Turnstile | **Stays in the portal plan** (login bot check, already in code). It is a Cloudflare product, but it does not need the domain on Cloudflare. |

**Accepted risk:** the portal can go live on Laravel 8 / PHP 8.0, which no longer get security fixes, and without
an edge layer in front of the origin. The login hardening in the app (rate limits, lockout, Turnstile, honeypot)
and the API policies still apply.

---

## Part A — Laravel / PHP / Backpack upgrade

### Summary

Move the app from **Laravel 8.29 / PHP 8.0 / Backpack 4.1** to versions that still receive security fixes:
a currently supported Laravel release, **PHP 8.3**, and the Backpack version that supports that Laravel.
The upgrade is done **one Laravel major at a time**, each step tested and shippable on its own.

### Why

| Reason | Detail |
|---|---|
| Framework out of support | Laravel 8 stopped receiving security fixes in **January 2023**. We are on 8.29, not even the last 8.x release (8.83). |
| PHP out of support | PHP 8.0 reached end of life in **November 2023**: no security patches for the runtime. |
| Known vulnerabilities | `composer audit` reports **76 advisories in 20 packages** in the current lock file. |
| Base image end of life | `php:8.0-apache` is on Debian bullseye; the Docker build broke when its package mirrors were archived. The Dockerfile now points at `archive.debian.org` as a stop-gap. |
| Locked dependencies | mPDF 8.0.10 (via `carlos-meneses/laravel-mpdf` 2.1.4) requires PHP ≤ 8.0, so the PHP version cannot move without moving PDF generation too. Other packages are stuck on old lines (Sanctum 2, webpush 5, phpword 0.18). |
| Bigger attack surface | The new portal exposes an API, cookie auth, uploads and push to every student and teacher. A framework flaw there affects everyone. |
| Cloudflare is not a fix | The WAF and rate limits (Part B) reduce noise; they cannot patch a vulnerability in the framework or a package. |

### Decisions

| Topic | Decision |
|---|---|
| When | Deferred (2026-10-13). Originally "before `portal.mode = live`" (Q21). |
| Path | One Laravel major per step: 8 → 9 → 10 → 11 → 12 → current. Each step merged and deployed (or at least fully tested) before the next. |
| Target PHP | **8.3** (meets the minimum of current Laravel, supported upstream for longer than 8.2). |
| App skeleton | **Keep the Laravel 8 structure** (`app/Http/Kernel.php`, `app/Console/Kernel.php`, service providers). Laravel 11+ still supports it; moving to the slim `bootstrap/app.php` skeleton is optional and out of scope. |
| Tests first | No step is merged unless the PHPUnit suite (102 tests today) and the Vitest suite pass. |
| Branch | `feature/platform-upgrade`, one commit (or PR) per step. |

> Version numbers below are what the plan expects at the time of writing. Check each package's upgrade guide and
> its supported Laravel/PHP range before starting a step.

### Current state

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

### Steps

**Step 0 — Preparation**
- Full database backup and a tested restore.
- Branch `feature/platform-upgrade` from the current main.
- Baseline: run PHPUnit + Vitest, record results. Fix or delete the stock `tests/Feature/ExampleTest` (fails today).
- Capture regression baselines (see "Regression checks"): `student_attend_list` HTML for all classrooms (the method
  used in P2.5), a sample of generated PDFs (reports, certificates), and a list of admin CRUD pages that load.
- Remove the unused `anasbakro/dompdf` VCS repository from `composer.json`.
- Update to the last Laravel 8 release (8.83) and run the tests. Small, low-risk first step.

**Step 1 — PHP 8.1 and PDF generation**
- Container to `php:8.1-apache` (Debian bookworm image; drop the `archive.debian.org` rewrite).
- Upgrade `carlos-meneses/laravel-mpdf` / mPDF to a release that runs on PHP 8.1+.
- Compare generated PDFs with the baseline (Arabic text shaping, fonts, page breaks).
- `phpoffice/phpword` → 1.x if a Word export breaks.

**Step 2 — Laravel 9**
- Follow the official 8 → 9 upgrade guide.
- Remove `fideloper/proxy` (rebase `TrustProxies` on the framework class) and `fruitcake/laravel-cors`
  (use the built-in `HandleCors`).
- `facade/ignition` → `spatie/laravel-ignition`; collision 6.
- Sanctum 2 → 3.
- Replace `laravelcollective/html` in `FormBuilderHelper.php`.
- **Backpack 4.1 → 5** in the same step (Backpack 5 supports Laravel 8 and 9, so it can also be done just before).
  Follow its upgrade guide; add-ons move with it.
- Flysystem 1 → 3 comes with Laravel 9: check uploads (student photos, file manager, backups).

**Step 3 — Laravel 10 and PHP 8.2**
- Container to PHP 8.2.
- Laravel 10 upgrade guide (native return types in the skeleton are optional).
- **Backpack 5 → 6** (supports Laravel 10+). This is the biggest admin change: re-test every CRUD, custom field
  and custom view.
- PHPUnit 10.

**Step 4 — Laravel 11, then 12**
- Laravel 11 needs PHP 8.2+. Keep the existing skeleton.
- Sanctum 3 → 4; webpush package to its current line.
- Laravel 12 is a small release on top of 11.

**Step 5 — Current Laravel and PHP 8.3**
- Container to PHP 8.3; upgrade to the current Laravel release if it is newer than 12.
- Final `composer audit`: **0 advisories** (or each remaining one written down with a reason).

**Step 6 — Production**
- Install PHP 8.3 (+ extensions: gd with jpeg/webp/freetype, intl, mbstring, zip, pdo_mysql, …) on the VPS
  alongside the old version.
- Deploy in a maintenance window (`portal.mode = maintenance` for the portal): backup → deploy → `migrate` → clear
  caches → switch the web server's PHP-FPM pool → smoke test → keep the old PHP version available for a quick
  rollback.
- Restart the queue worker after the switch, if Part C added one by then.

### Regression checks (every step)

- PHPUnit (API v1, attendance, portal mode, trust proxies) and Vitest pass.
- `student_attend_list` HTML identical to the baseline for every classroom.
- PDFs: attendance reports, certificates, other exports open and match the baseline visually (Arabic!).
- Admin: every Backpack CRUD list/create/edit page loads; permissions (roles) still restrict; settings page saves
  `portal.mode`; backup manager creates a backup; file manager opens.
- Old Blade portal: `/login`, `/dashboard` work for a student.
- New portal: login (student + teacher), teacher roster save offline/online, messages, push subscribe.
- `php artisan route:list`, `config:cache`, `route:cache`, `view:cache` succeed.

### Effort

Rough estimate: **2–4 weeks** of developer time, most of it in the Backpack 4.1 → 6 move and the admin regression
pass. Laravel steps 9 → 12 are mostly mechanical once the tests are green.

### Risks

| Risk | Mitigation |
|---|---|
| Backpack custom fields/views break across 4.1 → 5 → 6 | Upgrade guides per version; click through every CRUD each step |
| mPDF upgrade changes Arabic PDF layout | Compare against baseline PDFs before merging Step 1 |
| Backpack licence: some features may need a paid licence in newer versions | Check which features the admin uses before Step 3 (open question) |
| Production PHP switch fails | Old PHP kept installed; FPM pool switch is reversible; backup taken before deploy |
| Unused dev generators block the upgrade | Remove them instead of upgrading |

### Open questions

- Which PHP version is installed on the production VPS today, and can several versions run side by side?
- Does the admin use any Backpack features that are PRO/paid in Backpack 5/6?
- Are `laracasts/generators`, `laravel-shift/blueprint` and `backpack/generators` still used? (If not, remove.)
- Deploy each Laravel step to production, or only the final result? (Recommended: at least after Step 2 and the
  final step, to keep each production change small.)

### Acceptance criteria

- Laravel and PHP on versions that receive security fixes; Backpack on the version that supports them.
- `composer audit` clean (or every remaining advisory documented).
- All regression checks above pass on the final version.
- Dockerfile on a supported base image without the `archive.debian.org` workaround.
- Production running the new versions; rollback path tested.

---

## Part B — Cloudflare and VPS hardening

Goal: attacks (DDoS, bots, credential stuffing, scanners) are absorbed at Cloudflare's edge, and the origin server is
not reachable directly. It protects **both** the old portal and the new panel, and it needs no app release.

### Already in the code

- `TrustProxies` reads `TRUSTED_PROXIES` from `.env`: the `cloudflare` keyword expands to Cloudflare's IP ranges
  (`config('portal.cloudflare_ips')`), `127.0.0.1` covers a Tunnel. With the variable empty (today) no proxy is
  trusted. A spoofed `X-Forwarded-For` from outside the trusted proxies is ignored (tested).
- Turnstile is verified server-side and sends the real client IP as `remoteip`.

**Required when this track starts:** set `TRUSTED_PROXIES=cloudflare` (or `127.0.0.1` with a Tunnel) in the same
change that moves traffic behind Cloudflare. **Without it every request looks like it comes from Cloudflare**, and
the per-IP login rate limiter would lock out everyone at once or no one at all.

### B.1 Migration (low risk, reversible)
1. **Inventory DNS** at the current provider: A/AAAA for the app, MX/SPF/DKIM/DMARC for email, any other subdomains.
2. **Add the site in Cloudflare**, let it import the records, and check every record against the inventory.
3. **Proxy only web records** (orange cloud): the app host(s). **Mail records stay "DNS only"** (grey cloud).
4. Lower the TTLs a day ahead, then **switch the nameservers at the registrar** to Cloudflare's. Propagation takes
   hours. Keep the old DNS zone untouched as the rollback path.
5. Verify: the site loads over HTTPS via Cloudflare, `/admin` works, email still sends and arrives, Web Push still
   delivers (it goes browser ↔ push service, not through Cloudflare).

### B.2 TLS and the origin
- SSL/TLS mode **Full (strict)** with a **Cloudflare Origin Certificate** on the server. "Always Use HTTPS",
  minimum TLS 1.2, HSTS (after confirming everything is HTTPS).
- **Lock the origin:** the server firewall accepts HTTP(S) **only from Cloudflare's IP ranges**, or better, use a
  **Cloudflare Tunnel** (`cloudflared` as a systemd service), so the server has no open inbound web ports at all.
  Otherwise attackers can bypass Cloudflare by hitting the server IP directly.

### B.3 Protection rules
| Feature | Configuration |
|---|---|
| DDoS protection | On by default (L3/4 + HTTP). |
| WAF managed rules | Cloudflare managed ruleset (+ OWASP core ruleset where the plan allows), in **log mode for a week**, then block. |
| Rate limiting rules (edge) | `POST /login`, `POST /api/v1/auth/login`, `POST /admin/login`: e.g. 10 / minute per IP → challenge, then block. `/api/v1/*`: a generous per-IP ceiling. These sit **in front of** Laravel's own limiter, not instead of it. |
| Bots | Bot Fight Mode (or Super Bot Fight Mode on a paid plan). |
| Admin | No Cloudflare Access (Q22): Backpack login only, protected by the WAF + the edge rate limit; scanner paths blocked. |
| Geo / scanners | Optional: challenge traffic from countries the centre doesn't serve; block common scanner paths (`/.env`, `/wp-admin`, `/vendor/`). |
| "Under Attack" mode | Documented runbook: who turns it on, and when (sustained L7 attack). It adds an interstitial, so it is a short-term measure. |

### B.4 Caching rules (do not break the app)
| Path | Rule |
|---|---|
| `/beta/assets/*`, fonts, icons | Cache everything, long TTL (hashed file names) |
| `/beta/sw.js`, `/beta/manifest.webmanifest`, `/beta/index.html` | **Bypass cache** (no-cache), so PWA updates are never stuck |
| `/api/*`, `/sanctum/*`, `/login`, `/logout`, `/dashboard`, `/admin/*` | **Bypass cache**, since it is personal or session data |

### B.5 Plan, ownership and the VPS
- **Cloudflare plan: Free** (Q19): DNS, proxy, DDoS, the free managed ruleset and a limited number of rate-limiting
  rules. Use those rules on the login endpoints. Revisit Pro if attacks show up in the analytics.
- **Owner: the developer** for now (Q19). Turn on **2FA** on the registrar and Cloudflare accounts, store recovery
  codes safely, and add a centre account as a second Super Administrator.
- **Origin: Hostinger KVM 2 VPS, Ubuntu + Webmin** (Q20). ⚠ **Webmin** (port 10000) is a full root panel: never
  expose it publicly. Allow it only through an SSH tunnel or from a fixed admin IP, keep it updated, and use 2FA.
  SSH: keys only, no root password login, fail2ban. (These VPS items do not depend on Cloudflare and are cheap to
  do early.)

### Acceptance criteria
DNS moved, web records proxied, mail verified, Full (strict) + origin locked to Cloudflare / Tunnel, WAF in log →
block, edge rate limits on the three login endpoints, `TRUSTED_PROXIES` set in production, Webmin and SSH locked down.

---

## Part C — Production services (Redis, queue worker, scheduler, storage)

### Current decision: `QUEUE_CONNECTION=sync`, no worker, no cron

What that means in the running app:

| Area | Effect with `sync` / no cron |
|---|---|
| Push notifications | The queued notifications (`AttendanceRecordedPush`, `MessageReceivedPush`) run **inside the request**, after the save commits. A roster save or a message send makes one HTTP call to the push service per subscribed device, so it gets slower as more students enable notifications. A push failure never fails the save (the listeners catch and report it). |
| Message retention (Q15, 90 days) | `messages:prune` is scheduled daily in `app/Console/Kernel.php`, but without the `schedule:run` cron the schedule never fires. Instead, the **first admin visit to `/admin/dashboard` each day** runs `PruneOldMessages::dispatchSync()` (`App\Http\Controllers\Admin\AdminController`, once per day via `Cache::add`; a failure is reported and never breaks the dashboard). If no admin opens the dashboard, messages stay longer; `php artisan messages:prune` still works by hand. |
| Exam ZIP packaging | Runs inside the request as well (same as before the portal work). |
| Sessions / cache | Stay on `file` (one server only). |

Watch the roster-save time once push is in use. If it gets slow, Part C (at least a database queue + worker) is the
fix, and it needs no code change: only `.env` and a supervised process.

### Deferred work

| Area | Plan |
|---|---|
| Stateless app | Move `SESSION_DRIVER`, `CACHE_DRIVER` and `QUEUE_CONNECTION` from `file`/`sync` to **Redis** (already in `docker-compose`). Then more than one app server can run. |
| Background work | A **queue worker** (`php artisan queue:work`, systemd or supervisor) for push sends and exam ZIPs. Chunk push jobs (e.g. 100 recipients per job) so a "message to all" never blocks a request. A simpler first step: `QUEUE_CONNECTION=database` + one worker. |
| Scheduler | Cron entry `* * * * * php /path/to/artisan schedule:run` (as the web user), so `messages:prune` runs daily without depending on an admin visit. The dashboard trigger can then be removed; keeping it is harmless (a second run the same day finds nothing to prune). |
| Files | Student photos and exam files move from the local `public` disk to **S3-compatible object storage (e.g. Cloudflare R2)** once there is more than one app server. While there is one server they stay on the local disk. |
| Capacity | The k6 "4:00 م peak" test (portal plan §9.2) should be re-run after this change; with a worker it also checks the queue backlog (< 1 min). |

### Acceptance criteria
Worker running under a supervisor and restarted on deploy, cron installed, `messages:prune` seen in the logs daily,
pushes no longer sent inside the request, roster save p95 within the §9.2 target at peak.
