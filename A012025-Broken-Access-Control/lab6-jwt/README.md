# Lab 6 — JWT Signature Verification Bypass

**Goal:** Bypass authentication by tampering with a JWT token when signature verification is missing or flawed.

## Files

- Vulnerable: `/lab6-jwt/vulnerable/login_vulnerable.php` — JWT without signature verification
- Secure: `/lab6-jwt/fixed/login_secure.php` — JWT with proper signature verification
- Admin page: `/lab6-jwt/vulnerable/admin.php` — Protected resource (`fixed/admin_secure.php`)

## Vulnerability

JWT (JSON Web Tokens) consist of three parts: header, payload, and signature. The signature ensures the token hasn't been tampered with. If the application doesn't verify the signature, attackers can:

- Modify the payload (e.g., change role from "user" to "admin")
- Remove the signature entirely
- Sign with their own secret key

## Attack

### Using the Vulnerable Implementation

1. Login via `/lab6-jwt/vulnerable/login_vulnerable.php` with any credentials
2. Receive a JWT token in the response
3. Decode the JWT (base64url decode the payload)
4. Modify the payload to change `"role": "user"` to `"role": "admin"`
5. Re-encode the payload (base64url encode)
6. Reconstruct the JWT with the modified payload
7. Access `/lab6-jwt/vulnerable/admin.php` with the tampered token
8. Gain admin access without proper authentication

### Example JWT Manipulation

**Original token:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJyb2xlIjoidXNlciJ9.signature
```

**Modified token (admin role):**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJyb2xlIjoiYWRtaW4ifQ.signature
```

Or simply remove the signature:
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJyb2xlIjoiYWRtaW4ifQ.
```

## Vulnerable Code

```php
// VULNERABLE: Does not verify signature
$token = $_COOKIE['jwt'] ?? $_GET['token'] ?? '';
$parts = explode('.', $token);
$payload = json_decode(base64_decode($parts[1]), true);

// No signature verification!
// Trusts the payload blindly
if ($payload['role'] === 'admin') {
    // Grant admin access
}
```

## Fix

Always verify the JWT signature using the secret key:

```php
// SECURE: Verifies signature
$secret = 'your-secret-key';
$token = $_COOKIE['jwt'] ?? $_GET['token'] ?? '';

try {
    $decoded = JWT::decode($token, new Key($secret, 'HS256'));
    $payload = (array)$decoded;
    
    if ($payload['role'] === 'admin') {
        // Grant admin access
    }
} catch (Exception $e) {
    http_response_code(401);
    exit('Invalid token');
}
```

## Additional Security Measures

- Use strong, random secret keys (at least 256 bits)
- Rotate secret keys periodically
- Implement token expiration (`exp` claim)
- Use strong algorithms (HS256, RS256)
- Validate all claims (issuer, audience, expiration)
- Store tokens securely (HttpOnly, Secure cookies)
- Implement token revocation/blacklisting

## Testing Tools

- JWT Debugger: https://jwt.io/
- jwt_tool (Python): https://github.com/ticarpi/jwt_tool
- Burp Suite JWT extension

## Key Takeaways

- **Never trust JWT payloads without signature verification**
- Signature verification is the security mechanism that prevents tampering
- Missing or flawed signature verification = complete authentication bypass
- Always use established JWT libraries (don't implement manually)
