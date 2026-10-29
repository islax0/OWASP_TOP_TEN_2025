# A03: Software Supply Chain Failures Labs

Hands-on labs covering OWASP Top 10:2025 **A03 — Software Supply Chain Failures** (السلسلة البديلة للبرمجيات / فشل سلسلة البرمجيات).
Stack for these labs: **PHP 8.4 + Composer 2.8 + Git + Docker** (equivalents of the npm/npm-audit/node world).

## Labs (خريطة اللابات)

| # | Lab | Arabic | Tools used |
|---|-----|--------|------------|
| 1 | [Detecting Vulnerable Libraries](lab1-vulnerable-deps/README.md) | اكتشاف المكتبات الضعيفة | `composer audit`, local-php-security-checker, OWASP Dependency-Check (Docker) |
| 2 | [Dependency Tree & SBOM](lab2-sbom/README.md) | تتبع التبعيات وSBOM | `composer show -t`, `composer why`, CycloneDX `make-sbom` |
| 3 | [Detecting Malicious Changes](lab3-malicious-changes/README.md) | اكتشاف التغييرات الخبيثة | git hooks / PR diff review, `composer-guard.php`, branch protection, dependabot |
| 4 | [Securing the Build Chain](lab4-build-chain/README.md) | تأمين سلسلة بناء البرنامج | Docker pinning by digest, buildx provenance, SBOM attestation, cosign |

## What A03 covers (mapped to the labs)

- You don't track versions of direct **and** transitive dependencies → Lab 1 + Lab 2
- Vulnerable/unsupported/abandoned components → Lab 1 (`composer audit --abandoned=fail`)
- No change-management over the repo/CI/artifacts → Lab 3 + Lab 4
- No separation of duties (one person writes + ships) → Lab 3 (branch protection, CODEOWNERS)
- CI/CD weaker than the product it builds → Lab 4 (hardened workflow, signing)

## Setup

```powershell
# per-lab:
cd A032025-Software-Supply-Chain-Failures\labN-...\vulnerable  (or \fixed)
composer install
php index.php / php <lab-specific command>
```

Every `composer.lock` is committed — diffs between the vulnerable and fixed lockfiles are themselves lab material.

## Incident models simulated (from OWASP A03:2025 scenarios)

- **Shai-Hulud (2025, npm)** — self-propagating worm harvesting env + tokens via install script → Lab 3 `tools/collect-env.php` simulation
- **Bybit (2025)** — code behaving maliciously only under a specific condition (build env) → Lab 3 `laravell-support` package
- **SolarWinds (2019)** — trusted pipeline shipping attacker code → Lab 4

## Requirements

- PHP ≥ 8.4 (7.x/8.x fine for Lab 1 vulnerable set)
- Composer ≥ 2.7 (`composer audit` + `allow-plugins`)
- git (Lab 3), Docker optional (Lab 1 Dependency-Check + Lab 4 image steps)
