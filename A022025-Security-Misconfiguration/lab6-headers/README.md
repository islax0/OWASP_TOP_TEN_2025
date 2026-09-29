# Lab 6 — Permissive CORS + Missing Security Headers

**Goal:** Steal the victim's JSON cross-origin, and frame their account page for clickjacking.

## Files

- Vulnerable: `/lab6-headers/vulnerable/data.php` — reflects any `Origin` + credentials
- Vulnerable: `/lab6-headers/vulnerable/account.php` — no frame/content-type protection
- Vulnerable: `/lab6-headers/vulnerable/attacker_demo.html` — attacker PoC page
- Secure: `/lab6-headers/fixed/data_secure.php`, `account_secure.php`

Login comes from the shared app (`/login.php`).

## Vulnerable Code

```php
// data.php — attacker's origin is trusted blindly
header("Access-Control-Allow-Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header("Access-Control-Allow-Credentials: true");
```

```php
// account.php — no X-Frame-Options, no CSP, no nosniff
```

## Attack

**1. CORS theft:**
1. Login as the victim (`user2` / `123456`)
2. Open `attacker_demo.html` **from another origin** (different port/host — e.g. `php -S localhost:8888` serving the file, or copy it elsewhere)
3. Click **Fetch victim data** → victim JSON (`id`, `username`, `role`) renders on the attacker page

**2. Clickjacking:**
1. Same page frames the real `account.php` under a fake "prize" button
2. Victim click lands on **Delete my account**

**3. Secure page refuses framing:** section 3 of the demo page frames `fixed/account_secure.php` — the frame stays empty (headers) and the page would show a red **Blocked** error if framed anyway (JS layer).

Verify missing headers: `curl -i .../vulnerable/account.php` shows no `X-Frame-Options`, no `Content-Security-Policy`, no `X-Content-Type-Options`.

## Fix

```php
// data_secure.php — allowlist, validate before reflecting
$allowed_origins = ['https://yourdomain.com'];
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
```

```php
// account_secure.php — headers (layer 1) + JS frame detector (layer 2)
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
```
```js
if (window.self !== window.top) { /* hide account, show Blocked error */ }
```

## Setup

Main setup only (`init.php` visited once).

- Vulnerable: `http://localhost/A022025-Security-Misconfiguration/lab6-headers/vulnerable/data.php`
- Secure: `http://localhost/A022025-Security-Misconfiguration/lab6-headers/fixed/data_secure.php`

## CWE

- CWE-942: Permissive Cross-domain Policy with Untrusted Domains
- CWE-1021: Improper Restriction of Rendered UI Layers (clickjacking)
- CWE-693: Protection Mechanism Failure (missing hardening headers)
