<?php
// Application configuration
define('DB_PATH', __DIR__ . '/database.sqlite');
define('BASE_URL', '/OWASP_TOP_TEN_2025'); // adjust if needed for your server

// Session settings
// NOTE: Secure + SameSite=None cookies are rejected by browsers over plain
// HTTP, which silently breaks login (every request looks logged-out).
// Use strict flags only when actually serving HTTPS.
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if ($isHttps) {
    ini_set('session.cookie_samesite', 'None');
    ini_set('session.cookie_secure', 1); // Required for SameSite=None
} else {
    // Local / lab use over plain HTTP
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', 0);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}