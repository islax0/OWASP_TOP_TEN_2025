# Lab 3 — Privilege Escalation

## Vertical (Parameter Tampering)

**Goal:** Change your role to admin by modifying the form data.

- Vulnerable: `/lab3-privilege/vulnerable/profile.php`
- Secure: `/lab3-privilege/fixed/profile_secure.php`

### Vulnerable Code

```php
UPDATE users SET role = ? WHERE id = ?
// role comes from $_POST['role']
```

### Attack

1. Login as a regular user
2. Change the role dropdown to `admin` in the profile form
3. Submit the form
4. User becomes admin

### Fix

Never accept `role` from the client (`unset($_POST['role'])` or hardcode the role).

## Horizontal Privilege Escalation

**Goal:** View another user's profile.

- `/lab3-privilege/vulnerable/profile.php?id=2`

### Attack

1. Login as `user1`
2. Access `/lab3-privilege/vulnerable/profile.php?id=2` to view `user2`'s profile

### Fix

Check that the profile being accessed belongs to the current user.

## Vertical (Forced Browsing)

**Goal:** Access the user list without admin privileges.

- Vulnerable: `/lab3-privilege/vulnerable/users.php` — user list with no admin check
- Secure: `/lab3-privilege/fixed/users_secure.php`

### Attack

1. Login as a regular user
2. Access `/lab3-privilege/vulnerable/users.php` directly

### Fix

Add admin role check before allowing access to the user list.
