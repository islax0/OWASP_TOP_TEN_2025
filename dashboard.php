<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$user = currentUser();
$pdo = getDB();

$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$balance = $stmt->fetchColumn();

$categories = [
    [
        'title' => 'A01:2025 — Broken Access Control',
        'labs' => [
            [
                'name' => 'Lab 1 — IDOR',
                'desc' => 'Insecure Direct Object Reference. View orders by ID without ownership check.',
                'vuln' => 'A012025-Broken-Access-Control/lab1-idor/vulnerable/order.php?id=3',
                'vuln_label' => 'Vulnerable: order.php?id=N',
                'secure' => 'A012025-Broken-Access-Control/lab1-idor/fixed/order_secure.php?id=9',
                'secure_label' => 'Secure: order_secure.php',
            ],
            [
                'name' => 'Lab 2 — Missing Authorization',
                'desc' => 'Admin panel reachable by any authenticated user. Forced browsing + missing role check.',
                'vuln' => 'A012025-Broken-Access-Control/lab2-missing-auth/vulnerable/admin.php',
                'vuln_label' => 'Vulnerable: admin.php & delete_user.php',
                'secure' => 'A012025-Broken-Access-Control/lab2-missing-auth/fixed/admin_secure.php',
                'secure_label' => 'Secure: admin_secure.php',
            ],
            [
                'name' => 'Lab 3 — Privilege Escalation',
                'desc' => 'Parameter tampering (role), horizontal IDOR on profiles, vertical access to user list.',
                'vuln' => 'A012025-Broken-Access-Control/lab3-privilege/vulnerable/profile.php',
                'vuln_label' => 'Vulnerable: role=admin, profile?id=, users.php',
                'secure' => 'A012025-Broken-Access-Control/lab3-privilege/fixed/profile_secure.php',
                'secure_label' => 'Secure: profile_secure.php',
            ],
            [
                'name' => 'Lab 4 — CSRF',
                'desc' => 'Money transfer without CSRF token. Cookie-based auth only.',
                'vuln' => 'A012025-Broken-Access-Control/lab4-csrf/vulnerable/transfer.php',
                'vuln_label' => 'Vulnerable: transfer_action.php',
                'secure' => 'A012025-Broken-Access-Control/lab4-csrf/fixed/transfer_secure.php',
                'secure_label' => 'Secure: transfer_secure.php',
            ],
            [
                'name' => 'Lab 5 — Force Browsing',
                'desc' => 'Uploaded files accessible without authentication. Direct URL access to private files.',
                'vuln' => 'A012025-Broken-Access-Control/lab5-force-browsing/vulnerable/upload.php',
                'vuln_label' => 'Vulnerable: /uploads/ direct access',
                'secure' => 'A012025-Broken-Access-Control/lab5-force-browsing/fixed/upload_secure.php',
                'secure_label' => 'Secure: upload_secure.php',
            ],
            [
                'name' => 'Lab 6 — JWT Signature Bypass',
                'desc' => 'JWT tokens without signature verification. Tamper with tokens to escalate privileges.',
                'vuln' => 'A012025-Broken-Access-Control/lab6-jwt/vulnerable/login_vulnerable.php',
                'vuln_label' => 'Vulnerable: No signature verification',
                'secure' => 'A012025-Broken-Access-Control/lab6-jwt/fixed/login_secure.php',
                'secure_label' => 'Secure: login_secure.php',
            ],
        ],
    ],
    [
        'title' => 'A02:2025 — Security Misconfiguration',
        'labs' => [
            [
                'name' => 'Lab 1 — Secrets / Credentials',
                'desc' => 'Hard-coded secrets in config, env dump via debug endpoint, .env and backup files in webroot.',
                'vuln' => 'A022025-Security-Misconfiguration/lab1-secrets/vulnerable/info.php',
                'vuln_label' => 'Vulnerable: info.php + status.php',
                'secure' => 'A022025-Security-Misconfiguration/lab1-secrets/fixed/info_secure.php',
                'secure_label' => 'Secure: info_secure.php',
            ],
            [
                'name' => 'Lab 2 — Debug / Dev Config',
                'desc' => 'Unauthenticated phpinfo, verbose errors leaking paths.',
                'vuln' => 'A022025-Security-Misconfiguration/lab2-debug/vulnerable/debug.php',
                'vuln_label' => 'Vulnerable: debug.php & errors.php',
                'secure' => 'A022025-Security-Misconfiguration/lab2-debug/fixed/debug_secure.php',
                'secure_label' => 'Secure: 404 + logged errors',
            ],
            [
                'name' => 'Lab 3 — Insecure Cookies',
                'desc' => 'Cleartext password in remember-me cookie, no Secure/HttpOnly/SameSite.',
                'vuln' => 'A022025-Security-Misconfiguration/lab3-cookies/vulnerable/remember.php',
                'vuln_label' => 'Vulnerable: remember.php',
                'secure' => 'A022025-Security-Misconfiguration/lab3-cookies/fixed/remember_secure.php',
                'secure_label' => 'Secure: signed token cookie',
            ],
            [
                'name' => 'Lab 4 — XXE',
                'desc' => 'XML import resolves external entities: file read + entity-expansion DoS.',
                'vuln' => 'A022025-Security-Misconfiguration/lab4-xml/vulnerable/parse.php',
                'vuln_label' => 'Vulnerable: parse.php',
                'secure' => 'A022025-Security-Misconfiguration/lab4-xml/fixed/parse_secure.php',
                'secure_label' => 'Secure: entities off',
            ],
            [
                'name' => 'Lab 5 — Defaults + Listing',
                'desc' => 'Factory admin/admin panel, browsable backup directory with DB dump.',
                'vuln' => 'A022025-Security-Misconfiguration/lab5-config/vulnerable/admin.php',
                'vuln_label' => 'Vulnerable: admin.php (admin/admin)',
                'secure' => 'A022025-Security-Misconfiguration/lab5-config/fixed/admin_secure.php',
                'secure_label' => 'Secure: admins only',
            ],
            [
                'name' => 'Lab 6 — CORS + Headers',
                'desc' => 'Reflected-Origin CORS theft, clickjacking, missing hardening headers.',
                'vuln' => 'A022025-Security-Misconfiguration/lab6-headers/vulnerable/data.php',
                'vuln_label' => 'Vulnerable: data.php & account.php',
                'secure' => 'A022025-Security-Misconfiguration/lab6-headers/fixed/data_secure.php',
                'secure_label' => 'Secure: allowlist + headers',
            ],
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — OWASP Top 10 Labs</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1.25rem; }
        nav a:hover { color: #f1f5f9; }
        .brand { font-weight: 700; color: #f1f5f9; }
        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }
        h1 { font-size: 1.75rem; margin-bottom: .5rem; }
        .meta { color: #94a3b8; margin-bottom: 2rem; }
        .category { margin-bottom: 2.5rem; }
        .category-title { font-size: 1.25rem; color: #f1f5f9; margin-bottom: 1rem; padding-bottom: .5rem; border-bottom: 2px solid #334155; }
        .category-desc { color: #94a3b8; font-size: .875rem; margin-bottom: 1.25rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; transition: border-color .2s; }
        .card:hover { border-color: #3b82f6; }
        .card h2 { font-size: 1.1rem; margin-bottom: .5rem; }
        .card p { color: #94a3b8; font-size: .875rem; margin-bottom: 1rem; line-height: 1.5; }
        .links { display: flex; flex-direction: column; gap: .5rem; }
        .links a { display: inline-block; padding: .5rem 1rem; border-radius: 6px; text-decoration: none; font-size: .875rem; font-weight: 500; text-align: center; }
        .links a.vuln-link { background: #7f1d1d; color: #fecaca; }
        .links a.vuln-link:hover { background: #991b1b; }
        .links a.secure-link { background: #14532d; color: #bbf7d0; }
        .links a.secure-link:hover { background: #166534; }
        .badge { display: inline-block; padding: .2rem .5rem; border-radius: 4px; font-size: .7rem; font-weight: 600; text-transform: uppercase; }
        .badge-admin { background: #7f1d1d; color: #fecaca; }
        .badge-user { background: #1e3a5f; color: #93c5fd; }
    </style>
</head>
<body>
    <nav>
        <span class="brand">OWASP Top 10 Labs</span>
        <div>
            <span><?= htmlspecialchars($user['username']) ?>
                <span class="badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>"><?= htmlspecialchars($user['role']) ?></span>
            </span>
            <a href="/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Dashboard</h1>
        <p class="meta">Balance: <strong>$<?= number_format($balance, 2) ?></strong> &nbsp;|&nbsp; Welcome back, <?= htmlspecialchars($user['username']) ?></p>

        <?php foreach ($categories as $cat): ?>
            <div class="category">
                <div class="category-title"><?= htmlspecialchars($cat['title']) ?></div>
                <div class="grid">
                    <?php foreach ($cat['labs'] as $lab): ?>
                        <div class="card">
                            <h2><?= htmlspecialchars($lab['name']) ?></h2>
                            <p><?= htmlspecialchars($lab['desc']) ?></p>
                            <div class="links">
                                <a class="vuln-link" href="<?= htmlspecialchars($lab['vuln']) ?>"><?= htmlspecialchars($lab['vuln_label']) ?></a>
                                <a class="secure-link" href="<?= htmlspecialchars($lab['secure']) ?>"><?= htmlspecialchars($lab['secure_label']) ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
