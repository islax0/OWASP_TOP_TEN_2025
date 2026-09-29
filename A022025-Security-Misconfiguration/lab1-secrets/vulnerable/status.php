<?php
/**
 * LAB 1 — Secrets (Vulnerable status endpoint)
 *
 * Vulnerable code:
 *   echo json_encode(['_ENV' => $_ENV, ...]);
 *   → dumps getenv()/$_ENV with zero authentication (CWE-526)
 *
 * Fix (see ../fixed/status_secure.php):
 *   404 in prod; when debug is on, require login and return
 *   {status:ok} only — never env.
 */
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

// ========== VULNERABLE: exposes environment with zero auth ==========
echo json_encode([
    'app' => 'Acme HelpDesk Mini',
    'debug' => true,
    'php_version' => PHP_VERSION,
    'env_APP_MASTER_KEY' => getenv('APP_MASTER_KEY'),
    '_ENV' => $_ENV,
    '_SERVER_filtered' => array_filter($_SERVER, function($k){
        // Developer tried to be "helpful" - leaks anything with KEY, PASS, SECRET, TOKEN
        foreach (['KEY','PASS','SECRET','TOKEN','MASTER'] as $needle) {
            if (stripos($k, $needle) !== false) return true;
        }
        return false;
    }, ARRAY_FILTER_USE_KEY),
    'hint' => 'See also config.php.bak and robots.txt'
], JSON_PRETTY_PRINT);
