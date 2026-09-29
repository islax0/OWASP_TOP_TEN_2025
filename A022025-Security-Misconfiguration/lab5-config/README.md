# Lab 5 — Default Credentials + Directory Listing

**Goal:** Log into the vendor panel with factory defaults, then loot the browsable backup directory.

## Files

- Vulnerable: `/lab5-config/vulnerable/admin.php` — `admin` / `admin`
- Vulnerable: `/lab5-config/vulnerable/files/` — listing ON (`Options +Indexes`) with `backup.sql`
- Secure: `/lab5-config/fixed/admin_secure.php` — `requireAdmin()`, no defaults
- Secure: `/lab5-config/fixed/files/.htaccess` — `Options -Indexes`, no backup shipped

## Vulnerable Code

```php
// admin.php — factory default, never force-changed on install
if ($u === 'admin' && $p === 'admin') { $_SESSION['lab5_admin'] = true; }
```

```apache
# files/.htaccess — directory listing enabled
Options +Indexes
```

(CWE-798 default credentials, CWE-16 configuration, CWE-538 file exposure.)

## Attack

1. Open `admin.php` → login `admin` / `admin` (first guess from the vendor manual — no brute force needed)
2. Follow the `files/` link → directory listing shows `backup.sql`, `notes.txt` (bundled `index.php` simulates `Options +Indexes` so it works on any server; the `.htaccess` next to it is the real-world config)
3. Open `files/backup.sql` → user table with SHA1 hashes (`d033e22a...`, `5baa61e4...`)
4. Crack offline (CrackStation / hashcat `mode 100`) → both are top-10 passwords; reuse them against the main app / other services

## Fix

```php
// admin_secure.php — default account deleted entirely
requireAdmin();   // shared-app admins only
```

```apache
# fixed/files/.htaccess
Options -Indexes
```

Move backups outside the webroot (a 403 on the file is not enough if the path is guessable — remove it), force credential change on install, re-hash with bcrypt.

## Setup

Main setup only (`init.php` visited once). No DB seed needed for the vulnerable panel.

- Vulnerable: `http://localhost/A022025-Security-Misconfiguration/lab5-config/vulnerable/admin.php`
- Secure: `http://localhost/A022025-Security-Misconfiguration/lab5-config/fixed/admin_secure.php` (non-admins → 403)

## CWE

- CWE-798: Use of Hard-coded Credentials (factory default)
- CWE-16: Configuration (listing on, backups in webroot)
- CWE-538: File and Directory Information Exposure
