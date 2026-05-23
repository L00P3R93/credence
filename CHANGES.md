# Security Changes Log

All timestamps are UTC. Roll-back instructions are per-entry.

---

## Phase 4 — Maintenance Mode (2026-05-23)

---

### [2026-05-23] NEW FILE — `resources/views/errors/503.blade.php`

**File:** `resources/views/errors/503.blade.php`  
**Change:** Custom branded 503 maintenance page. Minimal design — no tech stack references (no framework name, no version info, no stack traces). Uses inline CSS with Instrument Sans font from bunny.net CDN. Includes `<meta http-equiv="refresh" content="30">` to auto-refresh every 30 seconds.  
**Rollback:** Delete the file.

---

### [2026-05-23] Maintenance mode activated

**Command run on server:**
```bash
php artisan down \
  --secret="[REDACTED — see .env or ops runbook]" \
  --render="errors::503" \
  --retry=60 \
  --refresh=30
```

**Bypass URL:** `https://DOMAIN/[secret]` — sets a bypass cookie; the browser then passes through maintenance mode for the remainder of the session.  
**Bring back up:** `php artisan up`  
**Driver:** `file` (default) — down state stored in `storage/framework/down`. If running multiple servers, set `MAINTENANCE_DRIVER=cache` in `.env` so all nodes share the same state.

**Security caveat:** The bypass secret appears in the URL path, which is logged by Apache/nginx in `access.log`. Anyone with access to the web server logs can see it. Mitigations:
- Rotate the secret after use (`php artisan up && php artisan down --secret="new-secret" ...`)
- Clear or rotate access logs after the maintenance window
- Restrict access log permissions to root/www-data only

**Rollback:** `php artisan up`

---

## Phase 3 — Bot / Crawler / Scraper Blocking (2026-05-23)

---

### [2026-05-23] Layer 1 — `public/robots.txt` updated

**File:** `public/robots.txt`  
**Change:** Was `User-agent: * / Disallow:` (allowed everything). Now disallows `/admin`, `/log-viewer`, `/storage`, `/api`, `/admin/login`. Fully blocks AI training crawlers: GPTBot, ClaudeBot, CCBot, Google-Extended, Amazonbot, Bytespider, FacebookBot, PerplexityBot. Adds `Crawl-delay: 10`.  
**Rollback:** Revert to `User-agent: *\nDisallow:`.  
**Note:** `robots.txt` is advisory only — malicious bots ignore it. It is Layer 1 of a 6-layer defense.

---

### [2026-05-23] Layer 2 — `app/Http/Middleware/BlockBots.php` (new)

**File:** `app/Http/Middleware/BlockBots.php`  
**Change:** New middleware registered globally in `bootstrap/app.php`. Blocks:
- Empty / missing `User-Agent`
- 25 known scanner / scraper UA patterns (python-requests, sqlmap, nikto, etc.)
- Stale browser UAs: Chrome < 100, Firefox < 110 (nearly always spoofed by scanners)

Returns HTTP 403 plain text (no Laravel error page). Logs to `storage/logs/blocked.log` via the `blocked` channel. Gated by `SECURITY_BLOCK_BOTS` env flag.  
Exempt paths: `livewire/*`, `up` (health check).  
**Rollback:** Set `SECURITY_BLOCK_BOTS=false` in `.env`, or remove `BlockBots::class` from `bootstrap/app.php`.

---

### [2026-05-23] Layer 3 — `app/Http/Middleware/IpBlocklist.php` (new)

**File:** `app/Http/Middleware/IpBlocklist.php`  
**Change:** New middleware registered globally. Checks two lists:
1. **Static config list** (`config/security.php` → `blocked_ips`): 11 confirmed bot IPs seeded from Phase 1 session logs. Supports CIDR ranges via `Symfony\Component\HttpFoundation\IpUtils::checkIp`.
2. **Dynamic cache list** (`temp_blocklist:{IP}`): populated by honeypot routes (24h TTL).

