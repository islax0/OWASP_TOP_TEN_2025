<?php
/**
 * LAB 4 — CSRF Vulnerable Transfer Action
 *
 * No CSRF token validation. Any POST with a valid session cookie succeeds.
 *
 * Fix:
 *   1. Generate token: $_SESSION['csrf'] = bin2hex(random_bytes(32));
 *   2. Put hidden input in form: <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
 *   3. Verify: if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { die('CSRF'); }
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('POST only');
}

// ========== VULNERABLE: no CSRF check ==========
// FIXED:
// if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
//     http_response_code(403);
//     die('Invalid CSRF token');
// }

$userId = (int)$_SESSION['id'];
$amount = (float)($_POST['amount'] ?? 0);
$to     = (int)($_POST['to'] ?? 0);

if ($amount <= 0 || $to <= 0 || $to === $userId) {
    $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Invalid transfer parameters'];
    header('Location: http://localhost/A012025-Broken-Access-Control/lab4-csrf/vulnerable/transfer.php');
    exit;
}

$pdo = getDB();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $balance = (float)$stmt->fetchColumn();

    if ($balance < $amount) {
        $pdo->rollBack();
        $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Insufficient balance'];
        header('Location: http://localhost/A012025-Broken-Access-Control/lab4-csrf/vulnerable/transfer.php');
        exit;
    }

    // Debit sender
    $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")
        ->execute([$amount, $userId]);

    // Credit receiver
    $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
        ->execute([$amount, $to]);

    $pdo->commit();

    $_SESSION['flash'] = [
        'type' => 'ok',
        'msg'  => "Transferred \$$amount to user #$to successfully."
    ];
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Transfer failed'];
}

header('Location: http://localhost/A012025-Broken-Access-Control/lab4-csrf/vulnerable/transfer.php');
exit;