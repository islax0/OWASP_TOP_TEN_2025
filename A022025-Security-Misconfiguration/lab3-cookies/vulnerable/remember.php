<?php
/**
 * LAB 3 — "Remember me" cookie (Vulnerable)
 *
 * Vulnerable code:
 *   setcookie('remember', base64_encode("$user:$pass"), time()+86400*30);
 *   → cleartext password in a persistent cookie, no Secure/HttpOnly/SameSite
 *     (CWE-315, CWE-614, CWE-1004). Anyone reading the cookie owns the account.
 *
 * Fix (see ../fixed/remember_secure.php):
 *   signed, expiring token + Secure + HttpOnly + SameSite. Never the password.
 */
require_once __DIR__ . '/../../../auth.php';

$error = '';

// Auto-login from cookie (the vulnerability: cookie == credentials)
if (!isset($_SESSION['user']) && isset($_COOKIE['remember'])) {
    $raw = base64_decode($_COOKIE['remember'] ?? '', true);
    if ($raw !== false && str_contains($raw, ':')) {
        [$u, $p] = explode(':', $raw, 2);
        loginUser($u, $p); // password straight out of the cookie
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (loginUser($u, $p)) {
        if (!empty($_POST['remember'])) {
            // ========== VULNERABLE: password in cookie, zero flags ==========
            setcookie('remember', base64_encode("$u:$p"), time() + 86400 * 30);
        }
        header('Location: remember.php');
        exit;
    }
    $error = 'Invalid username or password';
}

// Already logged in via the shared session? Stay on the page and show
// status instead of bouncing away — otherwise the Remember-me form
// below is unreachable and no cookie ever gets set.
$labUser = $_SESSION['user'] ?? null;
$hasCookie = isset($_COOKIE['remember']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login + Remember (Lab 3)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 700px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 2rem; border: 1px solid #334155; max-width: 440px; margin: 0 auto; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; }
        .card h1 { margin-bottom: .5rem; }
        input { width: 100%; padding: .65rem .85rem; border: 1px solid #334155; border-radius: 8px; background: #0f172a; color: #f1f5f9; margin-bottom: 1rem; }
        button { width: 100%; padding: .75rem; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 600; }
        .error { background: #7f1d1d; color: #fecaca; padding: .75rem; border-radius: 8px; margin-bottom: 1rem; }
        label.cb { display: block; margin-bottom: 1rem; font-size: .875rem; color: #94a3b8; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 3 — Cookies</span>
        <div>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>
    <div class="container"><div class="card">
    <h1>Lab 3 — Login</h1>
    <p style="color:#94a3b8;margin-bottom:1rem">Try <code>user1 / 123456</code> + Remember me, then inspect your cookies.</p>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($labUser): ?>
        <p style="background:#1e3a5f;color:#93c5fd;padding:.75rem;border-radius:8px;margin-bottom:1rem">Logged in as <b><?= htmlspecialchars($labUser) ?></b> (shared session).</p>
        <p style="color:#94a3b8;font-size:.85rem;margin-bottom:1rem"><code>remember</code> cookie: <b><?= $hasCookie ? 'PRESENT — open devtools → Application → Cookies and decode it' : 'absent — submit the form below with Remember me checked' ?></b></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" required placeholder="username">
        <input type="password" name="password" required placeholder="password">
        <label class="cb"><input type="checkbox" name="remember" value="1" style="width:auto"> Remember me for 30 days</label>
        <button type="submit">Login</button>
    </form>
</div></div></body>
</html>
