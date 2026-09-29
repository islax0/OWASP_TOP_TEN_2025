<?php
/**
 * LAB 2 — Missing Authorization on destructive action (SECURE VERSION)
 *
 * Fixed code:
 *   requireAdmin();
 *   → only admin users can delete other users
 */
require_once __DIR__ . '/../../../auth.php';
requireAdmin();

$id = $_GET['id'] ?? null;
if ($id === null || !ctype_digit((string)$id)) {
    die('Invalid id');
}

$pdo = getDB();

$stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$target = $stmt->fetch();

if (!$target) {
    die('User not found');
}

if ($target['username'] === 'admin') {
    die('Cannot delete the admin account in this lab.');
}

$pdo->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$id]);

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header('Location: http://localhost/A012025-Broken-Access-Control/lab2-missing-auth/fixed/admin_secure.php?deleted=1');
exit;
