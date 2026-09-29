# OWASP Top Ten 2025 - Security Labs

Hands-on labs covering the OWASP Top Ten 2025 security vulnerabilities.

## Categories

- [A01: Broken Access Control](A012025-Broken-Access-Control/README.md) - Labs for access control vulnerabilities
- [A02: Security Misconfiguration](A022025-Security-Misconfiguration/README.md) - Labs for misconfiguration vulnerabilities

## Setup

1. Place this folder under your web root (e.g. `htdocs` or `/var/www/html/`).
2. Ensure PHP has the **SQLite** extension (`pdo_sqlite`).
3. Visit once:  
   `http://localhost/init.php`  
   This creates `database.sqlite` and seeds users + orders.
4. Login: `http://localhost/login.php`

### Demo accounts

| Username | Password | Role  | Balance |
|----------|----------|-------|---------|
| admin    | 123456   | admin | 10000   |
| user1    | 123456   | user  | 1500    |
| user2    | 123456   | user  | 800     |

If the app is not at the web root, edit `BASE_URL` in `config.php`.

## Reset database

Visit `init.php` again anytime to wipe and re-seed.