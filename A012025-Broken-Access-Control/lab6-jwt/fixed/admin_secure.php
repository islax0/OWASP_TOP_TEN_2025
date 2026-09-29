<?php
/**
 * LAB 6 — Admin Page (SECURE VERSION)
 *
 * This page ONLY accepts JWT tokens with valid signature verification.
 * It uses the SecureJWT class which verifies HMAC-SHA256 signatures.
 *
 * Key difference from admin.php:
 * - Does NOT accept the vulnerable 'jwt' cookie (no signature check)
 * - Only accepts 'jwt_secure' cookie (signature verified)
 * - Rejects tampered tokens
 */
session_start();

class SecureJWT {
    private $secret = 'secure-secret-key-change-in-production';

    public function decode($token) {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        list($header, $payload, $signature) = $parts;

        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, $this->secret, true);
        $expectedSignatureBase64 = $this->base64UrlEncode($expectedSignature);

        if (!hash_equals($expectedSignatureBase64, $signature)) {
            return null;
        }

        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);

        // Check expiration
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return null;
        }

        return $decodedPayload;
    }

    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

$secureJwt = new SecureJWT();

// ========== SECURE: only accept the secure token ==========
$secureToken = $_COOKIE['jwt_secure'] ?? $_GET['token'] ?? null;

$payload = null;
$isAdmin = false;
$errorMessage = '';

if ($secureToken) {
    $payload = $secureJwt->decode($secureToken);

    if ($payload === null) {
        $errorMessage = 'Invalid or tampered JWT token — signature verification failed';
    } elseif (isset($payload['role']) && $payload['role'] === 'admin') {
        $isAdmin = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page — Lab 6 (Secure)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 560px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .msg { background: #14532d; color: #bbf7d0; padding: .75rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .error { background: #7f1d1d; color: #fecaca; padding: .75rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .hint { background: #1e293b; border-left: 4px solid #22c55e; padding: 1rem; margin: 1.25rem 0; border-radius: 0 8px 8px 0; font-size: .85rem; color: #86efac; }
        .token-display { background: #0f172a; padding: 1rem; border-radius: 8px; font-family: monospace; font-size: .8rem; word-break: break-all; margin: 1rem 0; border: 1px solid #334155; }
        .links { margin-top: 1.5rem; font-size: .9rem; }
        .links a { color: #3b82f6; margin-right: 1rem; }
        .warn { background: #7f1d1d; color: #fecaca; padding: .75rem; border-radius: 8px; margin-top: 1rem; font-size: .85rem; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 6 — Admin Page (SECURE)</span>
        <div>
            <a href="../fixed/login_secure.php">Login (Secure)</a>
            <a href="../vulnerable/admin.php">Vulnerable Admin</a>
            <a href="/dashboard.php">Dashboard</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>Admin Panel (Secure)</h1>

            <?php if ($isAdmin): ?>
                <div class="msg">
                    <strong>Access Granted!</strong><br>
                    Welcome, Admin! Your JWT signature was verified successfully.
                </div>

                <div class="token-display">
                    <strong>Your JWT Payload:</strong><br>
                    <?= htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) ?>
                </div>

                <div class="hint">
                    <strong>Secure Implementation:</strong> This page only accepts JWT tokens with valid
                    HMAC-SHA256 signatures. Tampered tokens are rejected. Token expiration is also checked.
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>Access Denied</strong><br>
                    You need a valid admin JWT token with verified signature to access this page.
                </div>

                <?php if ($errorMessage): ?>
                    <div class="warn">
                        <strong>JWT Error:</strong> <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php elseif ($payload): ?>
                    <div class="token-display">
                        <strong>Your Current Payload:</strong><br>
                        <?= htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) ?>
                    </div>

                    <div class="warn">
                        <strong>Your role:</strong> <?= htmlspecialchars($payload['role'] ?? 'unknown') ?><br>
                        <strong>Required role:</strong> admin
                    </div>
                <?php else: ?>
                    <div class="warn">
                        No secure JWT token found. Please login using the secure login page.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="links">
            <a href="../fixed/login_secure.php">Login (Secure)</a>
            <a href="../vulnerable/admin.php">Vulnerable Admin Page</a>
        </div>
    </div>
</body>
</html>
