# A01: Broken Access Control Labs

Hands-on labs covering the most common Broken Access Control scenarios from OWASP Top Ten 2025.

## Overview

Broken Access Control is the #1 vulnerability in OWASP Top Ten 2025. These labs demonstrate various access control vulnerabilities including IDOR, missing authorization, privilege escalation, CSRF, force browsing, and JWT signature verification bypass.

## Labs

- [Lab 1 — IDOR](lab1-idor/README.md) - Insecure Direct Object Reference
- [Lab 2 — Missing Authorization](lab2-missing-auth/README.md) - Forced Browsing
- [Lab 3 — Privilege Escalation](lab3-privilege/README.md) - Horizontal & Vertical
- [Lab 4 — CSRF](lab4-csrf/README.md) - Cross-Site Request Forgery
- [Lab 5 — Force Browsing](lab5-force-browsing/README.md) - Uploaded Files
- [Lab 6 — JWT](lab6-jwt/README.md) - Signature Verification Bypass
- [Bonus — CORS](api/README.md) - Misconfiguration

## Setup

Ensure you have completed the main setup:
1. Database initialized via `init.php`
2. Logged in with a demo account

See the main [README](../README.md) for full setup instructions.
