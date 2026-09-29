<?php
/**
 * LAB 6 — JSON endpoint with reflected Origin (Vulnerable)
 *
 * Vulnerable code:
 *   header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
 *   header("Access-Control-Allow-Credentials: true");
 *   → any site can read this response with the victim's cookies (CWE-942).
 *
 * Fix (see ../fixed/data_secure.php): strict allowlist, no credentials wildcard.
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();
$user = currentUser();

// ========== VULNERABLE: reflects any Origin with credentials ==========
header("Access-Control-Allow-Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header("Access-Control-Allow-Credentials: true");

header('Content-Type: application/json');
echo json_encode([
    'id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
]);
