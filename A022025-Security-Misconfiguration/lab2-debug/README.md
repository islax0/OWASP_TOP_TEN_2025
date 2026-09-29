# Lab 2 — Debug / Development Configuration

**Goal:** Find the forgotten debug page and verbose errors, then read server internals without credentials.

## Files

- Vulnerable: `/lab2-debug/vulnerable/debug.php` — unauthenticated `phpinfo()`
- Vulnerable: `/lab2-debug/vulnerable/errors.php` — `display_errors=1` with dev leftovers
- Secure: `/lab2-debug/fixed/debug_secure.php`, `errors_secure.php`

Login comes from the shared app (`/login.php`).

## Vulnerable Code

```php
// debug.php — reachable by anyone, no auth check at all
phpinfo();

// errors.php — dev settings shipped to prod
ini_set('display_errors', 1);
echo 100 / $_GET['div'];   // ?div=0 → warning/fatal with full path
```

## Attack

1. `GET .../vulnerable/debug.php` with **no session** → full `phpinfo()`: PHP version, extensions, `$_ENV`/`$_SERVER`, docroot paths, loaded `php.ini`
2. Login as `user2` / `123456`, open `errors.php?div=0` → `DivisionByZeroError` page leaks the absolute install path
3. Open `errors.php` (no `div`) → undefined-variable notice confirms display_errors is on
4. Use the version + extension list from step 1 to pick a matching exploit (e.g. known CVE for that PHP build)

## Fix

```php
// debug_secure.php — dead in production
if (!getenv('APP_DEBUG')) { http_response_code(404); exit; }
requireLogin();
echo json_encode(['status' => 'ok']);   // never phpinfo()

// errors_secure.php
ini_set('display_errors', 0);
ini_set('log_errors', 1);               // details → server log only
```

Validate input (`?div=0` gets "Invalid input", not a stack trace).

## Setup

Main setup only (`init.php` visited once).

- Vulnerable: `http://localhost/A022025-Security-Misconfiguration/lab2-debug/vulnerable/debug.php`
- Secure: `http://localhost/A022025-Security-Misconfiguration/lab2-debug/fixed/debug_secure.php` (→ 404)

## CWE

- CWE-489: Active Debug Code — debug page shipped to production
- CWE-215: Information Exposure Through Debug Information — phpinfo + stack traces
- CWE-209: Generation of Error Message Containing Sensitive Information
