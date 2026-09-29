<?php
/**
 * LAB 1 — Secrets (Vulnerable info page)
 *
 * Vulnerable code:
 *   <?= SMTP_HOST ?> / <?= substr(STRIPE_SECRET_KEY, 0, 12) ?>
 *   → displays secrets to any logged-in user
 *
 * Fix: display no secrets. See ../fixed/info_secure.php
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();

require_once __DIR__ . '/config.php';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Info (Lab 1)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 700px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 1 — Secrets</span>
        <div>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>System info</h1>
            <p style="color:#94a3b8;margin:.5rem 0 1rem">Welcome, <?= htmlspecialchars($user['username']) ?>. Left by dev for troubleshooting:</p>
            <p>SMTP host: <code><?= htmlspecialchars(SMTP_HOST) ?></code> as <code><?= htmlspecialchars(SMTP_USER) ?></code></p>
            <p>Stripe key prefix: <code><?= htmlspecialchars(substr(STRIPE_SECRET_KEY, 0, 12)) ?>...</code> (full key in config.php)</p>
        </div>
    </div>
<!-- TODO(mike): remove debug page before prod: status.php -->
<!-- backup: config.php.bak (apache serves it as plain text) -->
</body>
</html>
