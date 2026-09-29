<?php
/**
 * LAB 2 — Debug page (SECURE VERSION)
 *
 * Fixed code:
 *   if (!APP_DEBUG) { 404 } + requireLogin()
 *   → no phpinfo in prod; admins get a minimal status only.
 */
require_once __DIR__ . '/../../../auth.php';

// ========== SECURE: dead in production ==========
$debug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
if (!$debug) {
    http_response_code(404);
    die('Not found');
}
requireLogin();
header('Content-Type: application/json');
// Minimal output only — NEVER phpinfo()
echo json_encode(['app' => 'labs', 'status' => 'ok', 'time' => date('c')]);
