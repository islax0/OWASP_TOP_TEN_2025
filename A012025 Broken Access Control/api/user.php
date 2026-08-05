<?php
/**
 * BONUS — CORS Misconfiguration
 *
 * Reflects the request Origin and sets Access-Control-Allow-Credentials: true.
 * This allows any website to read authenticated API responses (credentialed CORS).
 *
 * Note: Browsers reject Access-Control-Allow-Origin: * together with
 * Access-Control-Allow-Credentials: true. Reflecting the Origin is the realistic
 * misconfiguration that enables the attack.
 *
 * Attack:
 *   Victim is logged in → visits attacker page → fetch('.../api/user.php', {credentials:'include'})
 *   → attacker receives {id, username, role, balance}
 */
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

// ========== VULNERABLE CORS ==========
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Require login for the actual data
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT id, username, role, balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['id']]);
$user = $stmt->fetch();

echo json_encode([
    'id'       => (int)$user['id'],
    'username' => $user['username'],
    'role'     => $user['role'],
    'balance'  => (float)$user['balance'],
]);