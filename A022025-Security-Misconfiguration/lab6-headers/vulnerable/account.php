<?php
/**
 * LAB 6 — Account page with no framing / content-type protection (Vulnerable)
 *
 * Vulnerable: no X-Frame-Options / CSP frame-ancestors (clickjacking, CWE-1021),
 * no X-Content-Type-Options (MIME sniffing), no Referrer-Policy.
 *
 * Fix (see ../fixed/account_secure.php): DENY + nosniff + CSP + Referrer-Policy.
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();
$user = currentUser();

// ========== VULNERABLE: zero hardening headers ==========
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Account Settings (Lab 6)</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #1e293b; padding: 2rem; border-radius: 12px; width: 100%; max-width: 440px; }
        button { padding: .75rem 1.5rem; background: #dc2626; color: white; border: none; border-radius: 8px; font-weight: 600; margin-top: 1rem; }
        a { color: #60a5fa; }
    </style>
</head>
<body><div class="card">
    <h1>Account: <?= htmlspecialchars($user['username']) ?></h1>
    <p style="color:#94a3b8;margin:.5rem 0">Danger zone (one click, no confirm):</p>
    <form method="POST" action="delete.php">
        <button type="submit">Delete my account</button>
    </form>
    <p style="margin-top:1rem"><a href="/dashboard.php">Labs dashboard</a></p>
</div></body>
</html>