Returns HTTP 403 with empty body. Logs to `blocked` channel. Exempt: `/up`.  
**Rollback:** Set `SECURITY_BLOCKED_IPS=` (empty) in `.env`, or remove `IpBlocklist::class` from `bootstrap/app.php`.  
**To add IPs at runtime:** `SECURITY_BLOCKED_IPS=1.2.3.4,10.0.0.0/8` in `.env` (overrides the default seed list).

---

### [2026-05-23] Layer 4 — Rate limiting (applied to web group)

**Files:** `bootstrap/app.php`, `app/Providers/AppServiceProvider.php`  
**Change:** `throttle:global` (60 req/min per IP) applied to web middleware group. `auth` (5/min) and `api` (30/min) limiters defined and ready. Filament's built-in login throttle (5 attempts per IP + 5 per IP+email) is already active in `Filament\Auth\Pages\Login`.  
**Rollback:** Remove `$middleware->web(append: ['throttle:global'])` from `bootstrap/app.php`.

---

### [2026-05-23] Layer 5 — Web server snippets (provided, not auto-deployed)

**Files created:**
- `infra/block-bots-nginx.conf` — nginx `map` + `geo` blocks for UA and IP filtering; `444` drops connection
- `infra/block-bots-apache.conf` — Apache `mod_rewrite` rules for UA and IP filtering; `[F]` returns 403

**Not deployed.** To deploy (Apache):
```bash
# Review the file, then:
apachectl configtest
# If clean, include it in your VirtualHost config and:
systemctl reload apache2
```
To deploy (nginx):
```bash
nginx -t
systemctl reload nginx
```
**Rollback:** Remove the `Include` line from VirtualHost / server block and reload.

---

### [2026-05-23] Layer 6 — Honeypot routes (new)

**File:** `routes/web.php`  
**Change:** 11 honeypot routes registered for paths no legitimate user visits:
`.env`, `.git/config`, `.git/HEAD`, `wp-login.php`, `wp-admin`, `wp-admin/admin-ajax.php`, `phpmyadmin`, `phpmyadmin/index.php`, `administrator`, `administrator/index.php`, `admin/config.php`.

Each hit:
1. Logs `[HONEYPOT]` + IP + UA to `storage/logs/blocked.log`
2. Sets `Cache::put("temp_blocklist:{IP}", true, 24h)` — picked up by `IpBlocklist` on the next request
3. Returns HTTP 200 empty HTML (scanner thinks it found a target, wastes time on follow-up probes)

**Rollback:** Remove the `foreach` block from `routes/web.php`.

---

### [2026-05-23] `config/logging.php` — `blocked` channel added

**File:** `config/logging.php`  
**Change:** Added `blocked` channel: `single` driver writing to `storage/logs/blocked.log`.  
**Rollback:** Remove the `blocked` channel entry.

---

### [2026-05-23] `bootstrap/app.php` — middleware order fixed

**File:** `bootstrap/app.php`  
**Change:** `SecurityHeaders` changed from `append` → `prepend` so it wraps the full middleware stack. This ensures security headers are added to **all** responses, including early 403 exits from `IpBlocklist` and `BlockBots`. `IpBlocklist` and `BlockBots` appended in that order (IP check before UA check).  
**Rollback:** Swap `prepend` back to `append`; remove `IpBlocklist` and `BlockBots` lines.

---

## Phase 3 Test Plan

Run these on the production server after deploying. Replace `DOMAIN` with the actual hostname.

```bash
# Should be blocked 403 — bad UA
curl -i -A "python-requests/2.32.5" https://DOMAIN/

# Should be blocked 403 — empty UA
curl -i -A "" https://DOMAIN/

# Should be blocked 403 — stale Chrome UA (Chrome 85)
curl -i -A "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36" https://DOMAIN/

# Should pass — modern UA, redirect to /admin/login
curl -i -A "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36" https://DOMAIN/

# Honeypot — should return 200, then log the IP and block it
curl -i https://DOMAIN/wp-login.php

# Verify the IP was added to temp_blocklist and is now blocked
curl -i https://DOMAIN/

# Check the blocked log
tail -20 storage/logs/blocked.log
```

