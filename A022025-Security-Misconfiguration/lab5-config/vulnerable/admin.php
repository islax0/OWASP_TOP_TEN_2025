<?php
/**
 * LAB 5 — Vendor panel with default credentials (Vulnerable)
 *
 * Vulnerable code:
 *   if ($u === 'admin' && $p === 'admin') // factory default, never changed
 *   → anyone who reads the manual owns the panel (CWE-798, CWE-16).
 *
 * Fix (see ../fixed/admin_secure.php):
 *   default account removed; shared-app admins only (requireAdmin).
 */
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    // ========== VULNERABLE: factory default credentials ==========
    if ($u === 'admin' && $p === 'admin') {
        $_SESSION['lab5_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $error = 'Invalid credentials';
}
$authed = !empty($_SESSION['lab5_admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Panel (Lab 5)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 700px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 2rem; border: 1px solid #334155; max-width: 440px; margin: 0 auto; }
        .card h1 { margin-bottom: .5rem; }
        input { width: 100%; padding: .65rem .85rem; border: 1px solid #334155; border-radius: 8px; background: #0f172a; color: #f1f5f9; margin-bottom: 1rem; }
        button { width: 100%; padding: .75rem; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 600; }
        .error { background: #7f1d1d; color: #fecaca; padding: .75rem; border-radius: 8px; margin-bottom: 1rem; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; }
        a { color: #60a5fa; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 5 — Defaults + Listing</span>
        <div>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>
    <div class="container"><div class="card">
    <?php if (!$authed): ?>
        <h1>Acme Router Admin</h1>
        <p style="color:#94a3b8;margin-bottom:1rem">Firmware v2.1. Factory settings.</p>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="text" name="username" required placeholder="username">
            <input type="password" name="password" required placeholder="password">
            <button type="submit">Login</button>
        </form>
    <?php else: ?>
        <h1>Acme Router Admin</h1>
        <p>Status: <code>online</code> · Uptime: <code>14 days</code></p>
        <p style="margin-top:1rem">Config backups: <a href="files/">files/</a> (browse the directory)</p>
    <?php endif; ?>
</div></div></body>
</html>
