<?php
/**
 * LAB 3 — Direct role update endpoint (SECURE VERSION)
 *
 * Fixes applied:
 * 1. Only admin can change roles
 * 2. Target user must be specified by admin, not by the attacker
 * 3. Role value is validated against a whitelist
 */
require_once __DIR__ . '/../../../auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('POST only');
}

$pdo = getDB();

$role = $_POST['role'] ?? '';
$id   = (int)($_POST['id'] ?? 0);

// ========== SECURE: validate role against whitelist ==========
$allowedRoles = ['user', 'admin'];
if (!in_array($role, $allowedRoles, true)) {
    http_response_code(400);
    die('Invalid role');
}

if ($id <= 0) {
    http_response_code(400);
    die('Invalid user id');
}

$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->execute([$role, $id]);

header('Location: http://localhost/A012025-Broken-Access-Control/lab3-privilege/fixed/profile_secure.php?id=' . $id);
exit;
