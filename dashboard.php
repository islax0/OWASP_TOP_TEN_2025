<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$user = currentUser();
$pdo = getDB();

// Refresh balance
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$balance = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Broken Access Control Labs</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1.25rem; }
        nav a:hover { color: #f1f5f9; }
        .brand { font-weight: 700; color: #f1f5f9; }
        .container { max-width: 960px; margin: 2rem auto; padding: 0 1.5rem; }
        h1 { font-size: 1.75rem; margin-bottom: .5rem; }
        .meta { color: #94a3b8; margin-bottom: 2rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; transition: border-color .2s; }
        .card:hover { border-color: #3b82f6; }
        .card h2 { font-size: 1.1rem; margin-bottom: .5rem; }
        .card p { color: #94a3b8; font-size: .875rem; margin-bottom: 1rem; line-height: 1.5; }
        .card a { display: inline-block; background: #3b82f6; color: white; padding: .5rem 1rem; border-radius: 6px; text-decoration: none; font-size: .875rem; font-weight: 500; }
        .card a:hover { background: #2563eb; }
        .badge { display: inline-block; padding: .2rem .5rem; border-radius: 4px; font-size: .7rem; font-weight: 600; text-transform: uppercase; }
        .badge-admin { background: #7f1d1d; color: #fecaca; }
        .badge-user { background: #1e3a5f; color: #93c5fd; }
        .vuln { color: #f87171; font-size: .75rem; margin-top: .75rem; }
    </style>
</head>
<body>
    <nav>
        <span class="brand">BAC Labs</span>
        <div>
            <span><?= htmlspecialchars($user['username']) ?>
                <span class="badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>"><?= htmlspecialchars($user['role']) ?></span>
            </span>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Dashboard</h1>
        <p class="meta">Balance: <strong>$<?= number_format($balance, 2) ?></strong> &nbsp;|&nbsp; Welcome back, <?= htmlspecialchars($user['username']) ?></p>

        <div class="grid">
            <div class="card">
                <h2>Lab 1 — IDOR</h2>
                <p>Insecure Direct Object Reference. View orders by ID without ownership check.</p>
                <a href="A012025-Broken-Access-Control/lab1-idor/orders.php">Open Lab 1</a>
                <p class="vuln">Vulnerable: order.php?id=N</p>
            </div>

            <div class="card">
                <h2>Lab 2 — Missing Authorization</h2>
                <p>Admin panel reachable by any authenticated user. Forced browsing + missing role check.</p>
                <a href="A012025-Broken-Access-Control/lab2-missing-auth/admin.php">Open Lab 2</a>
                <p class="vuln">Vulnerable: /admin.php &amp; delete_user.php</p>
            </div>

            <div class="card">
                <h2>Lab 3 — Privilege Escalation</h2>
                <p>Parameter tampering (role), horizontal IDOR on profiles, vertical access to user list.</p>
                <a href="A012025-Broken-Access-Control/lab3-privilege/profile.php">Open Lab 3</a>
                <p class="vuln">Vulnerable: role=admin, profile?id=, /users.php</p>
            </div>

            <div class="card">
                <h2>Lab 4 — CSRF</h2>
                <p>Money transfer without CSRF token. Cookie-based auth only.</p>
                <a href="A012025-Broken-Access-Control/lab4-csrf/transfer.php">Open Lab 4</a>
                <p class="vuln">Vulnerable: transfer_action.php</p>
            </div>

            <div class="card">
                <h2>Bonus — CORS Misconfiguration</h2>
                <p>API reflects any Origin with credentials. Cross-origin data theft.</p>
                <a href="A012025-Broken-Access-Control/api/user.php">View API</a>
                <p class="vuln">Vulnerable: Access-Control-Allow-Origin reflects Origin</p>
            </div>

            <div class="card">
                <h2>Lab 5 — Force Browsing</h2>
                <p>Uploaded files accessible without authentication. Direct URL access to private files.</p>
                <a href="A012025-Broken-Access-Control/lab5-force-browsing/upload.php">Open Lab 5</a>
                <p class="vuln">Vulnerable: /uploads/&lt;filename&gt; direct access</p>
            </div>

            <div class="card">
                <h2>Lab 6 — JWT Signature Bypass</h2>
                <p>JWT tokens without signature verification. Tamper with tokens to escalate privileges.</p>
                <a href="A012025-Broken-Access-Control/lab6-jwt/login_vulnerable.php">Open Lab 6</a>
                <p class="vuln">Vulnerable: No signature verification on JWT decode</p>
            </div>
        </div>
    </div>
</body>
</html>