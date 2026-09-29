<?php
/**
 * LAB 1 — Secrets (SECURE info page)
 *
 * Fixed: displays no secrets. Debug page disabled.
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Info (Secure)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 700px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
        .ok { background: #14532d; color: #bbf7d0; padding: .75rem 1rem; border-radius: 8px; margin-top: 1.25rem; font-size: .875rem; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 1 — Secrets (SECURE)</span>
        <div>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>System info</h1>
            <p style="color:#94a3b8;margin:.5rem 0 1rem">Welcome, <?= htmlspecialchars($user['username']) ?>.</p>
            <div class="ok">FIXED: no SMTP password, no Stripe key, no secrets displayed. Debug page disabled.</div>
        </div>
    </div>
</body>
</html>
