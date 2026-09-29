<?php
/**
 * LAB 2 — Errors (SECURE VERSION)
 *
 * Fixed code:
 *   ini_set('display_errors', 0); ini_set('log_errors', 1);
 *   → users see a generic message, details go to the server log.
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();
$user = currentUser();

// ========== SECURE: errors logged, never displayed ==========
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Profile (Secure)</title>
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
    <nav><span>Lab 2 — Debug (SECURE)</span>
        <div><a href="/dashboard.php">Dashboard</a><a href="/logout.php">Logout</a></div>
    </nav>
    <div class="container"><div class="card">
        <h1>Balance for <?= htmlspecialchars($user['username']) ?></h1>
        <?php
        $div = $_GET['div'] ?? 1;
        if (!is_numeric($div) || (float)$div == 0.0) {
            echo '<div class="ok">Invalid input. Please use a non-zero number.</div>';
        } else {
            echo '<p>Result: ' . (100 / (float)$div) . '</p>';
        }
        ?>
        <div class="ok">FIXED: input validated, errors logged server-side instead of displayed.</div>
    </div></div>
</body>
</html>
