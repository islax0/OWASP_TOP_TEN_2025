<?php
/**
 * LAB 2 — Verbose errors in production (Vulnerable)
 *
 * Vulnerable code:
 *   ini_set('display_errors', 1);  // stack traces + paths to users
 *   → path disclosure, query/SQL fragments in errors (CWE-209, CWE-215)
 *
 * Fix (see ../fixed/errors_secure.php):
 *   display_errors=0, log_errors=1 — users see a generic message.
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();
$user = currentUser();

// ========== VULNERABLE: errors rendered into the page ==========
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Profile (Lab 2)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 700px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
    </style>
</head>
<body>
    <nav><span>Lab 2 — Debug</span>
        <div><a href="/dashboard.php">Dashboard</a><a href="/logout.php">Logout</a></div>
    </nav>
    <div class="container"><div class="card">
        <h1>Balance for <?= htmlspecialchars($user['username']) ?></h1>
        <?php
        // Dev leftover: divides by a request parameter with no validation.
        // ?div=0 triggers a visible warning/fatal leaking the full path.
        $div = $_GET['div'] ?? 1;
        echo '<p>Result: ' . (100 / $div) . '</p>';
        // Dev leftover: undefined variable notice leaks internals.
        echo '<p>Nickname: ' . $profile['nickname'] . '</p>';
        ?>
    </div></div>
</body>
</html>
