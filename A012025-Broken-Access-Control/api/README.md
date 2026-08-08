# Bonus — CORS Misconfiguration

**Goal:** Demonstrate how misconfigured CORS can leak sensitive data across origins.

## Files

- API: `/api/user.php`
- Demo attacker page: `/api/attacker_demo.html`

## Vulnerability

The API reflects any `Origin` header and sets `Access-Control-Allow-Credentials: true`, allowing:
- Cross-origin requests with cookies
- Reading of sensitive API responses
- Potential data exfiltration

## Attack

1. Login to the main application
2. Open the attacker demo page (or host it on another origin)
3. Click **Fetch victim data**
4. The API response (id, username, role, balance) is readable cross-origin

## Vulnerable Code

```php
header("Access-Control-Allow-Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header("Access-Control-Allow-Credentials: true");
```

## Fix

```php
$allowed_origins = ['https://yourdomain.com'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
```

Only whitelist trusted origins and validate the Origin header before setting CORS headers.
