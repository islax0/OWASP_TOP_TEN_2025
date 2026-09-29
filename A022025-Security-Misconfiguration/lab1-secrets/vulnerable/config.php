<?php
/**
 * LAB 1 — Secrets / Credentials Exposure (Vulnerable)
 *
 * Vulnerable code:
 *   define('SMTP_PASSWORD', 'Smtp$ecret2024!');
 *   define('STRIPE_SECRET_KEY', 'sk_live_51H7...');
 *   → hard-coded secrets committed to webroot (CWE-260, CWE-547)
 *
 * Fix (see ../fixed/config_secure.php):
 *   secrets come from env/vault via env_or_fail(), never in code.
 */
define('SMTP_HOST', 'smtp.internal.acme.local');
define('SMTP_USER', 'support@acme.local');
define('SMTP_PASSWORD', 'Smtp$ecret2024!');

define('STRIPE_SECRET_KEY', 'sk_live_51H7xY9QaBcDeFgHiJkLmNoPqRs');
define('JWT_SECRET', 'my-super-secret-jwt-key-123');
define('ENCRYPTION_KEY', '1234567890123456'); // 16 bytes, hardcoded

// CWE-526 setup: secret pushed into environment, dumped by status.php
putenv('APP_MASTER_KEY=master-key-9f8b7a6c5d4e3f2a1b0c');
$_ENV['APP_MASTER_KEY'] = 'master-key-9f8b7a6c5d4e3f2a1b0c';
$_SERVER['APP_MASTER_KEY'] = 'master-key-9f8b7a6c5d4e3f2a1b0c';
