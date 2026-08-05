<?php
require_once __DIR__ . '/../../auth.php';
requireLogin();

$user = currentUser();
$pdo = getDB();

// Only show current user's orders (this page is "safe" listing)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 1 — IDOR | My Orders</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        nav a:hover { color: #f1f5f9; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1.5rem; }
        h1 { margin-bottom: .5rem; }
        .hint { background: #1e293b; border-left: 4px solid #f59e0b; padding: 1rem; margin: 1.5rem 0; border-radius: 0 8px 8px 0; font-size: .9rem; color: #fbbf24; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #334155; }
        th { color: #94a3b8; font-weight: 500; font-size: .8rem; text-transform: uppercase; }
        a.view { color: #3b82f6; text-decoration: none; }
        a.view:hover { text-decoration: underline; }
        .back { display: inline-block; margin-bottom: 1.5rem; color: #94a3b8; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 1 — IDOR</span>
        <div>
            <a href="../../dashboard.php">Dashboard</a>
            <a href="../../logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <a class="back" href="../../dashboard.php">← Dashboard</a>
        <h1>My Orders</h1>
        <p style="color:#94a3b8;margin-bottom:1rem;">Logged in as <?= htmlspecialchars($user['username']) ?> (user_id=<?= $user['id'] ?>)</p>

        <div class="hint">
            <strong>Vulnerability:</strong> Click any order → <code>order.php?id=N</code>.  
            The detail page only checks <code>id</code>, not ownership. Try <code>?id=1</code>, <code>?id=2</code>, … to view other users' orders.
        </div>

        <table>
            <thead>
                <tr><th>ID</th><th>Product</th><th>Price</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="4">No orders yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= htmlspecialchars($o['product']) ?></td>
                            <td>$<?= number_format($o['price'], 2) ?></td>
                            <td><a class="view" href="order.php?id=<?= $o['id'] ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>