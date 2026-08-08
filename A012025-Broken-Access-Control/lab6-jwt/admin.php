<?php
/**
 * LAB 6 — Admin Page (JWT Protected)
 *
 * This page checks JWT token for admin role.
 * The vulnerability is in how the JWT is decoded (see login_vulnerable.php).
 */
session_start();

// Simple JWT implementation (VULNERABLE - no signature verification on decode)
class VulnerableJWT {
    private $secret = 'vulnerable-secret-key';
    
    public function decode($token) {
        $parts = explode('.', $token);
        
        // VULNERABLE: Does not verify signature!
        if (count($parts) >= 2) {
            $payload = json_decode($this->base64UrlDecode($parts[1]), true);
            return $payload;
        }
        
        return null;
    }
    
    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// Secure JWT implementation with signature verification
class SecureJWT {
    private $secret = 'secure-secret-key-change-in-production';
    
    public function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return ['error' => 'invalid_structure', 'message' => 'Invalid token structure'];
        }
        
        list($header, $payload, $signature) = $parts;
        
        // SECURE: Verify the signature
        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, $this->secret, true);
        $expectedSignatureBase64 = $this->base64UrlEncode($expectedSignature);
        
        if ($signature !== $expectedSignatureBase64) {
            return ['error' => 'invalid_signature', 'message' => 'Invalid JWT signature - token has been tampered with'];
        }
        
        // Signature is valid, decode payload
        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        return $decodedPayload;
    }
    
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

$vulnerableJwt = new VulnerableJWT();
$secureJwt = new SecureJWT();

// Check for vulnerable token first, then secure token
$token = $_COOKIE['jwt'] ?? $_GET['token'] ?? null;
$secureToken = $_COOKIE['jwt_secure'] ?? null;

$payload = null;
$isAdmin = false;
$isSecure = false;
$errorMessage = '';

if ($token) {
    $payload = $vulnerableJwt->decode($token);
    if ($payload && isset($payload['role']) && $payload['role'] === 'admin') {
        $isAdmin = true;
    }
} elseif ($secureToken) {
    $payload = $secureJwt->decode($secureToken);
    
    // Check for decoding errors
    if (isset($payload['error'])) {
        $errorMessage = $payload['message'];
        $payload = null;
    } elseif ($payload && isset($payload['role']) && $payload['role'] === 'admin') {
        $isAdmin = true;
        $isSecure = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page — Lab 6</title>
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
        .hint { background: #1e293b; border-left: 4px solid #f59e0b; padding: 1rem; margin: 1.25rem 0; border-radius: 0 8px 8px 0; font-size: .85rem; color: #fbbf24; }
        .token-display { background: #0f172a; padding: 1rem; border-radius: 8px; font-family: monospace; font-size: .8rem; word-break: break-all; margin: 1rem 0; border: 1px solid #334155; }
        .links { margin-top: 1.5rem; font-size: .9rem; }
        .links a { color: #3b82f6; margin-right: 1rem; }
        .warn { background: #7f1d1d; color: #fecaca; padding: .75rem; border-radius: 8px; margin-top: 1rem; font-size: .85rem; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 6 — Admin Page</span>
        <div>
            <a href="/A012025-Broken-Access-Control/lab6-jwt/login_vulnerable.php">Login (Vulnerable)</a>
            <a href="/A012025-Broken-Access-Control/lab6-jwt/login_secure.php">Login (Secure)</a>
            <a href="/dashboard.php">Dashboard</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>Admin Panel</h1>

            <?php if ($isAdmin): ?>
                <div class="msg">
                    <strong>Access Granted!</strong><br>
                    Welcome, Admin! 
                    <?php if ($isSecure): ?>
                        You are using a secure JWT token with proper signature verification.
                    <?php else: ?>
                        You have successfully bypassed authentication by tampering with the JWT token.
                    <?php endif; ?>
                </div>

                <div class="token-display">
                    <strong>Your JWT Payload:</strong><br>
                    <?= htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) ?>
                </div>

                <?php if (!$isSecure): ?>
                    <div class="hint">
                        <strong>Vulnerability Exploited:</strong> The server decoded your JWT but <strong>did not verify the signature</strong>. This allowed you to modify the payload (role: "admin") and gain admin access.
                    </div>
                <?php else: ?>
                    <div class="hint" style="border-left-color: #22c55e; color: #86efac;">
                        <strong>Secure Token:</strong> The server verified your JWT signature and confirmed it was not tampered with. This is the correct way to handle JWTs.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="error">
                    <strong>Access Denied</strong><br>
                    You need an admin JWT token to access this page.
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
                        No JWT token found. Please login first.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="links">
            <a href="/A012025-Broken-Access-Control/lab6-jwt/login_vulnerable.php">Login (Vulnerable)</a>
            <a href="/A012025-Broken-Access-Control/lab6-jwt/login_secure.php">Login (Secure)</a>
        </div>
    </div>
</body>
</html>
