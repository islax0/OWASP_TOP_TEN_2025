<?php
/**
 * LAB 1 — Secrets (SECURE VERSION)
 *
 * Fixed code:
 *   env_or_fail('SMTP_PASSWORD'); // secrets from env/vault, fail closed
 *   → no secrets in code, .env lives OUTSIDE webroot, never committed
 */
function env_or_fail(string $name): string {
    $v = getenv($name);
    if ($v === false || $v === '') {
        http_response_code(500);
        die('Server misconfigured: missing env ' . $name);
    }
    return $v;
}
