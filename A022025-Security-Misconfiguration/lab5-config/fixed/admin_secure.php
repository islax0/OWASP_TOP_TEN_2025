<?php
/**
 * LAB 5 — Vendor panel (SECURE VERSION)
 *
 * Fixed code:
 *   requireAdmin();  // default account deleted; shared-app admins only
 *   → no factory credentials anywhere (CWE-798 fixed).
 */
require_once __DIR__ . '/../../../auth.php';
requireAdmin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Panel (Secure)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 700px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 2rem; border: 1px solid #334155; max-width: 440px; margin: 0 auto; }
        .card h1 { margin-bottom: .5rem; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; }
        .ok { background: #14532d; color: #bbf7d0; padding: .75rem 1rem; border-radius: 8px; margin-top: 1rem; font-size: .875rem; }
        a { color: #60a5fa; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 5 — Defaults + Listing (SECURE)</span>
        <div>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>
    <div class="container"><div class="card">
    <h1>Acme Router Admin (Secure)</h1>
    <p>Status: <code>online</code> · admin: <code><?= htmlspecialchars($user['username']) ?></code></p>
    <div class="ok">FIXED: factory <code>admin/admin</code> account deleted — shared-app admins only. Backups moved outside webroot, listing off (<code>Options -Indexes</code>).</div>
</div></div></body>
</html>
