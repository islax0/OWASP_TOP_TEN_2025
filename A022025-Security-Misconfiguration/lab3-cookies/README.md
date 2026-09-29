# Lab 3 — Insecure Cookie Configuration

**Goal:** Steal your own "remember me" cookie, decode it, and log in as that user without knowing the password.

## Files

- Vulnerable: `/lab3-cookies/vulnerable/remember.php` — password in cookie, no flags
- Secure: `/lab3-cookies/fixed/remember_secure.php` — signed token + hardened flags

## Vulnerable Code

```php
// password stored in cleartext, cookie readable by JS, sent over HTTP,
// and attached to cross-site requests
setcookie('remember', base64_encode("$u:$p"), time() + 86400 * 30);
```

Missing: `Secure` (CWE-614), `HttpOnly` (CWE-1004), `SameSite`, and the password should never be client-side at all (CWE-315).

## Attack

1. Open `remember.php`, login as `user1` / `123456` with **Remember me** checked
2. Devtools → Application → Cookies → copy the `remember` value (note: no `Secure`/`HttpOnly` flags)
3. `echo '<value>' | base64 -d` → `user1:123456` — the actual password
4. In a fresh private window (or `curl`), send only the cookie:
   ```bash
   curl -b "remember=<value>" http://localhost/.../vulnerable/remember.php -L
   ```
   → logged in as `user1` with zero knowledge of the password beforehand
5. Same cookie also works over plain HTTP (sniffable) and is readable by any injected script (no `HttpOnly`)

## Fix

```php
// signed, expiring token — username only, signature proves the server issued it
$token = base64($user) . '.' . base64($expiry) . '.' . hash_hmac('sha256', ..., $key);
setcookie('remember_secure', $token, [
    'expires' => time() + 86400 * 30,
    'secure' => true, 'httponly' => true, 'samesite' => 'Lax',
]);
```

Tampering breaks the HMAC; expiry is enforced server-side; XSS can't read it; it never leaves HTTPS.

## Setup

Main setup only (`init.php` visited once).

- Vulnerable: `http://localhost/A022025-Security-Misconfiguration/lab3-cookies/vulnerable/remember.php`
- Secure: `http://localhost/A022025-Security-Misconfiguration/lab3-cookies/fixed/remember_secure.php`

## CWE

- CWE-315: Cleartext Storage of Sensitive Information in a Cookie
- CWE-614: Sensitive Cookie in HTTPS Session Without 'Secure' Attribute
- CWE-1004: Sensitive Cookie Without 'HttpOnly' Flag
