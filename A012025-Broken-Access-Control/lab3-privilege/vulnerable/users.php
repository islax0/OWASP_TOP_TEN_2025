<?php
/**
 * LAB 3 — Vertical privilege escalation (Vulnerable)
 *
 * /users.php is an admin-only page, but there is NO role check.
 * Any logged-in user can open it (forced browsing + missing authz).
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();

// ========== VULNERABLE: no admin check ==========
// FIXED:
// requireAdmin();

$pdo = getDB();
$users = $pdo->query("SELECT id, username, role, balance FROM users ORDER BY id")->fetchAll();
$current = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users — Lab 3</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 720px; margin: 2rem auto; padding: 0 1.5rem; }
        h1 { margin-bottom: .5rem; }
        .warn { background: #7f1d1d; color: #fecaca; padding: 1rem; border-radius: 8px; margin: 1.25rem 0; font-size: .9rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #334155; }
        th { color: #94a3b8; font-size: .8rem; text-transform: uppercase; }
        a { color: #3b82f6; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 3 — Users list</span>
        <div>
            <a href="profile.php">Profile</a>
            <a href="/dashboard.php">Dashboard</a>
        </div>
    </nav>
    <div class="container">
        <h1>All Users</h1>
        <p style="color:#94a3b8;">You are: <?= htmlspecialchars($current['username']) ?> (<?= htmlspecialchars($current['role']) ?>)</p>

        <?php if ($current['role'] !== 'admin'): ?>
            <div class="warn">
                <strong>Vertical privilege escalation!</strong>
                This page should be admin-only, but there is no <code>role == admin</code> check.
                Any authenticated user can browse here.
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr><th>ID</th><th>Username</th><th>Role</th><th>Balance</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td>$<?= number_format($u['balance'], 2) ?></td>
                        <td><a href="profile.php?id=<?= $u['id'] ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>