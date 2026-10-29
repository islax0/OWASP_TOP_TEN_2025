# Lab 2 — Dependency Tracking & SBOM

**Goal:** Understand direct vs transitive dependencies, map your full dependency tree, and generate a CycloneDX Software Bill of Materials (SBOM) for the whole project.

## Files

- App project: `/lab2-sbom/app/` — a tiny "production" app with only **2 direct dependencies**, which pulls in a bigger tree
- Generated inventory: `/lab2-sbom/app/sbom.cdx.json` (CycloneDX JSON) and `sbom.cdx.xml`
- Committed: `composer.json` **and** `composer.lock` (the lockfile IS part of the audit trail)

## Lab Steps

### 1. Direct vs transitive

```powershell
cd A032025-Software-Supply-Chain-Failures\lab2-sbom\app
composer install
composer show --direct       # only what we asked for (3 incl. dev tool)
composer show                # EVERYTHING installed (22 packages)
composer show -t             # the tree - who pulled in whom
```

Sample of the tree (`composer show -t`): only `symfony/mailer` + `monolog/monolog` were declared, yet:

```
symfony/mailer 7.4.22
|--psr/event-dispatcher
|--symfony/event-dispatcher
|--symfony/deprecation-contracts
|--symfony/mime
|  |--symfony/polyfill-intl-idn
|--symfony/service-contracts
|--symfony/polyfill-mbstring
monolog/monolog 3.12.1
|--monolog/cloudfoundry?...
```

**Key idea (A03):** you are vulnerable to a transitive dep you never saw in `composer.json`. When Log4Shell-like news hits, your question is "where does this package name appear *below* my direct deps?"

### 2. Trace a transitive dependency

```powershell
composer why symfony/mime        # which path brought it in
composer why-not phpmailer/phpmailer v6.0.6   # conflicts?
composer outdated --direct       # what is drifting behind
```

### 3. Generate an SBOM (CycloneDX)

```powershell
# the tool is a dev dependency (already in composer.json + allow-plugins config)
composer require --dev cyclonedx/cyclonedx-php-composer
composer install

composer cyclonedx:make-sbom --output-format=json --output-file=sbom.cdx.json
composer cyclonedx:make-sbom --output-format=xml  --output-file=sbom.cdx.xml
```

Note the trust moment: Composer asks to **allow the plugin** (`config.allow-plugins`). Explicitly allowlisting the CycloneDX tool is the correct hardened behavior — never leave the allowlist empty/open.

Inspect `sbom.cdx.json`: `serialNumber`, `metadata.tools`, `components` (22) with `name`, `version`, `purl`, `licenses`, and `externalReferences` (dist URL + dist sha of every package).

### 4. Use the SBOM to answer supply-chain questions

- Ship it to [OWASP Dependency-Track](https://owasp.org/www-project-dependency-track/) (or anywhere CycloneDX is consumed) for continuous monitoring: every new advisory that matches an SBOM component raises an alert.
- Quick local check: audit every component in the lockfile:

```powershell
composer audit --locked                       # exit code != 0 if any advisory exists
composer audit --abandoned=fail --locked      # also fail on abandoned packages
```

- Provenance sanity check on a lockfile entry: every `dist.shasum` in `composer.lock` corresponds to a dist Composer verified on install (`composer.json` vs `composer.lock` diff = exactly one PR's worth of supply-chain change).

### 5. Reduce the attack surface

```powershell
composer remove monolog/monolog     # unused dep? delete it (A03: remove unused components)
composer prune? -> (re-check tree)  composer show -t
```

Keep only what the product needs — SBOM shrinks, alert noise shrinks, blast radius shrinks.

## SBOM / tracking cheat sheet

| npm world | Composer world |
|---|---|
| `npm ls --all` / `npm tree` | `composer show -t` |
| `npm ls <pkg>` | `composer why <pkg>` |
| `npx @cyclonedx/cyclonedx-npm` | `composer cyclonedx:make-sbom` (CycloneDX PHP Composer plugin) |
| lockfile `package-lock.json` | `composer.lock` (also records `dist.shasum` per package) |

## CWE

- CWE-1035 / CWE-1357: reliance on insufficiently-trusted components
- CWE-1329: components not updateable/tracked

Related OWASP Cheat Sheet: [Dependency Graph SBOM](https://cheatsheetseries.owasp.org/cheatsheets/Dependency_Graph_SBOM_Cheat_Sheet.html)
