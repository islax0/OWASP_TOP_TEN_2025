<?php
/**
 * LAB 1 — Secrets (SECURE status endpoint)
 *
 * Fixed code:
 *   if (!APP_DEBUG) { 404 }
 *   require login, return {status:ok} only — never env.
 */
require_once __DIR__ . '/../../../auth.php';

header('Content-Type: application/json');

// ========== SECURE: 404 in prod, minimal output only when debugging ==========
$debug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
if (!$debug) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}
requireLogin();
// Minimal, non-sensitive health check only — NEVER dumps env
echo json_encode(['app' => 'Acme HelpDesk Mini', 'status' => 'ok', 'time' => date('c')]);
exit;
