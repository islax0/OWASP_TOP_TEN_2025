<?php
/**
 * LAB 3 helper — direct role update endpoint (also vulnerable).
 * Can be called via POST for demos.
 */
require_once __DIR__ . '/../../auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('POST only');
}

$user = currentUser();
$pdo = getDB();

// VULNERABLE: accepts role from client
$role = $_POST['role'] ?? 'user';
$id   = $_POST['id'] ?? $user['id'];

// No ownership / admin check
$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->execute([$role, $id]);

if ((int)$id === (int)$user['id']) {
    $_SESSION['role'] = $role;
}

header('Location: http://localhost/A012025-Broken-Access-Control/lab3-privilege/profile.php?id=' . (int)$id);
exit;