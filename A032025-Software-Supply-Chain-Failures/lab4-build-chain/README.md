# Lab 4 — Securing the Build Chain

**Goal:** Attack the *build and release* side of the supply chain from the attacker's perspective (untrusted scripts, unpinned images, mutability), then harden Docker builds and CI/CD with digest pinning, provenance, SBOM attestations, signing, and artifact promotion.

## Files

- Vulnerable: `/lab4-build-chain/vulnerable/Dockerfile` — `php:8.4-apache:latest`, `curl|bash`, tarball with no checksum, `composer install` with scripts, `.env` in image, runs as root
- Vulnerable: `/lab4-build-chain/vulnerable/ci-build.yml` — floating action tag, `curl bootstrap | bash`, mutable `:latest` tag
- Secure: `/lab4-build-chain/fixed/Dockerfile` — digest-pinned base + composer, `validate/audit/--no-scripts` inside the build, non-root, noted `--provenance=true --sbom=true`
- Secure: `/lab4-build-chain/fixed/ci-deploy.yml` — pinned actions, least-priv token, scan gate, cosign sign/verify, promote-don't-rebuild deploy
- Secure: `/lab4-build-chain/fixed/verify-artifact.php` — sha256 manifest verification of release bundles (+ cosign verify-blob when signature present)

## Lab Steps

### 1. Find the failures line by line (vulnerable Dockerfile)

| # | Failure | Class |
|---|---|---|
| 1 | `curl http://internal-tools... \| bash` as root | Untrusted script execution (CWE-1357) |
| 2 | tarball downloaded with no sha256 | A08 integrity failure |
| 3 | `composer install` **with scripts enabled** | runs keys of `post-install-cmd` from the registry (Lab 3 payload) |
| 4 | `COPY .env` | secrets ship inside the image → registry leak |
| 5 | runs as root + `:latest` base | no isolation + image is mutable - "rebuild = surprise" |

Docker is a supply chain: `FROM php:8.4-apache` with no digest means upstream can change the bytes of "the same" tag.

### 2. Fix the base: pin the digest

```powershell
docker buildx imagetools inspect php:8.4-fpm-alpine:8.4.12 | Select-String Digest
# digest (sha256:...) -> paste into FROM
```

Then any rebuild is byte-identical (or fails) — the image can't silently drift.

### 3. Fix the pipeline (fixed/ci-deploy.yml)

Key mechanics in the hardened workflow:

- **Pinned actions**: `actions/checkout@11bd71901bbe5b1630ceea73d27597364c9af683`
- **Least privilege**: `permissions: contents: read`, job adds only `packages: write`, `id-token: write` for keyless signing
- **Provenance**: `docker buildx build --provenance=true --sbom=true` — the build records what it did and attaches a CycloneDX SBOM attestation (the image version of Lab 2)
- **Scan gate**: `docker scout cves <image>@<digest> --exit-code` blocks vulnerable builds
- **Signing**: `cosign sign` the digest; deploy step runs `cosign verify` with GitHub OIDC identity regex — unsigned/cross-origin artifacts cannot deploy
- **Promotion, not rebuild**: deploy pulls the **same digest** that was signed; if you rebuild per environment, you're shipping a different supply chain into prod

### 4. Verify artifacts manually (works without Docker)

```powershell
# produce release bundle + checksums
Compress-Archive src, vendor -Destination app-release.zip
(Get-FileHash app-release.zip -Algorithm SHA256).Hash | Set-Content -Encoding ascii sha256sums.txt -NoNewline
# in CI: record + sign (cosign sign-blob), in prod: verify
php fixed\verify-artifact.php
```

### 5. Run it

```powershell
cd lab4-build-chain\fixed
# create checksums to test verifier
"FFFF  fake.bin" | Set-Content sha256sums.txt
"AAAA  fake.bin" | Set-Content fake.bin
php verify-artifact.php        # -> [FAIL] fake.bin, exit 1
```

Then with Docker daemon running: build the fixed Dockerfile (insert real digests first), inspect provenance/SBOM attestations with `docker buildx imagetools inspect <image>@<digest>`.

## CWE

- CWE-1357: Reliance on Insufficiently Trustworthy Components / CI systems
- CWE-494: Download of Code Without Integrity Check
- CWE-798: Hard-coded credentials (env baked into image)
