<?php
/**
 * LAB 6 — JSON endpoint (SECURE VERSION)
 *
 * Fixed code:
 *   allowlist check on Origin before reflecting it; credentials only
 *   for listed origins — never '*' and never blind reflection (CWE-942).
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();
$user = currentUser();

// ========== SECURE: strict origin allowlist ==========
$allowed_origins = ['https://yourdomain.com'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header('Vary: Origin');
}

header('Content-Type: application/json');
echo json_encode([
    'id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
]);
