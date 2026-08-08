<?php
/**
 * LAB 6 — JWT Signature Verification Bypass (VULNERABLE)
 *
 * This implementation:
 * - Generates JWT tokens with a signature
 * - DOES NOT verify the signature when decoding
 * - Allows attackers to tamper with the payload
 *
 * Fix:
 * - Always verify JWT signature using the secret key
 * - Use established JWT libraries (firebase/php-jwt)
 */
session_start();

// Simple JWT implementation (VULNERABLE - no signature verification on decode)
class VulnerableJWT {
    private $secret = 'vulnerable-secret-key';
    
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
        
        // VULNERABLE: Does not verify signature!
        // Just decodes the payload blindly
        if (count($parts) >= 2) {
            $payload = json_decode($this->base64UrlDecode($parts[1]), true);
            return $payload;
        }
        
        return null;
    }
    
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

$jwt = new VulnerableJWT();
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
            'iat' => time()
        ];
        
        $token = $jwt->encode($payload);
        setcookie('jwt', $token, time() + 3600, '/', '', false, true);
        $message = 'Login successful! JWT token generated (check cookie or response)';
        $decodedPayload = $payload;
    } else {
        $error = 'Please enter username and password';
    }
}

// Check for existing token
if (!$token && isset($_COOKIE['jwt'])) {
    $token = $_COOKIE['jwt'];
    $decodedPayload = $jwt->decode($token);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login (Vulnerable) — Lab 6</title>
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
        .hint { background: #1e293b; border-left: 4px solid #f59e0b; padding: 1rem; margin: 1.25rem 0; border-radius: 0 8px 8px 0; font-size: .85rem; color: #fbbf24; }
        .token-display { background: #0f172a; padding: 1rem; border-radius: 8px; font-family: monospace; font-size: .8rem; word-break: break-all; margin: 1rem 0; border: 1px solid #334155; }
        .links { margin-top: 1.5rem; font-size: .9rem; }
        .links a { color: #3b82f6; margin-right: 1rem; }
        .warn { background: #7f1d1d; color: #fecaca; padding: .75rem; border-radius: 8px; margin-top: 1rem; font-size: .85rem; }
    </style>
</head>
<body>
    <nav>
        <span>Lab 6 — JWT (Vulnerable)</span>
        <div>
            <a href="/A012025-Broken-Access-Control/lab6-jwt/admin.php">Admin Page</a>
            <a href="/A012025-Broken-Access-Control/lab6-jwt/login_secure.php">Secure Version</a>
            <a href="/dashboard.php">Dashboard</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>Login (Vulnerable)</h1>

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
                <strong>Vulnerability:</strong> The JWT is generated with a signature, but when decoded, the signature is <strong>NOT verified</strong>. You can tamper with the payload (e.g., change role to "admin") and the server will accept it.
            </div>

            <div class="warn">
                <strong>Attack Steps:</strong>
                <ol style="margin-left: 1.5rem; margin-top: 0.5rem;">
                    <li>Login to get a JWT token</li>
                    <li>Decode the payload (base64url)</li>
                    <li>Change "role": "user" to "role": "admin"</li>
                    <li>Re-encode the payload</li>
                    <li>Replace the token in your cookie</li>
                    <li>Access the admin page</li>
                </ol>
            </div>
        </div>

        <div class="links">
            <a href="/A012025-Broken-Access-Control/lab6-jwt/admin.php">Try Admin Page</a>
            <a href="/A012025-Broken-Access-Control/lab6-jwt/login_secure.php">View Secure Implementation</a>
        </div>
    </div>
</body>
</html>
