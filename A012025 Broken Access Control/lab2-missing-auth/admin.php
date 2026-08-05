<?php
/**
 * LAB 2 — Missing Authorization + Forced Browsing (Vulnerable)
 *
 * Only checks that the user is logged in, NOT that role == admin.
 * Any authenticated user can open this page (forced browsing).
 *
 * Fix:
 *   if ($_SESSION['role'] !== 'admin') {
 *       http_response_code(403);
 *       exit;
 *   }
 */
require_once __DIR__ . '/../../auth.php';

session_start(); // already started in config, but explicit for clarity

// ========== VULNERABLE: only checks login, not role ==========
if (!isset($_SESSION['user'])) {
    die('Not authenticated. <a href="../../login.php">Login</a>');
}

// ========== FIXED (commented) ==========
// if (($_SESSION['role'] ?? '') !== 'admin') {
//     http_response_code(403);
//     die('403 Forbidden — Admin only');
// }

$pdo = getDB();
$users = $pdo->query("SELECT id, username, role, balance FROM users ORDER BY id")->fetchAll();
$currentRole = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — Lab 2</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1.5rem; }
        h1 { margin-bottom: .5rem; }
        .warn { background: #7f1d1d; color: #fecaca; padding: 1rem; border-radius: 8px; margin: 1.25rem 0; font-size: .9rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #334155; }
        th { color: #94a3b8; font-size: .8rem; text-transform: uppercase; }
        .btn-del { background: #dc2626; color: white; border: none; padding: .35rem .75rem; border-radius: 6px; cursor: pointer; font-size: .8rem; text-decoration: none; display: inline-block; }
        .btn-del:hover { background: #b91c1c; }
        .badge { padding: .15rem .45rem; border-radius: 4px; font-size: .7rem; }
        .badge-admin { background: #7f1d1d; color: #fecaca; }
        .badge-user { background: #1e3a5f; color: #93c5fd; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 2 — Missing Authorization</span>
        <div>
            <a href="../../dashboard.php">Dashboard</a>
            <a href="../../logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h1>Admin Panel</h1>
        <p style="color:#94a3b8;">You are logged in as <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>
            (role: <span class="badge <?= $currentRole === 'admin' ? 'badge-admin' : 'badge-user' ?>"><?= htmlspecialchars($currentRole) ?></span>)</p>

        <?php if ($currentRole !== 'admin'): ?>
            <div class="warn">
                <strong>Missing Authorization!</strong> You are a normal user but can still access the admin panel.
                This page only checked <code>isset($_SESSION['user'])</code> and forgot to verify <code>role == admin</code>.
            </div>
        <?php else: ?>
            <div class="warn" style="background:#14532d;color:#bbf7d0;">
                You are admin — access is expected. Try logging in as <code>user1</code> and open this URL directly.
            </div>
        <?php endif; ?>

        <h2 style="margin-top:1.5rem;font-size:1.1rem;">All Users</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Username</th><th>Role</th><th>Balance</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                        <td>$<?= number_format($u['balance'], 2) ?></td>
                        <td>
                            <?php if ($u['username'] !== 'admin'): ?>
                                <a class="btn-del" href="delete_user.php?id=<?= $u['id'] ?>"
                                   onclick="return confirm('Delete user <?= htmlspecialchars($u['username']) ?>?')">Delete</a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>