<?php
// Application configuration
define('DB_PATH', __DIR__ . '/database.sqlite');
define('BASE_URL', '/OWASP_TOP_TEN_2025'); // adjust if needed for your server

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 1); // Required for SameSite=None

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}