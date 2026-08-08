<?php
/**
 * LAB 4 — CSRF (Vulnerable money transfer form)
 *
 * No CSRF token. State-changing POST relies only on session cookie.
 */
require_once __DIR__ . '/../../auth.php';
requireLogin();

$user = currentUser();
$pdo = getDB();

$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$balance = $stmt->fetchColumn();

$users = $pdo->query("SELECT id, username FROM users WHERE id != " . (int)$user['id'])->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money — Lab 4</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 480px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
        h1 { font-size: 1.25rem; margin-bottom: .5rem; }
        label { display: block; margin: 1rem 0 .35rem; font-size: .875rem; color: #cbd5e1; }
        select, input { width: 100%; padding: .65rem; border: 1px solid #334155; border-radius: 8px; background: #0f172a; color: #f1f5f9; }
        button { margin-top: 1.25rem; width: 100%; padding: .75rem; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .balance { color: #94a3b8; margin-bottom: 1rem; }
        .hint { background: #1e293b; border-left: 4px solid #f59e0b; padding: 1rem; margin: 1.25rem 0; border-radius: 0 8px 8px 0; font-size: .85rem; color: #fbbf24; }
        .flash { padding: .75rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .flash.ok { background: #14532d; color: #bbf7d0; }
        .flash.err { background: #7f1d1d; color: #fecaca; }
        pre { background: #0f172a; padding: 1rem; border-radius: 8px; overflow-x: auto; font-size: .75rem; margin-top: 1rem; color: #94a3b8; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 4 — CSRF</span>
        <div>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>Transfer Money</h1>
            <p class="balance">Your balance: <strong>$<?= number_format($balance, 2) ?></strong></p>

            <?php if ($flash): ?>
                <div class="flash <?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
            <?php endif; ?>

            <form method="POST" action="transfer_action.php">
                <!-- NO CSRF TOKEN -->
                <label>To user</label>
                <select name="to" required>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?> (id=<?= $u['id'] ?>)</option>
                    <?php endforeach; ?>
                </select>

                <label>Amount</label>
                <input type="number" name="amount" min="1" step="0.01" required placeholder="100">

                <button type="submit">Transfer</button>
            </form>
        </div>

        <div class="hint">
            <strong>CSRF attack:</strong> An attacker hosts a page that auto-submits a form to
            <code>transfer_action.php</code>. Because auth is only the session cookie, the browser
            sends it and the transfer happens.
        </div>

        <pre>&lt;!-- Attacker page (save as attacker.html and open while logged in) --&gt;
&lt;form action="http://localhost/A012025-Broken-Access-Control/lab4-csrf/transfer_action.php"
      method="POST"&gt;
  &lt;input name="to" value="2"&gt;
  &lt;input name="amount" value="100"&gt;
&lt;/form&gt;
&lt;script&gt;document.forms[0].submit();&lt;/script&gt;</pre>
    </div>
</body>
</html>