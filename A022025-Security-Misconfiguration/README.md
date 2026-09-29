# A02: Security Misconfiguration Labs

Hands-on labs covering the most common Security Misconfiguration scenarios from OWASP Top Ten 2025.

## Overview

Security Misconfiguration is #2 in OWASP Top Ten 2025. These labs demonstrate misconfigurations including exposed secrets, debug endpoints, insecure cookies, XXE, default configs, and missing security headers.

## Labs

- [Lab 1 — Secrets / Credentials](lab1-secrets/README.md) - Hard-coded secrets, .env exposure, backup files, debug endpoint
- [Lab 2 — Debug / Development Configuration](lab2-debug/README.md) - phpinfo in prod, verbose errors
- [Lab 3 — Cookies](lab3-cookies/README.md) - Cleartext cookie, missing Secure / HttpOnly / SameSite
- [Lab 4 — XML](lab4-xml/README.md) - XXE file read, entity-expansion DoS
- [Lab 5 — General Configuration](lab5-config/README.md) - Default creds, directory listing, backup in webroot
- [Lab 6 — Web / Browser](lab6-headers/README.md) - Permissive CORS, clickjacking, missing hardening headers

## Setup

Labs run on the shared app (same login, dashboard, DB as A01):

1. Complete the main setup (`init.php`, login via `/login.php`)
2. Open the lab's `vulnerable/` page from the dashboard
3. Compare with the `fixed/` version (`*_secure.php`)

See each lab's README for exact URLs and steps.
See the main [README](../README.md) for full setup instructions.
