# Lab 1 — Detecting Vulnerable Libraries

**Goal:** Learn to detect dependencies with known CVEs using `composer audit` (the Composer equivalent of `npm audit`) and OWASP Dependency-Check, then upgrade to patched, maintained versions.

## Files

- Vulnerable: `/lab1-vulnerable-deps/vulnerable/` — Composer project pinned to old versions (PHPMailer 6.0.6, swiftmailer 6.2.4, php-jwt 5.0.0, league/csv 9.5.0) + `composer.lock` committed
- Vulnerable: `/lab1-vulnerable-deps/vulnerable/index.php` — small demo app that uses the old libraries
- Secure: `/lab1-vulnerable-deps/fixed/` — patched versions, `symfony/mailer` replaces the abandoned `swiftmailer`, `composer.lock` committed
- Secure: `/lab1-vulnerable-deps/fixed/audit.yml` — weekly scheduled audit workflow (GitHub Actions)

## Vulnerable State

```json
// composer.json — exact old versions, "it worked at install time" syndrome
"phpmailer/phpmailer": "6.0.6",
"swiftmailer/swiftmailer": "6.2.4",   // abandoned since Dec 2021!
"firebase/php-jwt": "5.0.0",
"league/csv": "9.5.0"
```

## Lab Steps

### 1. Run the audit

```powershell
cd A032025-Software-Supply-Chain-Failures\lab1-vulnerable-deps\vulnerable
composer audit
```

Expected: **6 advisories affecting 3 packages**, including:

| Package | Severity | CVE | Notes |
|---|---|---|---|
| phpmailer/phpmailer | high | CVE-2020-13625 | XSS via attachment names |
| phpmailer/phpmailer | high | CVE-2021-34551 | RCE on Windows via UNC paths |
| phpmailer/phpmailer | high | CVE-2021-3603 | Untrusted code via address validator |
| swiftmailer/swiftmailer | medium | CVE-2024-28859 | Deserialization gadget chain |
| league/csv | — | CVE-2022-38454 | HTML/XSS injection |

Plus **1 abandoned package**: `swiftmailer/swiftmailer` → this is CWE-1104 (unmaintained third-party component), not a CVE — a pure supply-chain risk `npm audit` had no way to flag a few years ago.

### 2. Run the demo app (it still "works")

```powershell
php index.php
```

Dependencies being vulnerable does not break the app — that's why they survive in production for years.

### 3. Second tool: local PHP security checker (CLI scanner like retire.js)

Download the Go binary (single file, no deps):

```powershell
# from https://github.com/fabpot/local-php-security-checker/releases
local-php-security-checker --path=path\to\composer.lock --format=markdown
```

### 4. Third tool: OWASP Dependency-Check (needs Java, or run via Docker)

Generates the full NVD-annotated report used for compliance:

```powershell
docker run --rm -e NVD_API_KEY=$env:NVD_API_KEY `
  -v "$(pwd):/src" -v "$env:USERPROFILE\.owasp-dc:/shared" `
  -w /src owasp/dependency-check:latest `
  --scan composer.lock --enableRetired --project lab1
```

Open `dependency-check-report.html` — compare the findings with `composer audit`.

### 5. Fix

```powershell
cd ..\fixed
composer audit                  # -> "No security vulnerability advisories found."
composer audit --abandoned=fail # -> clean: abandoned package was replaced by symfony/mailer
php index.php                   # same app runs on maintained libs
```

Note two interesting diffs in `composer.lock`:
- `swiftmailer` replaced with `symfony/mailer` (never keep an abandoned package, even when "not vulnerable")
- `firebase/php-jwt` 7.x now **rejects HS256 keys shorter than 32 bytes** — supply-chain fixes also change behavior; test after upgrading

### 6. Make it continuous (the real fix)

Copy `fixed/audit.yml` to `.github/workflows/audit.yml` — the audit runs weekly with `--abandoned=fail`, so a new advisory published on Monday fails CI on Tuesday even without a deploy.

## Key Commands Cheat Sheet

| npm | Composer |
|---|---|
| `npm audit` | `composer audit` |
| `npm audit fix` | `composer update` (versions driven by `composer.json`) |
| `npm ls <pkg>` | `composer why <pkg>` |
| `npm outdated` | `composer outdated` |
| Snyk/retire.js | `local-php-security-checker`, Dependency-Check |

## CWE

- CWE-1395: Dependency on Vulnerable Third-Party Component
- CWE-1104: Use of Unmaintained Third-Party Component
- CWE-1329: Reliance on Component That is Not Updateable
