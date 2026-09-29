<?php
/**
 * LAB 6 — Account page (SECURE VERSION)
 *
 * Fixed: framing denied, MIME sniffing off, referrer trimmed, CSP baseline.
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();
$user = currentUser();

// ========== SECURE: hardening headers ==========
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Account Settings (Secure)</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #1e293b; padding: 2rem; border-radius: 12px; width: 100%; max-width: 440px; }
        .ok { background: #14532d; color: #bbf7d0; padding: .75rem 1rem; border-radius: 8px; margin-top: 1rem; font-size: .875rem; }
        .err { background: #7f1d1d; color: #fecaca; padding: 2rem; border-radius: 12px; width: 100%; max-width: 440px; text-align: center; display: none; }
        a { color: #60a5fa; }
    </style>
</head>
<body>
<div class="card" id="acct">
    <h1>Account: <?= htmlspecialchars($user['username']) ?> (Secure)</h1>
    <div class="ok">FIXED: page cannot be framed (<code>frame-ancestors 'none'</code>), MIME sniffing off, destructive action moved behind confirm + CSRF token (see A01 Lab 4).</div>
    <p style="margin-top:1rem"><a href="/dashboard.php">Labs dashboard</a></p>
</div>
<div class="err" id="framed-error">
    <h1>Blocked</h1>
    <p>This page refused to load inside a frame<br>(X-Frame-Options: DENY).</p>
</div>
<script>
// Layer 2 (headers are layer 1): if framed despite the headers
// (old browser, header-stripping proxy), hide the account, show the error.
if (window.self !== window.top) {
    document.getElementById('acct').style.display = 'none';
    document.getElementById('framed-error').style.display = 'block';
}
</script>
</body>
</html>
