# FIX: digest-pinned, multi-stage, non-root, no scripts, no secrets
# Get the digest once and record it:
#   docker buildx imagetools inspect php:8.4-fpm-alpine:8.4.12 | Select-String Digest
FROM php:8.4-fpm-alpine@sha256:REPLACE_WITH_REAL_DIGEST

# composer comes from the official image COPY - not curl|bash
COPY --from=composer:2.8.11@sha256:REPLACE_WITH_REAL_DIGEST /usr/bin/composer /usr/bin/composer

WORKDIR /app

# manifest + lock only; verify what is installed BEFORE any fetch
COPY composer.json composer.lock ./

# build-time gate: manifest/lock in sync, no advisories, no abandoned packages
RUN composer validate --strict --no-check-publish \
    && composer audit --locked --abandoned=fail --no-interaction

# --no-scripts: nothing from the registry executes on the build machine
RUN composer install --no-scripts --no-interaction --prefer-dist --no-dev \
    && rm composer.json composer.lock

# app code only (never .env)
COPY --chown=www-data:www-data src/ /app/src/

USER www-data

HEALTHCHECK --interval=30s --timeout=3s CMD php /app/src/healthcheck.php

# build reproducibly + attach provenance and SBOM attestations:
# docker buildx build --provenance=true --sbom=true -t invoice-app:1.4.2 .