---

## Phase 2 — Security Hardening (2026-05-23)

---

### [2026-05-23] ENV — APP_DEBUG disabled, APP_ENV set to production

**File:** `.env`  
**Change:** `APP_DEBUG=false`, `APP_ENV=production`  
**Why:** Debug mode was `true` — any unhandled exception would leak stack traces, environment variable values (including DB credentials), and query logs to the browser.  
**Rollback:** Set `APP_DEBUG=true`, `APP_ENV=local` in `.env`. Never do this on a live server.

---

### [2026-05-23] ENV — LOG_LEVEL changed from debug to error

**File:** `.env`  
**Change:** `LOG_LEVEL=debug` → `LOG_LEVEL=error`  
**Why:** `debug` level logs every DB query, every cache hit, request payloads. In production this fills disk and leaks internals.  
**Rollback:** Set `LOG_LEVEL=debug` in `.env`.

---

### [2026-05-23] ENV — Session cookies hardened

**File:** `.env`  
**Changes:**
- `SESSION_ENCRYPT=true` — payload encrypted at rest in the `sessions` DB table
- `SESSION_SECURE_COOKIE=true` — cookie only sent over HTTPS
- `SESSION_HTTP_ONLY=true` — cookie inaccessible to JavaScript (was default but now explicit)
- `SESSION_SAME_SITE=lax` — CSRF protection via SameSite (was default but now explicit)

**Why:** Session data was stored unencrypted. `secure` flag was unset, meaning the session cookie could be sent over HTTP and intercepted.  
**Rollback:** Remove the four `SESSION_*` lines added, or set `SESSION_ENCRYPT=false`, `SESSION_SECURE_COOKIE=false`.  
**Note:** Changing `SESSION_ENCRYPT` invalidates all existing sessions — all users will be logged out.

---

### [2026-05-23] NEW FILE — `config/security.php`

**File:** `config/security.php`  
**Change:** New file. Central feature flags for all security middleware: `headers_enabled`, `block_bots`, `blocked_ips`, `trusted_proxies`.  
**Why:** Every middleware needs a kill switch for debugging without a full deploy. Keeps security config in one place.  
**Rollback:** Delete the file. Remove any `config('security.*')` references from middleware.

---

### [2026-05-23] NEW FILE — `app/Http/Middleware/SecurityHeaders.php`

**File:** `app/Http/Middleware/SecurityHeaders.php`  
**Change:** New middleware, registered globally in `bootstrap/app.php`. Adds:
- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` (HTTPS only)
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Content-Security-Policy-Report-Only` (report-only — tune before enforcing)

**Why:** None of these headers were set. Missing HSTS means browsers won't upgrade HTTP→HTTPS automatically. Missing X-Frame-Options allows clickjacking.  
**Rollback:** Set `SECURITY_HEADERS_ENABLED=false` in `.env` (uses the kill switch in the middleware). Or remove `SecurityHeaders::class` from `bootstrap/app.php`.  
**Note:** CSP is in `Report-Only` mode — it logs violations but does not block. Once Filament's inline scripts/styles are accounted for in the policy, switch to `Content-Security-Policy`.

---

### [2026-05-23] MODIFIED — `bootstrap/app.php`

**File:** `bootstrap/app.php`  
**Changes:**
- Added `$middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'))` — required for correct IP detection behind Cloudflare / load balancer
- Added `$middleware->append(SecurityHeaders::class)` — applies security headers globally
- Added `$middleware->web(append: ['throttle:global'])` — 60 req/min per IP on all web routes

**Why:** Without `trustProxies`, Laravel reads the load-balancer IP as the client IP, breaking rate limiting and geo-blocking. Without the throttle, the web routes had no rate limiting at all.  
**Rollback:** Remove the three added lines and the `use App\Http\Middleware\SecurityHeaders;` import.  
**Note:** `TRUSTED_PROXIES=*` trusts all proxies. Tighten to actual Cloudflare CIDR ranges in `.env` before go-live:
```
TRUSTED_PROXIES=173.245.48.0/20,103.21.244.0/22,103.22.200.0/22,...
```

