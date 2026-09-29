<?php
/**
 * LAB 3 — "Remember me" cookie (SECURE VERSION)
 *
 * Fixed code:
 *   $token = base64(user) . '.' . base64(expiry) . '.' . HMAC(key, ...);
 *   setcookie(..., ['secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
 *   → signed + expiring, no password anywhere (CWE-315/614/1004 fixed).
 *   Server key comes from env, never from the client.
 */
require_once __DIR__ . '/../../../auth.php';

function lab3_key(): string {
    $k = getenv('LAB3_REMEMBER_KEY');
    if ($k === false || strlen($k) < 16) {
        // Demo fallback (NOT for production — set LAB3_REMEMBER_KEY env var)
        $k = 'demo-only-remember-key-change-me';
    }
    return $k;
}

function lab3_issue(string $username): string {
    $exp = time() + 86400 * 30;
    $p1 = base64_encode($username);
    $p2 = base64_encode((string)$exp);
    $sig = hash_hmac('sha256', "$p1.$p2", lab3_key());
    return "$p1.$p2.$sig";
}

/** @return ?string username if the token is valid and fresh */
function lab3_verify(string $token): ?string {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$p1, $p2, $sig] = $parts;
    if (!hash_equals(hash_hmac('sha256', "$p1.$p2", lab3_key()), $sig)) return null;
    $exp = (int)base64_decode($p2, true);
    if ($exp < time()) return null; // expired
    $u = base64_decode($p1, true);
    return $u !== false && $u !== '' ? $u : null;
}

$error = '';

// Auto-login from SIGNED token (lookup user server-side, never trust a password)
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_secure'])) {
    $u = lab3_verify($_COOKIE['remember_secure']);
    if ($u !== null) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$u]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (loginUser($u, $p)) {
        if (!empty($_POST['remember'])) {
            // ========== SECURE: signed token + hardened flags ==========
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            setcookie('remember_secure', lab3_issue($u), [
                'expires' => time() + 86400 * 30,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        header('Location: remember_secure.php');
        exit;
    }
    $error = 'Invalid username or password';
}

// Already logged in via the shared session? Stay on the page and show
// status instead of bouncing away — otherwise the Remember-me form
// below is unreachable and no cookie ever gets set.
$labUser = $_SESSION['user'] ?? null;
$hasCookie = isset($_COOKIE['remember_secure']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login + Remember (Secure)</title>
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
        .ok { background: #14532d; color: #bbf7d0; padding: .75rem; border-radius: 8px; margin-top: 1rem; font-size: .875rem; }
        label.cb { display: block; margin-bottom: 1rem; font-size: .875rem; color: #94a3b8; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 3 — Cookies (SECURE)</span>
        <div>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>
    <div class="container"><div class="card">
    <h1>Lab 3 — Login (Secure)</h1>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($labUser): ?>
        <p style="background:#1e3a5f;color:#93c5fd;padding:.75rem;border-radius:8px;margin-bottom:1rem">Logged in as <b><?= htmlspecialchars($labUser) ?></b> (shared session).</p>
        <p style="color:#94a3b8;font-size:.85rem;margin-bottom:1rem"><code>remember_secure</code> cookie: <b><?= $hasCookie ? 'PRESENT — note the Secure/HttpOnly flags in devtools' : 'absent — submit the form below with Remember me checked' ?></b></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" required placeholder="username">
        <input type="password" name="password" required placeholder="password">
        <label class="cb"><input type="checkbox" name="remember" value="1" style="width:auto"> Remember me for 30 days</label>
        <button type="submit">Login</button>
    </form>
    <div class="ok">Secure cookie: signed expiring token (no password) + HttpOnly + SameSite.</div>
</div></div></body>
</html>
