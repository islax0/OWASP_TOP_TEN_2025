<?php
/**
 * LAB 1 — IDOR (Vulnerable)
 *
 * Vulnerable code:
 *   $id = $_GET['id'];
 *   SELECT * FROM orders WHERE id = ?
 *   → no check that the order belongs to the current user
 *
 * Fix:
 *   SELECT * FROM orders WHERE id = ? AND user_id = ?
 */
require_once __DIR__ . '/../../../auth.php';
// requireLogin();

$user = currentUser();
$pdo = getDB();

$id = $_GET['id'] ?? null;

if ($id === null || !ctype_digit((string)$id)) {
    http_response_code(400);
    die('Missing or invalid order id');
}

// ========== VULNERABLE: no ownership check ==========
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

// ========== FIXED version (commented) ==========
// $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
// $stmt->execute([$id, $user['id']]);
// $order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    die('Order not found');
}

// Lookup owner username for display
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$order['user_id']]);
$owner = $stmt->fetchColumn() ?: 'unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= (int)$order['id'] ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 560px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
        .card h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .row { display: flex; justify-content: space-between; padding: .6rem 0; border-bottom: 1px solid #334155; }
        .row:last-child { border-bottom: none; }
        .label { color: #94a3b8; }
        .warn { background: #7f1d1d; color: #fecaca; padding: .75rem 1rem; border-radius: 8px; margin-top: 1.25rem; font-size: .875rem; }
        .ok { background: #14532d; color: #bbf7d0; padding: .75rem 1rem; border-radius: 8px; margin-top: 1.25rem; font-size: .875rem; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 1 — IDOR</span>
        <div>
            <a href="orders.php">My Orders</a>
            <a href="/dashboard.php">Dashboard</a>
        </div>
    </nav>
    <div class="container">
        <a class="back" href="orders.php">← My Orders</a>
        <div class="card">
            <h1>Order #<?= (int)$order['id'] ?></h1>
            <div class="row"><span class="label">Product</span><span><?= htmlspecialchars($order['product']) ?></span></div>
            <div class="row"><span class="label">Price</span><span>$<?= number_format($order['price'], 2) ?></span></div>
            <div class="row"><span class="label">Owner user_id</span><span><?= (int)$order['user_id'] ?> (<?= htmlspecialchars($owner) ?>)</span></div>
            <div class="row"><span class="label">Your user_id</span><span><?= (int)$user['id'] ?></span></div>

            <?php if ((int)$order['user_id'] !== (int)$user['id']): ?>
                <div class="warn">
                    <strong>IDOR confirmed!</strong> This order belongs to another user.
                    You should not be able to see it.
                </div>
            <?php else: ?>
                <div class="ok">This is your own order.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>