---

### [2026-05-23] MODIFIED — `app/Providers/AppServiceProvider.php`

**File:** `app/Providers/AppServiceProvider.php`  
**Change:** Added `configureRateLimiters()` — defines `global` (60/min), `auth` (5/min), and `api` (30/min or 10/min for guests) named rate limiters.  
**Why:** The `global` limiter is consumed by `throttle:global` on the web group. `auth` and `api` limiters are pre-defined for Phase 3 and future API routes.  
**Rollback:** Remove the `configureRateLimiters()` call and method. Remove the three `use` imports for `Limit`, `RateLimiter`, and `Request`.

---

### [2026-05-23] BUGFIX — `app/Models/User.php` — `canAccessPanel`

**File:** `app/Models/User.php`  
**Change:** 
```php
// Before (broken — always returns truthy model instance):
return ($this->status === UserStatus::Active) && $this->with('roles');

// After (correct — checks role assignment exists):
return $this->status === UserStatus::Active && $this->roles()->exists();
```
**Why:** `$this->with('roles')` returns the Eloquent model instance, which is always truthy. Any user with `status=active` could access the Filament admin panel regardless of role assignment. The fix queries the `model_has_roles` pivot table.  
**Rollback:** Revert the one-line change. (Do not revert — this is a security fix.)  
**Impact:** Any active user currently in the system without a role will be locked out of the panel. Check `users` table against `model_has_roles`.

---

### [2026-05-23] MODIFIED — `routes/console.php`

**File:** `routes/console.php`  
**Change:** Added daily scheduled closure to prune expired database sessions.  
**Why:** Laravel's built-in session GC is probabilistic (2/100 requests). Under low traffic it may never run, accumulating stale sessions in the DB. A scheduled delete is reliable.  
**Rollback:** Remove the `Schedule::call(...)` block.  
**Note:** Ensure the Laravel scheduler is running on the server: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

---

### [2026-05-23] DEPENDENCY — Filament upgraded 4.8.0 → 4.11.5

**Packages updated:** All `filament/*` packages + transitive dependencies (59 total)  
**CVE patched:** CVE-2026-33080 — XSS via unvalidated Range/Values summarizer in `filament/tables`  
**Rollback:** `composer require "filament/filament:4.8.0" --update-with-dependencies`  
**Note:** `composer.lock` updated — deploy the updated lock file to production.

---

### [2026-05-23] DEPENDENCY — Full composer update (all packages)

**Why:** Resolved remaining 14 advisories in symfony/yaml (CVE-2026-45133/45304/45305), league/commonmark (CVE-2026-30838/33347), phpoffice/phpspreadsheet CVEs.  
**Result:** `composer audit` — **No security vulnerability advisories found.**  
**Rollback:** `git checkout composer.lock && composer install`

---

### [2026-05-23] DEPENDENCY — npm audit fix

**Packages fixed:** axios, rollup, vite, picomatch, postcss, follow-redirects  
**Result:** `npm audit` — **found 0 vulnerabilities**  
**Rollback:** `git checkout package-lock.json && npm ci`

---

## Phase 2 Remaining Recommendations (not auto-implemented)

- **2FA:** Install `filament/filament` 2FA provider or `laragear/two-factor` for admin accounts. Propose install in Phase 3 review.
- **Tighten `TRUSTED_PROXIES`:** Replace `*` with actual Cloudflare IPv4/IPv6 ranges once confirmed.
- **`APP_URL`:** Update from `https://credence.test` to the real production domain.
- **CSP enforcement:** After monitoring `Content-Security-Policy-Report-Only` violations for 1–2 weeks, switch to `Content-Security-Policy`.
- **Default admin password:** The seeded Super Admin password follows `{FirstName}@{Phone}` pattern — rotate in production immediately.
