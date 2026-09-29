# Lab 2 — Missing Authorization + Forced Browsing

**Goal:** Reach the admin panel and delete users without being admin.

## Files

- `/lab2-missing-auth/vulnerable/admin.php` — only checks login, not role
- `/lab2-missing-auth/vulnerable/delete_user.php?id=3` — no authorization
- Secure: `/lab2-missing-auth/fixed/admin_secure.php`, `delete_user_secure.php`

## Attack

1. Login as `user2`
2. Open `/lab2-missing-auth/vulnerable/admin.php` directly (forced browsing)
3. Delete users via the admin panel

## Fix

```php
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit;
}
```

Add role-based access control to check if the user has admin privileges before accessing admin functions.
