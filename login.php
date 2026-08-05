<?php
require_once __DIR__ . '/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (loginUser($username, $password)) {
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid username or password';
}

// Already logged in?
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Broken Access Control Labs</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #1e293b; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 25px 50px -12px rgba(0,0,0,.5); }
        h1 { font-size: 1.5rem; margin-bottom: .5rem; }
        p.sub { color: #94a3b8; margin-bottom: 1.5rem; font-size: .9rem; }
        label { display: block; margin-bottom: .35rem; font-size: .875rem; color: #cbd5e1; }
        input { width: 100%; padding: .65rem .85rem; border: 1px solid #334155; border-radius: 8px; background: #0f172a; color: #f1f5f9; margin-bottom: 1rem; }
        input:focus { outline: none; border-color: #3b82f6; }
        button { width: 100%; padding: .75rem; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        button:hover { background: #2563eb; }
        .error { background: #7f1d1d; color: #fecaca; padding: .75rem; border-radius: 8px; margin-bottom: 1rem; font-size: .875rem; }
        .creds { margin-top: 1.5rem; padding: 1rem; background: #0f172a; border-radius: 8px; font-size: .8rem; color: #94a3b8; }
        .creds code { color: #38bdf8; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Broken Access Control Labs</h1>
        <p class="sub">Login to start the labs</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required autofocus placeholder="admin / user1 / user2">

            <label>Password</label>
            <input type="password" name="password" required placeholder="123456">

            <button type="submit">Login</button>
        </form>

        <div class="creds">
            <strong>Demo accounts</strong><br>
            <code>admin</code> / <code>123456</code> (admin)<br>
            <code>user1</code> / <code>123456</code> (user)<br>
            <code>user2</code> / <code>123456</code> (user)
        </div>
    </div>
</body>
</html>