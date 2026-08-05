<?php
/**
 * LAB 2 — Missing Authorization on destructive action (Vulnerable)
 *
 * delete_user.php?id=3  — no role check, any logged-in user can delete others.
 *
 * Fix: requireAdmin() or explicit role check before DELETE.
 */
require_once __DIR__ . '/../../auth.php';

if (!isset($_SESSION['user'])) {
    die('Not authenticated');
}

// ========== VULNERABLE: no role check ==========
// FIXED would be:
// requireAdmin();
// or:
// if (($_SESSION['role'] ?? '') !== 'admin') {
//     http_response_code(403);
//     exit;
// }

$id = $_GET['id'] ?? null;
if ($id === null || !ctype_digit((string)$id)) {
    die('Invalid id');
}

$pdo = getDB();

// Prevent deleting yourself / admin for lab stability (optional guard)
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$target = $stmt->fetch();

if (!$target) {
    die('User not found');
}

if ($target['username'] === 'admin') {
    die('Cannot delete the admin account in this lab.');
}

// First clean their orders to avoid foreign key constraint violation
$pdo->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$id]);

// Then delete the user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header('Location: http://localhost/A012025%20Broken%20Access%20Control/lab2-missing-auth/admin.php?deleted=1');
exit;