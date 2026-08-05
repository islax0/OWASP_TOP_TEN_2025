# Broken Access Control Labs (PHP + SQLite)

Hands-on labs covering the most common Broken Access Control scenarios.

## Setup

1. Place this folder under your web root (e.g. `htdocs/broken-access-control-labs` or `/var/www/html/...`).
2. Ensure PHP has the **SQLite** extension (`pdo_sqlite`).
3. Visit once:  
   `http://localhost/broken-access-control-labs/init.php`  
   This creates `database.sqlite` and seeds users + orders.
4. Login: `http://localhost/broken-access-control-labs/login.php`

### Demo accounts

| Username | Password | Role  | Balance |
|----------|----------|-------|---------|
| admin    | 123456   | admin | 10000   |
| user1    | 123456   | user  | 1500    |
| user2  | 123456   | user  | 800     |

If the app is not at `/broken-access-control-labs`, edit `BASE_URL` in `config.php`.

---

## Lab 1 — IDOR

**Goal:** View other users' orders by changing the `id` parameter.

- List (safe): `/lab1-idor/orders.php`
- Detail (vulnerable): `/lab1-idor/order.php?id=1`

**Vulnerable code:**
```php
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
```

**Attack:** Login as `user2`, then open `order.php?id=1`, `?id=2`, …

**Fix:**
```php
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['id']]);
```

---

## Lab 2 — Missing Authorization + Forced Browsing

**Goal:** Reach the admin panel and delete users without being admin.

- `/lab2-missing-auth/admin.php` — only checks login, not role
- `/lab2-missing-auth/delete_user.php?id=3` — no authorization

**Attack:** Login as `user2` → open `/lab2-missing-auth/admin.php` directly (forced browsing) → delete users.

**Fix:**
```php
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit;
}
```

---

## Lab 3 — Privilege Escalation

### Vertical (parameter tampering)
- `/lab3-privilege/profile.php`
- Change the role dropdown to `admin` and submit → becomes admin.

**Vulnerable:**
```php
UPDATE users SET role = ? WHERE id = ?
// role comes from $_POST['role']
```

**Fix:** Never accept `role` from the client (`unset($_POST['role'])` or hardcode).

### Horizontal
- `/lab3-privilege/profile.php?id=2` — view another user's profile.

### Vertical (forced browsing)
- `/lab3-privilege/users.php` — user list with no admin check.

---

## Lab 4 — CSRF

**Goal:** Transfer money without the victim's interaction beyond visiting a page.

- Form: `/lab4-csrf/transfer.php`
- Action (no token): `/lab4-csrf/transfer_action.php`

**Attack page:**
```html
<form action="http://localhost/broken-access-control-labs/lab4-csrf/transfer_action.php" method="POST">
  <input name="to" value="2">
  <input name="amount" value="100">
</form>
<script>document.forms[0].submit();</script>
```

Open while logged in as the victim → money is transferred.

**Fix:** Generate and validate a CSRF token (`$_SESSION['csrf']` vs `$_POST['csrf']`).

---

## Bonus — CORS Misconfiguration

- API: `/api/user.php`
- Reflects any `Origin` + `Access-Control-Allow-Credentials: true`
- Demo attacker page: `/api/attacker_demo.html`

While logged in, open the attacker page (or host it on another origin) and click **Fetch victim data**. The API response (id, username, role, balance) is readable cross-origin.

---

## Lab 5 — Force Browsing on Uploaded Files

**Goal:** Access uploaded files without authentication by guessing or enumerating filenames.

- Vulnerable: `/lab5-force-browsing/upload.php`
- Secure: `/lab5-force-browsing/upload_secure.php`

**Attack:** Login as any user → upload a file → access it directly via `/uploads/[filename]` → try accessing other users' files.

**Fix:**
- Store uploads in protected directory with `.htaccess` (Deny from all)
- Serve files through authenticated PHP script
- Verify file ownership before serving
- Prevent directory traversal attacks

*See `A012025 Broken Access Control/lab5-force-browsing/README.md` for detailed testing instructions and security fixes.*

---

## Design rationale

| Lab | Concept |
|-----|---------|
| 1   | IDOR — missing ownership check on resources |
| 2   | Missing Authorization + Forced Browsing |
| 3   | Privilege Escalation (horizontal + vertical + parameter tampering) |
| 4   | CSRF — cookie-only auth, no anti-CSRF token |
| 5   | Force Browsing on Uploaded Files |
| Bonus | CORS reflecting Origin with credentials |

Each lab focuses on one idea so you can explain it in short posts or videos without distraction.

## Reset database

Visit `init.php` again anytime to wipe and re-seed.