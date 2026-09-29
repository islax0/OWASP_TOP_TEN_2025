# Lab 1 — Secrets / Credentials Exposure

**Goal:** Find leaked secrets via robots.txt, HTML comments, backup file, and debug endpoint — then reuse them.

## Files

- Vulnerable: `/lab1-secrets/vulnerable/info.php` — displays SMTP/Stripe secrets
- Vulnerable: `/lab1-secrets/vulnerable/config.php` — hard-coded secrets
- Vulnerable: `/lab1-secrets/vulnerable/status.php` — env dump, no auth
- Vulnerable: `/lab1-secrets/vulnerable/.env` — real secrets in webroot
- Vulnerable (web root): `/robots.txt` — advertises hidden paths
- Secure: `/lab1-secrets/fixed/info_secure.php`, `config_secure.php`, `status_secure.php`, `robots.txt` (clean)

Login, dashboard, and logout come from the shared app (`/login.php`, `/dashboard.php`, `/logout.php`).

## Vulnerable Code

```php
// config.php — secrets baked into source (CWE-260, CWE-547)
define('SMTP_PASSWORD', 'Smtp$ecret2024!');
define('STRIPE_SECRET_KEY', 'sk_live_51H7...');

// info.php — displays secrets to any logged-in user
<?= SMTP_HOST ?> / <?= substr(STRIPE_SECRET_KEY, 0, 12) ?>

// status.php — env dump with zero auth (CWE-526)
echo json_encode(['_ENV' => $_ENV, 'env_APP_MASTER_KEY' => getenv('APP_MASTER_KEY')]);
```

## Attack

1. Login via the shared app as `user2` / `123456`
2. Open `info.php` and view source — find HTML comments:
   `TODO(mike): status.php` and `backup: config.php.bak`
3. Check `/robots.txt` (web root) — confirms `status.php`, `config.php.bak`, `.env`
4. Fetch each unauthenticated:
   - `status.php` → `APP_MASTER_KEY` + env dump
   - `config.php.bak` (ships with the lab, simulates a forgotten editor backup) → SMTP/Stripe/JWT secrets
   - `.env` → DB, SMTP, Stripe, JWT secrets

Note: opening `config.php` directly returns a blank page (HTTP 200, 0 bytes) — PHP executes the `define()`s and prints nothing. That is expected. Disclosure happens through the `.bak` copy, which the server sends as plain text.
5. Reuse secrets: SMTP takeover, Stripe key abuse, JWT forgery with `my-super-secret-jwt-key-123`

## Fix

```php
// config_secure.php — no secrets in code, fail closed
function env_or_fail(string $name): string {
    $v = getenv($name);
    if ($v === false || $v === '') { http_response_code(500); exit; }
    return $v;
}

// info_secure.php — displays no secrets
// status_secure.php — 404 in prod, login-only minimal output
if (!$debug) { http_response_code(404); exit; }
echo json_encode(['status' => 'ok']);
```

- `.env` lives OUTSIDE webroot, never committed; block `*.bak`/`.env`/`.git` via `.htaccess`
- Clean `robots.txt` (don't advertise hidden paths); rotate ALL leaked keys

## Setup

Main setup only (`init.php` visited once). No extra steps — `config.php.bak` ships with the lab.

- Vulnerable: `http://localhost/A022025-Security-Misconfiguration/lab1-secrets/vulnerable/info.php`
- Secure: `http://localhost/A022025-Security-Misconfiguration/lab1-secrets/fixed/info_secure.php`

## CWE

- CWE-260: Password in Configuration File
- CWE-547: Hard-coded Security Constants
- CWE-526: Exposure Through Environment Variables
