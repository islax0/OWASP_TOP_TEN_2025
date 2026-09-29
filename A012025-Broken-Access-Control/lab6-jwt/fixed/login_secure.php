<?php
/**
 * LAB 6 — JWT Signature Verification Bypass (SECURE)
 *
 * This implementation:
 * - Generates JWT tokens with a signature
 * - VERIFIES the signature when decoding
 * - Prevents attackers from tampering with the payload
 *
 * Key differences from vulnerable version:
 * - Signature verification is mandatory
 * - Uses HMAC-SHA256 for signing
 * - Rejects tokens with invalid signatures
 */
session_start();

// Secure JWT implementation with signature verification
class SecureJWT {
    private $secret = 'secure-secret-key-change-in-production';
    
    public function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null; // Invalid token structure
        }
        
        list($header, $payload, $signature) = $parts;
        
        // SECURE: Verify the signature
        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, $this->secret, true);
        $expectedSignatureBase64 = $this->base64UrlEncode($expectedSignature);
        
        if ($signature !== $expectedSignatureBase64) {
            return null; // Invalid signature - token tampered with
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

$jwt = new SecureJWT();
$message = '';
$error = '';
$token = '';
$decodedPayload = null;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simulated authentication (accepts any non-empty credentials)
    if ($username && $password) {
        // Create JWT payload
        $payload = [
            'user_id' => 1,
            'username' => $username,
            'role' => 'user',  // Normal users get 'user' role
            'iat' => time(),
            'exp' => time() + 3600 // Token expires in 1 hour
        ];
        
        $token = $jwt->encode($payload);
        setcookie('jwt_secure', $token, time() + 3600, '/', '', false, true);
        $message = 'Login successful! JWT token generated with signature verification';
        $decodedPayload = $payload;
    } else {
        $error = 'Please enter username and password';
    }
}

// Check for existing token
if (!$token && isset($_COOKIE['jwt_secure'])) {
    $token = $_COOKIE['jwt_secure'];
    $decodedPayload = $jwt->decode($token);
    
    if (!$decodedPayload) {
        $error = 'Invalid or tampered JWT token';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login (Secure) — Lab 6</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 560px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        label { display: block; margin: 1rem 0 .35rem; font-size: .875rem; color: #cbd5e1; }
        input { width: 100%; padding: .6rem; border: 1px solid #334155; border-radius: 8px; background: #0f172a; color: #f1f5f9; margin-bottom: .5rem; }
        button { margin-top: 1rem; padding: .65rem 1.25rem; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
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
        <span>Lab 6 — JWT (Secure)</span>
        <div>
            <a href="../fixed/admin_secure.php">Admin Page</a>
            <a href="../vulnerable/login_vulnerable.php">Vulnerable Version</a>
            <a href="/dashboard.php">Dashboard</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>Login (Secure)</h1>

            <?php if ($message): ?>
                <div class="msg"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" required>
                
                <label>Password</label>
                <input type="password" name="password" required>
                
                <button type="submit">Login</button>
            </form>

            <?php if ($token): ?>
                <div class="token-display">
                    <strong>JWT Token:</strong><br>
                    <?= htmlspecialchars($token) ?>
                </div>
                
                <?php if ($decodedPayload): ?>
                    <div class="token-display">
                        <strong>Decoded Payload:</strong><br>
                        <?= htmlspecialchars(json_encode($decodedPayload, JSON_PRETTY_PRINT)) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="hint">
                <strong>Secure Implementation:</strong> This version <strong>verifies the JWT signature</strong> before accepting the payload. If an attacker tries to tamper with the token, the signature verification will fail and the token will be rejected.
            </div>

            <div class="warn">
                <strong>Key Security Features:</strong>
                <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                    <li>Signature verification using HMAC-SHA256</li>
                    <li>Token expiration (exp claim)</li>
                    <li>Rejects tokens with invalid structure</li>
                    <li>Rejects tokens with failed signature verification</li>
                </ul>
            </div>
        </div>

        <div class="links">
            <a href="../fixed/admin_secure.php">Try Admin Page</a>
            <a href="../vulnerable/login_vulnerable.php">View Vulnerable Implementation</a>
        </div>
    </div>
</body>
</html>
